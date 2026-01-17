<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\AcademicPeriod;
use App\Models\Accounting\FeeItem;
use App\Models\Accounting\FeeStructure;
use App\Models\Accounting\Invoice;
use App\User;
use Illuminate\Support\Facades\Gate;

class InvoiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('teamAccount');
    }

    public function index()
    {
        $data['invoices'] = Invoice::with(['student', 'studentRecord.my_class', 'period', 'childInvoices.student'])
            ->whereNull('parent_invoice_id')
            ->orderByDesc('created_at')
            ->paginate(25);

        $data['students'] = User::select('id', 'name')
            ->where('user_type', 'student')
            ->orderBy('name')
            ->get();

        $data['periods'] = AcademicPeriod::orderBy('ordering')->get();
        $data['feeStructures'] = FeeStructure::orderBy('name')->get();
        $data['feeItems'] = FeeItem::with('category')->orderBy('name')->get();
        $prototype = new Invoice();
        $data['canCreateInvoices'] = Gate::allows('createInvoice', $prototype);

        return view('pages.accountant.invoices.index', $data);
    }

    public function show(Invoice $invoice)
    {
        $studentRecord = $invoice->studentRecord;
        if (! $studentRecord) {
            abort(404, 'Student record not found for invoice.');
        }

        return redirect()->route('accounting.students.account', $studentRecord->id);
    }
}
