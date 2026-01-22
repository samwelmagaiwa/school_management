<?php

namespace App\Http\Controllers\Librarian;

use App\Http\Controllers\Controller;
use App\Helpers\Qs;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $d = [];
        
        // Library Stats
        $d['total_books'] = DB::table('books')->count();
        $d['total_copies'] = DB::table('book_copies')->count();
        $d['active_loans'] = DB::table('book_loans')->where('status', 'active')->count();
        $d['overdue_loans'] = DB::table('book_loans')->where('status', 'overdue')->count();
        $d['pending_requests'] = DB::table('book_requests')->where('status', 'pending')->count();

        // Recent Loans
        $d['recent_loans'] = DB::table('book_loans')
            ->join('users', 'book_loans.user_id', '=', 'users.id')
            ->join('book_copies', 'book_loans.book_copy_id', '=', 'book_copies.id')
            ->join('books', 'book_copies.book_id', '=', 'books.id')
            ->select('users.name as user_name', 'books.title', 'book_loans.loan_date', 'book_loans.due_date', 'book_loans.status')
            ->orderBy('book_loans.created_at', 'desc')
            ->limit(5)
            ->get();

        return view('pages.librarian.dashboard', $d);
    }
}
