<?php

namespace App\Http\Controllers\SupportTeam;

use App\Helpers\Qs;
use App\Http\Controllers\Controller;
use App\Http\Requests\Library\LoanCreate;
use App\Http\Requests\Library\LoanOverrideRequest;
use App\Http\Requests\Library\LoanReturn;
use App\Models\Book;
use App\Models\BookCopy;
use App\Models\BookLoan;
use App\Models\BookLoanEvent;
use App\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

class BookLoanController extends Controller
{
    protected function userIsManager(): bool
    {
        $user = Auth::user();

        return $user && in_array($user->user_type, ['librarian', 'admin', 'super_admin']);
    }

    protected function userIsAdmin(): bool
    {
        $user = Auth::user();

        return $user && in_array($user->user_type, ['admin', 'super_admin']);
    }

    protected function userIsSuperAdmin(): bool
    {
        $user = Auth::user();

        return $user && $user->user_type === 'super_admin';
    }

    /**
     * Ensure a borrower is eligible to take a new loan.
     *
     * Rules (unless overridden by admin/super_admin):
     * - Active loans count must be below configured maximum.
     * - No active overdue loans.
     * - No loans with outstanding fines (fine_amount > 0).
     */
    protected function assertBorrowerEligible(User $user, bool $allowAdminOverride = true): void
    {
        $actor = Auth::user();
        $isAdminOverride = $allowAdminOverride && $actor && in_array($actor->user_type, ['admin', 'super_admin']);

        $maxLoans = max(1, (int) config('library.max_active_loans_per_user', 3));

        $activeLoansCount = BookLoan::where('user_id', $user->id)
            ->whereNull('returned_at')
            ->count();

        if ($activeLoansCount >= $maxLoans && ! $isAdminOverride) {
            abort(422, 'Borrowing limit reached.');
        }

        $now = Carbon::now();

        $hasOverdue = BookLoan::where('user_id', $user->id)
            ->whereNull('returned_at')
            ->where('due_at', '<', $now)
            ->exists();

        if ($hasOverdue && ! $isAdminOverride) {
            abort(422, 'Borrower has overdue loans and cannot borrow more books.');
        }

        $hasUnpaidFines = BookLoan::where('user_id', $user->id)
            ->where('fine_amount', '>', 0)
            ->exists();

        if ($hasUnpaidFines && ! $isAdminOverride) {
            abort(422, 'Borrower has outstanding fines and cannot borrow more books.');
        }
    }

    public function index()
    {
        abort_unless($this->userIsManager(), 403);

        $loans = BookLoan::with(['copy.book', 'user', 'processedBy', 'returnedBy'])
            ->whereNull('returned_at')
            ->orderByDesc('borrowed_at')
            ->get();

        return view('pages.library.loans.index', [
            'loans' => $loans,
            'overdueOnly' => false,
        ]);
    }

    public function overdue()
    {
        abort_unless($this->userIsManager(), 403);

        $now = Carbon::now();

        $loans = BookLoan::with(['copy.book', 'user', 'processedBy'])
            ->whereNull('returned_at')
            ->where('due_at', '<', $now)
            ->orderBy('due_at')
            ->get();

        return view('pages.library.loans.index', [
            'loans' => $loans,
            'overdueOnly' => true,
        ]);
    }

    public function show(BookLoan $loan)
    {
        abort_unless($this->userIsManager(), 403);

        $loan->load(['copy.book', 'user', 'processedBy', 'returnedBy', 'events.actor', 'request.requester']);

        return view('pages.library.loans.show', compact('loan'));
    }

    public function myLoans()
    {
        $user = Auth::user();

        $loans = BookLoan::with(['copy.book'])
            ->where('user_id', $user->id)
            ->orderByDesc('borrowed_at')
            ->get();

        return view('pages.library.loans.my', compact('loans'));
    }

    /**
     * Show all loans for the currently authenticated parent’s children.
     */
    public function childrenLoans()
    {
        $user = Auth::user();
        abort_unless($user && \App\Helpers\Qs::userIsParent(), 403);

        $children = \App\Helpers\Qs::findMyChildren($user->id);
        $childUserIds = $children->pluck('user_id')->all();

        $loans = BookLoan::with(['copy.book', 'user'])
            ->whereIn('user_id', $childUserIds ?: [0])
            ->orderByDesc('borrowed_at')
            ->get();

        return view('pages.library.loans.children', [
            'loans'    => $loans,
            'children' => $children,
        ]);
    }

    public function store(LoanCreate $request)
    {
        abort_unless($this->userIsManager(), 403);

        $data = $request->validated();
        $borrower = $data['borrower_id'] ?? Auth::id();

        if (($data['borrower_id'] ?? null) && ! $this->userIsManager()) {
            abort(403, 'You cannot borrow on behalf of another user.');
        }

        /** @var User $user */
        $user = User::findOrFail($borrower);

        // Enforce borrowing rules (limits, overdue, unpaid fines)
        try {
            $this->assertBorrowerEligible($user, true);
        } catch (HttpException $e) {
            return back()->with('flash_danger', $e->getMessage());
        }

        if (! empty($data['book_copy_id'])) {
            $copy = BookCopy::lockForUpdate()->with('book')->findOrFail($data['book_copy_id']);

            if ($copy->status !== 'available') {
                return back()->with('flash_danger', 'Selected copy is not available for borrowing.');
            }

            if ($copy->book && $copy->book->is_reference_only) {
                return back()->with('flash_danger', 'This book is reference only and cannot be borrowed.');
            }
        } elseif (! empty($data['book_id'])) {
            /** @var Book $book */
            $book = Book::findOrFail($data['book_id']);

            if ($book->is_reference_only) {
                return back()->with('flash_danger', 'This book is reference only and cannot be borrowed.');
            }

            $copy = $book->copies()->available()->lockForUpdate()->first();

            if (! $copy) {
                return back()->with('flash_danger', 'No available copies of this book.');
            }
        } else {
            return back()->with('flash_danger', 'Please select a book to borrow.');
        }

        $loanPeriodDays = max(1, (int) config('library.loan_period_days', 14));
        $now = Carbon::now();
        $explicitDueAt = ! empty($data['due_at']) ? Carbon::parse($data['due_at']) : null;

        DB::transaction(function () use ($user, $copy, $loanPeriodDays, $now, $explicitDueAt) {
            $dueAt = $explicitDueAt ?: $now->copy()->addDays($loanPeriodDays)->endOfDay();

            $loan = BookLoan::create([
                'book_copy_id' => $copy->id,
                'user_id' => $user->id,
                'processed_by' => Auth::id(),
                'borrowed_at' => $now,
                'due_at' => $dueAt,
                'status' => 'active',
            ]);

            $copy->update(['status' => 'borrowed']);

            $this->logLoanEvent($loan, 'loan_created', [
                'processed_by' => Auth::id(),
            ]);
        });

        return back()->with('flash_success', 'Book borrowed successfully.');
    }

    public function return(LoanReturn $request, BookLoan $loan)
    {
        abort_unless($this->userIsManager(), 403);

        if ($loan->returned_at) {
            return back()->with('flash_danger', 'This loan has already been returned.');
        }

        $data = $request->validated();

        DB::transaction(function () use ($loan, $data) {
            $now = Carbon::now();
            $due = Carbon::parse($loan->due_at);

            if ($now->lessThanOrEqualTo($due)) {
                $daysOverdue = 0;
            } else {
                $daysOverdue = max(1, $due->diffInDays($now));
            }

            $dailyFine = max(0, (float) config('library.daily_fine', 50));
            $fine = $daysOverdue * $dailyFine;

            $loan->update([
                'returned_at' => $now,
                'returned_by' => Auth::id(),
                'fine_amount' => $fine,
                'status' => 'returned',
            ]);

            $copy = $loan->copy;
            if ($copy) {
                $status = $data['mark_status'] ?? 'available';
                $copy->update(['status' => $status]);
            }

            $this->logLoanEvent($loan, 'returned', [
                'processed_by' => Auth::id(),
                'fine' => $fine,
                'days_overdue' => $daysOverdue,
            ]);
        });

        return back()->with('flash_success', 'Book returned successfully.');
    }

    public function waiveFine(LoanOverrideRequest $request, BookLoan $loan)
    {
        abort_unless($this->userIsAdmin(), 403);

        $loan->update([
            'fine_amount' => 0,
            'has_override' => true,
            'override_notes' => $request->input('reason'),
        ]);

        $this->logLoanEvent($loan, 'fine_waived', [
            'reason' => $request->input('reason'),
            'actor' => Auth::id(),
        ]);

        return back()->with('flash_success', 'Fine waived successfully.');
    }

    public function forceClose(LoanOverrideRequest $request, BookLoan $loan)
    {
        abort_unless($this->userIsAdmin(), 403);

        DB::transaction(function () use ($loan, $request) {
            $returnedAt = $request->input('returned_at') ? Carbon::parse($request->input('returned_at')) : Carbon::now();
            $loan->update([
                'returned_at' => $returnedAt,
                'returned_by' => Auth::id(),
                'status' => 'returned',
                'has_override' => true,
                'override_notes' => $request->input('reason'),
            ]);

            if ($loan->copy) {
                $loan->copy->update(['status' => $request->input('mark_status', 'available')]);
            }

            $this->logLoanEvent($loan, 'force_closed', [
                'reason' => $request->input('reason'),
                'actor' => Auth::id(),
            ]);
        });

        return back()->with('flash_success', 'Loan forcibly closed.');
    }

    public function reverse(LoanOverrideRequest $request, BookLoan $loan)
    {
        abort_unless($this->userIsSuperAdmin(), 403);

        DB::transaction(function () use ($loan, $request) {
            if ($loan->copy) {
                $loan->copy->update(['status' => 'available']);
            }

            $loan->update([
                'returned_at' => null,
                'returned_by' => null,
                'fine_amount' => 0,
                'status' => 'reversed',
                'has_override' => true,
                'override_notes' => $request->input('reason'),
            ]);

            if ($loan->request) {
                $loan->request->markCancelled();
            }

            $this->logLoanEvent($loan, 'loan_reversed', [
                'reason' => $request->input('reason'),
                'actor' => Auth::id(),
            ]);
        });

        return back()->with('flash_success', 'Loan reversed successfully.');
    }

    protected function logLoanEvent(BookLoan $loan, string $type, array $meta = []): void
    {
        BookLoanEvent::create([
            'book_loan_id' => $loan->id,
            'performed_by' => Auth::id(),
            'event_type' => $type,
            'meta' => $meta,
        ]);
    }
}
