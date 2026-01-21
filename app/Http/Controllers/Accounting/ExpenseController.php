<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\Expense;
use App\Models\Accounting\Vendor;
use Illuminate\Http\Request;
use App\Helpers\Qs;

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

        // Prevent Duplicate Saving (Same Title, Amount, Date and Vendor)
        $exists = Expense::where([
            ['title', '=', $data['title']],
            ['amount', '=', $data['amount']],
            ['expense_date', '=', $data['expense_date']->format('Y-m-d')],
            ['vendor_id', '=', $data['vendor_id']],
            ['category', '=', $data['category']],
        ])->where('created_at', '>', now()->subDay())->exists();

        if ($exists) {
            if ($request->ajax()) {
                return Qs::json('A similar expense was already recorded in the last 24 hours.', false);
            }
            return back()->with('flash_danger', 'A similar expense was already recorded in the last 24 hours.');
        }

        $data['recorded_by'] = auth()->id();
        
        // If status is approved/paid (default logic), maybe set approved_by?
        // For simplicity, accountant acts as approver
        if(in_array($data['status'], ['approved', 'paid'])) {
            $data['approved_by'] = auth()->id();
        }

        Expense::create($data);

        session()->flash('flash_success', 'Expense Recorded Successfully!');

        if ($request->ajax()) {
            return Qs::json('Expense Recorded Successfully!');
        }

        return back();
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

        session()->flash('flash_success', 'Expense Updated Successfully!');

        if ($request->ajax()) {
            return Qs::json('Expense Updated Successfully!');
        }

        return back();
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();
        return back()->with('flash_success', 'Expense deleted successfully.');
    }
}
