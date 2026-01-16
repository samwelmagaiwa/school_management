<?php

namespace App\Http\Controllers\SupportTeam;

use App\Helpers\Qs;
use App\Http\Controllers\Controller;
use App\Http\Requests\Library\BorrowRequestCreate;
use App\Http\Requests\Library\BorrowRequestDecision;
use App\Models\Book;
use App\Models\BookCopy;
use App\Models\BookLoan;
use App\Models\BookLoanEvent;
use App\Models\BookRequest;
use App\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

class BookRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    protected function userIsManager(): bool
    {
        $user = Auth::user();

        return $user && in_array($user->user_type, ['librarian', 'admin', 'super_admin']);
    }

    public function index()
    {
        abort_unless($this->userIsManager(), 403);

        $requests = BookRequest::with(['book.copies', 'requester'])
            ->orderByRaw("FIELD(status, 'pending','approved','rejected','cancelled')")
            ->orderByDesc('created_at')
            ->get();

        return view('pages.library.requests.index', compact('requests'));
    }

    public function myRequests()
    {
        $requests = BookRequest::with('book')
            ->where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->get();

        return view('pages.library.requests.my', compact('requests'));
    }

    public function store(BorrowRequestCreate $request)
    {
        $book = Book::findOrFail($request->input('book_id'));

        if ($book->is_reference_only) {
            return back()->with('flash_danger', 'This book is reference only and cannot be requested for borrowing.');
        }

        BookRequest::create([
            'book_id' => $book->id,
            'user_id' => Auth::id(),
        ]);

        return back()->with('flash_success', 'Borrow request submitted successfully.');
    }

    public function approve(BorrowRequestDecision $request, BookRequest $bookRequest)
    {
        abort_unless($this->userIsManager(), 403);
        abort_if($bookRequest->status !== 'pending', 422, 'Request already processed.');

        $payload = $request->validated();

        $borrower = isset($payload['borrower_id'])
            ? User::findOrFail($payload['borrower_id'])
            : $bookRequest->requester;

        // Reuse loan controller eligibility logic via a new instance
        try {
            app(\App\Http\Controllers\SupportTeam\BookLoanController::class)
                ->assertBorrowerEligible($borrower, true);
        } catch (HttpException $e) {
            return back()->with('flash_danger', $e->getMessage());
        }

        $loanPeriod = max(1, (int) config('library.loan_period_days', 14));

        DB::transaction(function () use ($bookRequest, $payload, $borrower, $loanPeriod) {
            if (! empty($payload['book_copy_id'])) {
                $copy = BookCopy::lockForUpdate()->with('book')->findOrFail($payload['book_copy_id']);
            } else {
                $copy = BookCopy::where('book_id', $bookRequest->book_id)
                    ->available()
                    ->with('book')
                    ->lockForUpdate()
                    ->first();
            }

            abort_if(! $copy || $copy->status !== 'available', 422, 'No available copies to fulfill this request.');
            abort_if($copy->book && $copy->book->is_reference_only, 422, 'This book is reference only and cannot be borrowed.');

            $now = Carbon::now();
            $explicitDueAt = ! empty($payload['due_at']) ? Carbon::parse($payload['due_at'])->toDateString() : null;

            $loan = BookLoan::create([
                'book_copy_id' => $copy->id,
                'user_id' => $borrower->id,
                'processed_by' => Auth::id(),
                'borrowed_at' => $now,
                'due_at' => $explicitDueAt ?: $now->copy()->addDays($loanPeriod)->toDateString(),
                'status' => 'active',
            ]);

            $copy->update(['status' => 'borrowed']);

            $bookRequest->markApproved(Auth::id(), $copy, $loan);

            BookLoanEvent::create([
                'book_loan_id' => $loan->id,
                'performed_by' => Auth::id(),
                'event_type' => 'request_approved',
                'meta' => [
                    'request_id' => $bookRequest->id,
                ],
            ]);
        });

        return back()->with('flash_success', 'Request approved. The borrower has been notified.');
    }

    public function reject(BorrowRequestDecision $request, BookRequest $bookRequest)
    {
        abort_unless($this->userIsManager(), 403);
        abort_if($bookRequest->status !== 'pending', 422, 'Request already processed.');

        $reason = $request->input('reason');
        abort_if(empty($reason), 422, 'A reason is required.');

        $bookRequest->markRejected(Auth::id(), $reason);

        return back()->with('flash_success', 'Request rejected successfully.');
    }

    public function cancel(BookRequest $bookRequest)
    {
        abort_unless($bookRequest->user_id === Auth::id(), 403);
        abort_if($bookRequest->status !== 'pending', 422, 'Only pending requests can be cancelled.');

        $bookRequest->markCancelled();

        return back()->with('flash_success', 'Request cancelled successfully.');
    }
}
