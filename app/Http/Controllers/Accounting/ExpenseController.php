<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\Expense;
use App\Models\Accounting\Vendor;
use Illuminate\Http\Request;

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
    
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:191',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'category' => 'required|string|max:100', // Could be a separate model, keeping simple string for now
            'vendor_id' => 'nullable|exists:vendors,id',
            'reference' => 'nullable|string|max:100',
            'note' => 'nullable|string',
            'status' => 'required|in:pending,approved,paid,cancelled',
        ]);

        $data['expense_date'] = \Carbon\Carbon::parse($data['expense_date']);
        $data['recorded_by'] = auth()->id();
        
        // If status is approved/paid (default logic), maybe set approved_by?
        // For simplicity, accountant acts as approver
        if(in_array($data['status'], ['approved', 'paid'])) {
            $data['approved_by'] = auth()->id();
        }

        Expense::create($data);

        return back()->with('flash_success', 'Expense recorded successfully.');
    }

    public function update(Request $request, Expense $expense)
    {
        $data = $request->validate([
            'title' => 'required|string|max:191',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'category' => 'required|string|max:100',
            'vendor_id' => 'nullable|exists:vendors,id',
            'reference' => 'nullable|string|max:100',
            'note' => 'nullable|string',
            'status' => 'required|in:pending,approved,paid,cancelled',
        ]);

        $data['expense_date'] = \Carbon\Carbon::parse($data['expense_date']);
        
         if(in_array($data['status'], ['approved', 'paid']) && !$expense->approved_by) {
            $data['approved_by'] = auth()->id();
        }

        $expense->update($data);

        return back()->with('flash_success', 'Expense updated successfully.');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();
        return back()->with('flash_success', 'Expense deleted successfully.');
    }
}
