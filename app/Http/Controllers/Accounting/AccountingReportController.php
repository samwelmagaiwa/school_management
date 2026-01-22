<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\StudentAccount;
use App\Models\Accounting\Expense;
use App\Repositories\PaymentRepo;
use App\Helpers\Qs;
use Carbon\Carbon;

class AccountingReportController extends Controller
{
    protected $payment;

    public function __construct(PaymentRepo $payment)
    {
        $this->middleware('auth');
        $this->middleware('can:payment.view');
        $this->payment = $payment;
    }

    public function summary()
    {
        $d = [];
        
        // Financial Summary Statistics
        $currentYear = Qs::getSetting('current_session');
        
        // Total Revenue YTD (Year to Date)
        $d['total_revenue'] = $this->payment->getPayment(['year' => $currentYear])
            ->sum('amount');
        
        // Total Collections (Paid amounts)
        $d['total_collections'] = \DB::table('payment_records')
            ->where('year', $currentYear)
            ->sum('amt_paid');
        
        // Outstanding Balances
        $d['outstanding_balance'] = \DB::table('payment_records')
            ->where('year', $currentYear)
            ->where('paid', 0)
            ->sum('balance');
        
        // Total Expenses YTD
        // Extract the first year from academic year format (e.g., "2026" from "2026-2027")
        $yearStart = explode('-', $currentYear)[0] ?? date('Y');
        $d['total_expenses'] = Expense::whereYear('expense_date', '>=', $yearStart)
            ->sum('amount');
        
        // Recent Payments (Last 10)
        $d['recent_payments'] = \DB::table('payment_records')
            ->join('users', 'payment_records.student_id', '=', 'users.id')
            ->join('payments', 'payment_records.payment_id', '=', 'payments.id')
            ->select('users.name as student_name', 'payments.title', 'payment_records.amt_paid', 'payment_records.created_at')
            ->where('payment_records.year', $currentYear)
            ->orderBy('payment_records.created_at', 'desc')
            ->limit(10)
            ->get();
        
        return view('pages.accounting.dashboard', $d);
    }
}
