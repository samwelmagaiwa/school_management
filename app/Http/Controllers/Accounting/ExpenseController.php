<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\Expense;
use App\Models\Accounting\Vendor;

class ExpenseController extends Controller
{
    public function __construct()
    {
        $this->middleware('teamAccount');
    }

    public function index()
    {
        $data['vendors'] = Vendor::orderBy('name')->get();
        $data['expenses'] = Expense::with(['vendor', 'recorder', 'approver'])
            ->orderByDesc('expense_date')
            ->paginate(50);

        return view('pages.accountant.expenses.index', $data);
    }
}
