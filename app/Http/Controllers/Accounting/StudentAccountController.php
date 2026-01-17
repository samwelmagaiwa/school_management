<?php

namespace App\Http\Controllers\Accounting;

use App\Helpers\Qs;
use App\Http\Controllers\Controller;
use App\Http\Requests\Accounting\PaymentRequest;
use App\Models\Accounting\Invoice;
use App\Models\Accounting\PaymentAllocation;
use App\Models\Accounting\PaymentLedger;
use App\Models\Accounting\StudentInstallment;
use App\Models\StudentRecord;
use App\Services\Accounting\AccountingPermissionService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class StudentAccountController extends Controller
{
    public function __construct(protected AccountingPermissionService $permissions)
    {
        $this->middleware('teamAccount');
    }

    public function show(StudentRecord $student)
    {
        $student->load(['user', 'my_class']);

        $invoices = Invoice::with(['childInvoices' => function ($q) {
                $q->orderBy('installment_sequence');
            }, 'childInvoices.installments', 'installments'])
            ->where('student_record_id', $student->id)
            ->whereNull('parent_invoice_id')
            ->orderByDesc('issued_at')
            ->get();

        $installments = StudentInstallment::with(['invoice', 'installmentDefinition'])
            ->where('student_id', $student->user_id)
            ->orderBy('due_date')
            ->get();

        $payments = PaymentLedger::with(['allocations.invoice'])
            ->where('student_id', $student->user_id)
            ->orderByDesc('received_at')
            ->get();

        return view('pages.accountant.invoices.show', [
            'student' => $student,
            'invoices' => $invoices,
            'installments' => $installments,
            'payments' => $payments,
        ]);
    }

    public function recordPayment(StudentRecord $student, PaymentRequest $request): RedirectResponse
    {
        $this->permissions->ensure('payments.record');

        $data = $request->validated();
        $data['student_id'] = $student->user_id;
        $data['recorded_by'] = auth()->id();
        $data['receipt_number'] = $data['receipt_number'] ?? $this->generateReceipt();
        $data['received_at'] = $data['received_at'] ? Carbon::parse($data['received_at']) : now();
        $data['status'] = 'open';

        $payment = PaymentLedger::create($data);
        $remaining = $payment->amount;
        $strategy = $data['allocation_strategy'] ?? 'oldest';

        if (($data['student_installment_id'] ?? null) && $strategy === 'specific') {
            $installment = StudentInstallment::find($data['student_installment_id']);
            if ($installment && $installment->student_id === $student->user_id) {
                $remaining = $this->allocateToInstallment($payment, $installment, $remaining, 'specific');
            }
        }

        if ($remaining > 0) {
            $remaining = $this->allocateOldest($payment, $student, $remaining);
        }

        $payment->update(['status' => $remaining <= 0 ? 'allocated' : 'open']);

        return back()->with('flash_success', __('msg.store_ok'));
    }

    protected function allocateOldest(PaymentLedger $payment, StudentRecord $student, float $amount): float
    {
        $installments = StudentInstallment::where('student_id', $student->user_id)
            ->where('status', '!=', 'paid')
            ->orderBy('due_date')
            ->get();

        foreach ($installments as $installment) {
            if ($amount <= 0) {
                break;
            }

            $amount = $this->allocateToInstallment($payment, $installment, $amount, 'oldest_first');
        }

        return $amount;
    }

    protected function allocateToInstallment(PaymentLedger $payment, StudentInstallment $installment, float $available, string $strategy = 'auto'): float
    {
        $invoice = $installment->invoice;
        if (! $invoice) {
            return $available;
        }

        $remaining = max(0, $installment->amount - $installment->amount_paid);
        $apply = min($available, $remaining);

        if ($apply <= 0) {
            return $available;
        }

        PaymentAllocation::create([
            'payment_id' => $payment->id,
            'invoice_id' => $invoice->id,
            'invoice_item_id' => $installment->invoice_item_id,
            'amount_applied' => $apply,
            'strategy' => $strategy,
            'applied_at' => now(),
        ]);

        $installment->increment('amount_paid', $apply);
        $installment->update(['status' => $this->computeStatus($installment)]);

        $invoice->increment('amount_paid', $apply);
        $invoice->decrement('balance_due', $apply);
        $this->updateInvoiceStatus($invoice);

        if ($parent = $invoice->parentInvoice) {
            $parent->increment('amount_paid', $apply);
            $parent->decrement('balance_due', $apply);
            $this->updateInvoiceStatus($parent);
        }

        return $available - $apply;
    }

    protected function computeStatus(StudentInstallment $installment): string
    {
        $balance = $installment->amount - $installment->amount_paid;
        if ($balance <= 0) {
            return 'paid';
        }

        if ($installment->amount_paid > 0) {
            return 'partial';
        }

        if ($installment->due_date && now()->greaterThan($installment->due_date)) {
            return 'overdue';
        }

        return 'pending';
    }

    protected function updateInvoiceStatus(Invoice $invoice): void
    {
        if ($invoice->balance_due <= 0) {
            $invoice->update(['status' => 'paid']);
        } elseif ($invoice->amount_paid > 0) {
            $invoice->update(['status' => 'partially_paid']);
        } else {
            $invoice->update(['status' => 'issued']);
        }
    }

    protected function generateReceipt(): string
    {
        return 'RCPT-'.now()->format('Ymd').'-'.Str::upper(Str::random(5));
    }
}
