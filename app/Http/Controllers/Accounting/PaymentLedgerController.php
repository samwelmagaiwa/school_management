<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\AcademicPeriod;
use App\Models\Accounting\PaymentLedger;
use App\User;
use Illuminate\Support\Facades\Gate;

class PaymentLedgerController extends Controller
{
    public function __construct()
    {
        $this->middleware('teamAccount');
    }

    public function index()
    {
        $data['payments'] = PaymentLedger::with(['student', 'recorder'])
            ->orderByDesc('received_at')
            ->paginate(50);

        $data['students'] = User::select('id', 'name')
            ->where('user_type', 'student')
            ->orderBy('name')
            ->get();
        $prototype = new PaymentLedger();
        $data['canRecordPayments'] = Gate::allows('recordPayment', $prototype);
        $data['periods'] = AcademicPeriod::orderBy('ordering')->get();

        // Limited list of open installments to allow direct allocation when recording payments
        $data['installments'] = \App\Models\Accounting\StudentInstallment::with(['student', 'invoice', 'installmentDefinition'])
            ->whereIn('status', ['pending', 'partial', 'overdue'])
            ->orderBy('due_date')
            ->limit(50)
            ->get();

        return view('pages.accountant.payments.index', $data);
    }
}
