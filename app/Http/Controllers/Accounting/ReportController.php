<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\AcademicPeriod;
use App\Models\Accounting\AccountingAuditLog;
use App\Models\Accounting\Expense;
use App\Models\Accounting\Invoice;
use App\Models\Accounting\NonFeeIncome;
use App\Models\Accounting\PaymentLedger;
use App\Models\Accounting\StudentInstallment;
use App\Models\MyClass;
use App\Models\StudentRecord;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('teamAccount');
    }

    public function index(Request $request)
    {
        if ($request->filled('export')) {
            return $this->handleExport($request);
        }

        $classFilter = $request->input('class_id');
        $studentSearch = $request->input('student_search');
        $cashbookStart = $request->input('cashbook_start');
        $cashbookEnd = $request->input('cashbook_end');

        $feeSummary = $this->buildFeeSummary();
        $outstandingByClass = $this->outstandingByClass($classFilter);
        $outstandingByStudent = $this->outstandingByStudent($studentSearch);
        $cashbookDaily = $this->cashbook('daily', $cashbookStart, $cashbookEnd);
        $cashbookMonthly = $this->cashbook('monthly', $cashbookStart, $cashbookEnd);
        $incomeVsExpenses = $this->incomeVsExpenses();
        $arrearsAging = $this->arrearsAging();
        $studentStatements = $this->studentStatements();
        $paymentHistory = $this->paymentHistory();
        $balanceForward = $this->balanceForward();
        $installmentsByStudent = $this->installmentsByStudent();
        $classes = MyClass::orderBy('name')->get();
        $periods = AcademicPeriod::orderBy('ordering')->get();
        $auditLogs = AccountingAuditLog::with('user')
            ->orderByDesc('created_at')
            ->limit(15)
            ->get();

        return view('pages.accountant.reports.index', compact(
            'feeSummary',
            'outstandingByClass',
            'outstandingByStudent',
            'cashbookDaily',
            'cashbookMonthly',
            'incomeVsExpenses',
            'arrearsAging',
            'studentStatements',
            'paymentHistory',
            'balanceForward',
            'installmentsByStudent',
            'classes',
            'classFilter',
            'studentSearch',
            'cashbookStart',
            'cashbookEnd',
            'periods',
            'auditLogs'
        ));
    }

    protected function buildFeeSummary(): array
    {
        // Use new invoices table as the source of truth
        $invoiceQuery = Invoice::query()->whereNotIn('status', ['draft', 'cancelled']);

        $totalInvoiced = (clone $invoiceQuery)->sum('total_amount');
        $totalPaid = (clone $invoiceQuery)->sum('amount_paid');
        $outstanding = (clone $invoiceQuery)->sum('balance_due');

        return [
            'total_invoiced' => $totalInvoiced,
            'total_paid' => $totalPaid,
            'outstanding' => $outstanding,
            'collection_rate' => $totalInvoiced > 0 ? round(($totalPaid / max(0.01, $totalInvoiced)) * 100, 2) : 0,
        ];
    }

    protected function outstandingByClass($classId = null)
    {
        // Aggregate outstanding balances from invoices grouped by class
        $rows = StudentRecord::select('student_records.my_class_id', DB::raw('SUM(invoices.balance_due) as outstanding'))
            ->join('invoices', 'invoices.student_record_id', '=', 'student_records.id')
            ->where('invoices.balance_due', '>', 0)
            ->whereNotIn('invoices.status', ['draft', 'cancelled'])
            ->when($classId, function ($query, $classId) {
                $query->where('student_records.my_class_id', $classId);
            })
            ->groupBy('student_records.my_class_id')
            ->orderByDesc('outstanding')
            ->get();

        $classMap = MyClass::pluck('name', 'id');

        return $rows->map(function ($row) use ($classMap) {
            return [
                'class' => $classMap->get($row->my_class_id, 'N/A'),
                'outstanding' => $row->outstanding,
            ];
        });
    }

    protected function outstandingByStudent($search = null)
    {
        $rows = Invoice::select('invoices.student_id', 'users.name as student_name', DB::raw('SUM(invoices.balance_due) as outstanding'), DB::raw('SUM(invoices.amount_paid) as paid'))
            ->join('users', 'users.id', '=', 'invoices.student_id')
            ->where('invoices.balance_due', '>', 0)
            ->whereNotIn('invoices.status', ['draft', 'cancelled'])
            ->when($search, function ($query, $search) {
                $query->where('users.name', 'like', "%{$search}%");
            })
            ->groupBy('invoices.student_id', 'users.name')
            ->orderByDesc('outstanding')
            ->take(10)
            ->get();

        return $rows->map(function ($row) {
            return [
                'student' => $row->student_name,
                'outstanding' => $row->outstanding,
                'paid' => $row->paid,
            ];
        });
    }

    protected function cashbook(string $type, $start = null, $end = null)
    {
        $dateExpr = $type === 'daily' ? 'DATE(received_at)' : "DATE_FORMAT(received_at, '%Y-%m')";
        $query = PaymentLedger::select(DB::raw($dateExpr . ' as label'), DB::raw('SUM(amount) as total'))
            ->where('status', '!=', 'refunded')
            ->when($start, function ($query, $start) {
                $query->whereDate('received_at', '>=', $start);
            })
            ->when($end, function ($query, $end) {
                $query->whereDate('received_at', '<=', $end);
            })
            ->groupBy('label')
            ->orderByDesc('label');

        $limit = $type === 'daily' ? 10 : 6;

        return $query->take($limit)->get();
    }

    protected function incomeVsExpenses(): array
    {
        // Fee income from new payments ledger
        $feeIncome = PaymentLedger::where('source', 'fee')
            ->where('status', '!=', 'refunded')
            ->sum('amount');

        $otherIncome = NonFeeIncome::sum('amount');
        $expenses = Expense::sum('amount');

        return [
            'fee_income' => $feeIncome,
            'other_income' => $otherIncome,
            'total_income' => $feeIncome + $otherIncome,
            'expenses' => $expenses,
            'net' => ($feeIncome + $otherIncome) - $expenses,
        ];
    }

    protected function arrearsAging(): array
    {
        $buckets = [
            '0-30' => 0,
            '31-60' => 0,
            '61-90' => 0,
            '90+' => 0,
        ];

        // Age based on installment due dates and remaining balances
        $records = StudentInstallment::whereColumn('amount_paid', '<', 'amount')
            ->get(['amount', 'amount_paid', 'due_date']);

        foreach ($records as $record) {
            $balance = (float) $record->amount - (float) $record->amount_paid;
            if ($balance <= 0) {
                continue;
            }

            $age = $record->due_date
                ? Carbon::parse($record->due_date)->diffInDays(now())
                : 0;

            if ($age <= 30) {
                $buckets['0-30'] += $balance;
            } elseif ($age <= 60) {
                $buckets['31-60'] += $balance;
            } elseif ($age <= 90) {
                $buckets['61-90'] += $balance;
            } else {
                $buckets['90+'] += $balance;
            }
        }

        return $buckets;
    }

    protected function studentStatements()
    {
        return Invoice::select(
                'invoices.student_id',
                'users.name as student_name',
                DB::raw('SUM(invoices.total_amount) as invoiced'),
                DB::raw('SUM(invoices.amount_paid) as paid'),
                DB::raw('SUM(invoices.balance_due) as balance')
            )
            ->join('users', 'users.id', '=', 'invoices.student_id')
            ->whereNotIn('invoices.status', ['draft'])
            ->groupBy('invoices.student_id', 'users.name')
            ->orderByDesc('balance')
            ->take(10)
            ->get();
    }

    protected function paymentHistory()
    {
        return PaymentLedger::with('student')
            ->orderByDesc('received_at')
            ->take(10)
            ->get();
    }

    protected function balanceForward()
    {
        return Invoice::select('invoices.student_id', 'users.name as student_name', DB::raw('SUM(invoices.balance_due) as outstanding'))
            ->join('users', 'users.id', '=', 'invoices.student_id')
            ->where('invoices.balance_due', '>', 0)
            ->whereNotIn('invoices.status', ['draft', 'cancelled'])
            ->groupBy('invoices.student_id', 'users.name')
            ->orderByDesc('outstanding')
            ->take(10)
            ->get();
    }

    protected function installmentsByStudent()
    {
        return StudentInstallment::select(
                'student_installments.student_id',
                'users.name as student_name',
                DB::raw('SUM(student_installments.amount) as scheduled'),
                DB::raw('SUM(student_installments.amount_paid) as paid'),
                DB::raw('SUM(student_installments.amount - student_installments.amount_paid) as balance'),
                DB::raw("SUM(CASE WHEN student_installments.status = 'overdue' THEN (student_installments.amount - student_installments.amount_paid) ELSE 0 END) as overdue")
            )
            ->join('users', 'users.id', '=', 'student_installments.student_id')
            ->groupBy('student_installments.student_id', 'users.name')
            ->orderByDesc('balance')
            ->take(20)
            ->get();
    }

    protected function handleExport(Request $request)
    {
        $type = $request->input('export');
        $classFilter = $request->input('class_id');
        $studentSearch = $request->input('student_search');
        $start = $request->input('cashbook_start');
        $end = $request->input('cashbook_end');

        switch ($type) {
            case 'outstanding-class':
                $data = $this->outstandingByClass($classFilter);
                $headers = ['Class', 'Outstanding'];
                $rows = $data->map(fn ($row) => [$row['class'], $row['outstanding']]);
                break;
            case 'installments-student':
                $data = $this->installmentsByStudent();
                $headers = ['Student ID', 'Student', 'Scheduled', 'Paid', 'Outstanding', 'Overdue'];
                $rows = $data->map(fn ($row) => [
                    $row->student_id,
                    $row->student_name,
                    $row->scheduled,
                    $row->paid,
                    $row->balance,
                    $row->overdue,
                ]);
                break;
            case 'outstanding-student':
                $data = $this->outstandingByStudent($studentSearch);
                $headers = ['Student', 'Outstanding', 'Paid'];
                $rows = $data->map(fn ($row) => [$row['student'], $row['outstanding'], $row['paid']]);
                break;
            case 'cashbook-daily':
                $data = $this->cashbook('daily', $start, $end);
                $headers = ['Date', 'Total'];
                $rows = $data->map(fn ($row) => [$row->label, $row->total]);
                break;
            case 'cashbook-monthly':
                $data = $this->cashbook('monthly', $start, $end);
                $headers = ['Month', 'Total'];
                $rows = $data->map(fn ($row) => [$row->label, $row->total]);
                break;
            case 'income-expenses':
                $data = $this->incomeVsExpenses();
                $headers = ['Fee Income', 'Other Income', 'Total Income', 'Expenses', 'Net'];
                $rows = collect([[
                    $data['fee_income'],
                    $data['other_income'],
                    $data['total_income'],
                    $data['expenses'],
                    $data['net'],
                ]]);
                break;
            case 'arrears-aging':
                $data = $this->arrearsAging();
                $headers = ['Bucket', 'Amount'];
                $rows = collect($data)->map(fn ($value, $label) => [$label, $value]);
                break;
            case 'student-statements':
                $data = $this->studentStatements();
                $headers = ['Student ID', 'Student', 'Invoiced', 'Paid', 'Balance'];
                $rows = $data->map(fn ($row) => [$row->student_id, $row->student_name, $row->invoiced, $row->paid, $row->balance]);
                break;
            case 'payment-history':
                $data = $this->paymentHistory();
                $headers = ['Receipt', 'Student', 'Amount', 'Method', 'Received At'];
                $rows = $data->map(fn ($row) => [$row->receipt_number, optional($row->student)->name, $row->amount, $row->method, optional($row->received_at)->format('Y-m-d H:i')]);
                break;
            case 'balance-forward':
                $data = $this->balanceForward();
                $headers = ['Student ID', 'Student', 'Outstanding'];
                $rows = $data->map(fn ($row) => [$row->student_id, $row->student_name, $row->outstanding]);
                break;
            default:
                abort(404);
        }

        return $this->streamCsv("{$type}-" . now()->format('Ymd_His') . '.csv', $headers, $rows);
    }

    protected function streamCsv(string $filename, array $headers, $rows): StreamedResponse
    {
        $callback = function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);
            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        };

        return response()->streamDownload($callback, $filename, ['Content-Type' => 'text/csv']);
    }
}
