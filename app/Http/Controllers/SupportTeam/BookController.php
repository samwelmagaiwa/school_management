<?php

namespace App\Http\Controllers\SupportTeam;

use App\Helpers\Qs;
use App\Http\Controllers\Controller;
use App\Http\Requests\Library\BookCreate;
use App\Http\Requests\Library\BookUpdate;
use App\Http\Requests\Library\CopyCreate;
use App\Models\Book;
use App\Models\BookCategory;
use App\Models\BookCopy;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BookController extends Controller
{
    public function __construct()
    {
        // Only librarians/admins/super-admins can manage the catalog and copies.
        // Index and show are left open to all authenticated users for read-only browsing.
        $this->middleware('libraryManager')->except(['index', 'show']);
    }

    /**
     * Display all books with availability information.
     */
    public function index(Request $request)
    {
        $query = Book::with('copies');

        $search   = trim($request->get('q', ''));
        $filter   = $request->get('filter', ''); // '', 'reference', 'borrowable'
        $category = $request->get('category', '');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%")
                  ->orWhere('isbn', 'like', "%{$search}%");
            });
        }

        if ($filter === 'reference') {
            $query->where('is_reference_only', true);
        } elseif ($filter === 'borrowable') {
            $query->where(function ($q) {
                $q->whereNull('is_reference_only')->orWhere('is_reference_only', false);
            });
        }

        if ($category !== '') {
            $query->where('category', $category);
        }

        $data['books']      = $query->orderBy('name')->get();
        $data['search']     = $search;
        $data['filter']     = $filter;
        $data['category']   = $category;
        $data['categories'] = Book::query()
            ->select('category')
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('pages.library.books.index', $data);
    }

    /**
     * Show form to create a new book.
     */
    public function create()
    {
        $categories = BookCategory::orderBy('name')->get();

        return view('pages.library.books.create', [
            'categories' => $categories,
        ]);
    }

    /**
     * Store a new book in the catalog.
     */
    public function store(BookCreate $request)
    {
        $validated = $request->validated();

        // Extract and normalize fields that are not stored directly on the model
        $totalCopies = isset($validated['total_copies']) ? (int) $validated['total_copies'] : 0;
        unset($validated['total_copies']);

        // Normalize reference-only flag
        $validated['is_reference_only'] = $request->boolean('is_reference_only');

        // Handle optional cover image upload
        if ($request->hasFile('cover_image')) {
            $image = $request->file('cover_image');
            $f = \App\Helpers\Qs::getFileMetaData($image);
            $f['name'] = 'cover.' . $f['ext'];
            $f['path'] = $image->storeAs(\App\Helpers\Qs::getUploadPath('books'), $f['name']);
            $validated['cover_image_path'] = 'storage/' . $f['path'];
        }

        $book = null;

        \DB::transaction(function () use (&$book, $validated, $totalCopies) {
            $book = Book::create($validated);

            if ($totalCopies > 0) {
                $existingCount = 0;

                for ($i = 1; $i <= $totalCopies; $i++) {
                    $sequence = $existingCount + $i;
                    $code = strtoupper(\App\Helpers\Qs::getAppCode().'-BK-'.$book->id.'-'.str_pad($sequence, 4, '0', STR_PAD_LEFT));

                    BookCopy::create([
                        'book_id' => $book->id,
                        'copy_code' => $code,
                        'status' => 'available',
                    ]);
                }

                $book->total_copies = $book->copies()->count();
                $book->issued_copies = $book->copies()->where('status', 'borrowed')->count();
                $book->save();
            }
        });

        // Use JSON response for AJAX-enabled forms, consistent with other modules
        return Qs::jsonStoreOk();
    }

    /**
     * Display a single book with its copies.
     */
    public function show(Book $book)
    {
        $book->load('copies');

        $borrowers = collect();
        $user = Auth::user();
        if ($user && in_array($user->user_type, ['librarian', 'admin', 'super_admin'])) {
            $borrowers = User::whereIn('user_type', ['student', 'teacher'])
                ->orderBy('name')
                ->get();
        }

        return view('pages.library.books.show', [
            'book'      => $book,
            'borrowers' => $borrowers,
        ]);
    }

    /**
     * Show the form for editing an existing book.
     */
    public function edit(Book $book)
    {
        $categories = BookCategory::orderBy('name')->get();

        return view('pages.library.books.edit', [
            'book'       => $book,
            'categories' => $categories,
        ]);
    }

    /**
     * Update an existing book.
     */
    public function update(BookUpdate $request, Book $book)
    {
        $data = $request->validated();

        // Normalize reference-only flag
        $data['is_reference_only'] = $request->boolean('is_reference_only');

        // Handle optional cover image upload
        if ($request->hasFile('cover_image')) {
            $image = $request->file('cover_image');
            $f = \App\Helpers\Qs::getFileMetaData($image);
            $f['name'] = 'cover.' . $f['ext'];
            $f['path'] = $image->storeAs(\App\Helpers\Qs::getUploadPath('books'), $f['name']);
            $data['cover_image_path'] = 'storage/' . $f['path'];
        }

        unset($data['cover_image']);

        $book->update($data);

        return Qs::jsonUpdateOk();
    }

    /**
     * Remove a book from the catalog.
     * A book can only be deleted if it has no copies with active loans.
     */
    public function destroy(Book $book)
    {
        $hasActiveLoans = BookCopy::where('book_id', $book->id)
            ->whereHas('loans', function ($q) {
                $q->whereNull('returned_at');
            })
            ->exists();

        if ($hasActiveLoans) {
            return back()->with('flash_danger', 'Cannot delete a book that has copies currently on loan.');
        }

        $book->delete();

        return back()->with('flash_success', __('msg.del_ok'));
    }

    /**
     * Add one or more physical copies for a book.
     */
    public function storeCopies(CopyCreate $request, Book $book)
    {
        $validated = $request->validated();
        $quantity = (int) $validated['quantity'];

        DB::transaction(function () use ($book, $quantity) {
            $existingCount = $book->copies()->count();

            for ($i = 1; $i <= $quantity; $i++) {
                $sequence = $existingCount + $i;
                $code = strtoupper(Qs::getAppCode().'-BK-'.$book->id.'-'.str_pad($sequence, 4, '0', STR_PAD_LEFT));

                BookCopy::create([
                    'book_id' => $book->id,
                    'copy_code' => $code,
                    'status' => 'available',
                ]);
            }

            // Optionally keep legacy counters roughly in sync
            $book->total_copies = $book->copies()->count();
            $book->issued_copies = $book->copies()->where('status', 'borrowed')->count();
            $book->save();
        });

        return back()->with('flash_success', __('msg.store_ok'));
    }

    /**
     * Update a copy status (e.g., mark as damaged or lost).
     */
    public function updateCopyStatus(Request $request, BookCopy $copy)
    {
        $data = $request->validate([
            // "borrowed" status should be driven by BookLoan creation only, to keep
            // a single source of truth. Librarians can mark copies as available,
            // damaged or lost from here.
            'status' => 'required|in:available,damaged,lost',
            'notes' => 'nullable|string',
        ]);

        $hasActiveLoan = $copy->loans()->whereNull('returned_at')->exists();
        if ($data['status'] === 'available' && $hasActiveLoan) {
            return back()->with('flash_danger', 'Copy is currently on loan and cannot be marked as available.');
        }

        $copy->update($data);

        return back()->with('flash_success', __('msg.update_ok'));
    }

    /**
     * Delete a physical copy when it is not on loan.
     */
    public function destroyCopy(BookCopy $copy)
    {
        $hasActiveLoan = $copy->loans()->whereNull('returned_at')->exists();

        if ($hasActiveLoan) {
            return back()->with('flash_danger', 'Cannot delete a copy that is currently on loan.');
        }

        $copy->delete();

        return back()->with('flash_success', __('msg.del_ok'));
    }
}
