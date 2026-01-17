<?php

namespace App\Http\Controllers\Accounting;

use App\Helpers\Qs;
use App\Http\Controllers\Controller;
use App\Http\Requests\Accounting\InvoiceRequest;
use App\Http\Requests\Accounting\PaymentRequest;
use App\Models\Accounting\AcademicPeriod;
use App\Models\Accounting\Invoice;
use App\Models\Accounting\PaymentLedger;
use App\Models\Accounting\PaymentAllocation;
use App\Models\Accounting\StudentInstallment;
use App\Services\Accounting\AccountingPermissionService;
use App\Services\Accounting\AccountingSecurityLogger;
use App\Services\Accounting\InstallmentScheduler;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountingActionController extends Controller
{
    public function __construct(
        protected AccountingPermissionService $permissions,
        protected InstallmentScheduler $installments
    ) {
        $this->middleware('auth');
        $this->middleware('teamAccount');
    }

    public function storeInvoice(InvoiceRequest $request)
    {
        $this->permissions->ensure('invoice.create');

        $payload = $request->validated();
        $period = AcademicPeriod::findOrFail($payload['academic_period_id']);
        $this->permissions->ensureNotLocked($period);

        $invoice = DB::transaction(function () use ($payload) {
            $items = $payload['items'];
            unset($payload['items']);

            $payload['invoice_number'] = $this->generateInvoiceNumber();
            $payload['issued_by'] = auth()->id();
            $payload['issued_at'] = now();
            $payload['status'] = 'issued';
            $payload['subtotal_amount'] = collect($items)->sum(fn ($item) => $item['unit_amount'] * $item['quantity']);
            $payload['total_amount'] = $payload['subtotal_amount'];
            $payload['balance_due'] = $payload['subtotal_amount'];

            $invoice = Invoice::create($payload);
            $invoice->items()->createMany($items);

            return $invoice;
        });

        $this->installments->generateForInvoice($invoice);

        AccountingSecurityLogger::log('invoice.created', $invoice, ['new' => $invoice->toArray()]);

        return Qs::jsonStoreOk();
    }

    public function recordPayment(PaymentRequest $request)
    {
        $this->permissions->ensure('payments.record');

        $payload = $request->validated();
        $payload['received_at'] = $payload['received_at'] ? Carbon::parse($payload['received_at']) : now();
        $payload['recorded_by'] = auth()->id();
        $payload['receipt_number'] = $this->generateReceiptNumber();

        $period = AcademicPeriod::where('start_date', '<=', $payload['received_at'])
            ->where('end_date', '>=', $payload['received_at'])
            ->first();

        $this->permissions->ensureNotLocked($period);

        $payment = DB::transaction(function () use ($payload) {
            $studentInstallmentId = $payload['student_installment_id'] ?? null;
            unset($payload['student_installment_id']);

            $payment = PaymentLedger::create($payload);

            if ($studentInstallmentId) {
                /** @var StudentInstallment|null $installment */
                $installment = StudentInstallment::with('invoice.period')->find($studentInstallmentId);
                if ($installment) {
                    // Prevent allocating payments into locked invoice periods
                    if ($installment->invoice && $installment->invoice->period) {
                        app(\App\Services\Accounting\AccountingPermissionService::class)
                            ->ensureNotLocked($installment->invoice->period);
                    }

                    $remaining = max(0, (float) $installment->amount - (float) $installment->amount_paid);
                    $apply = min($remaining, (float) $payment->amount);

                    if ($apply > 0) {
                        $allocation = PaymentAllocation::create([
                            'payment_id' => $payment->id,
                            'invoice_id' => $installment->invoice_id,
                            'invoice_item_id' => $installment->invoice_item_id,
                            'student_installment_id' => $installment->id,
                            'amount_applied' => $apply,
                            'strategy' => 'specific_item',
                            'applied_at' => $payload['received_at'],
                        ]);

                        $installment->amount_paid = (float) $installment->amount_paid + $apply;
                        if ($installment->amount_paid >= $installment->amount) {
                            $installment->status = 'paid';
                        } elseif ($installment->amount_paid > 0 && $installment->status === 'pending') {
                            $installment->status = 'partial';
                        }
                        $installment->last_payment_at = $allocation->applied_at;
                        $installment->save();

                        if ($invoice = $installment->invoice) {
                            $invoice->amount_paid = (float) $invoice->amount_paid + $apply;
                            $invoice->balance_due = max(0, (float) $invoice->total_amount - (float) $invoice->amount_paid);
                            if ($invoice->balance_due <= 0) {
                                $invoice->status = 'paid';
                            } elseif ($invoice->amount_paid > 0 && $invoice->status !== 'paid') {
                                $invoice->status = 'partially_paid';
                            }
                            $invoice->save();
                        }

                        if ($apply >= (float) $payment->amount) {
                            $payment->status = 'allocated';
                            $payment->save();
                        }
                    }
                }
            }

            return $payment;
        });

        AccountingSecurityLogger::log('payment.recorded', $payment, ['new' => $payment->toArray()]);

        return Qs::jsonStoreOk();
    }

    public function reversePayment(Request $request, PaymentLedger $payment): RedirectResponse
    {
        $this->permissions->ensure('payments.reverse', $payment);

        $reason = $request->input('reason');

        DB::transaction(function () use ($payment, $reason) {
            $old = $payment->toArray();
            $payment->update(['status' => 'refunded', 'notes' => trim($payment->notes.' | Reversed: '.$reason)]);
            AccountingSecurityLogger::log('payment.reversed', $payment, [
                'old' => $old,
                'new' => $payment->toArray(),
                'description' => $reason,
            ]);
        });

        return back()->with('flash_success', __('msg.update_ok'));
    }

    public function approveWaiver(Request $request, Invoice $invoice)
    {
        $this->permissions->ensure('payments.waive', $invoice);
        $amount = $request->input('amount');
        $notes = $request->input('notes');

        $old = $invoice->toArray();
        $invoice->update([
            'discount_total' => $invoice->discount_total + $amount,
            'balance_due' => max(0, $invoice->balance_due - $amount),
        ]);

        AccountingSecurityLogger::log('invoice.waiver_approved', $invoice, [
            'old' => $old,
            'new' => $invoice->toArray(),
            'description' => $notes,
        ]);

        return back()->with('flash_success', __('msg.update_ok'));
    }

    public function lockPeriod(Request $request, AcademicPeriod $period)
    {
        $this->permissions->ensure('locks.manage');
        $this->permissions->lockPeriod($period, $request->input('reason'));
        return back()->with('flash_success', __('msg.update_ok'));
    }

    public function unlockPeriod(Request $request, AcademicPeriod $period)
    {
        $this->permissions->ensure('periods.unlock');
        $this->permissions->unlockPeriod($period, $request->input('reason'));
        return back()->with('flash_success', __('msg.update_ok'));
    }

    protected function generateInvoiceNumber(): string
    {
        return 'INV-'.now()->format('Ymd').'-'.str_pad((string) (Invoice::count() + 1), 5, '0', STR_PAD_LEFT);
    }

    protected function generateReceiptNumber(): string
    {
        return 'RCPT-'.now()->format('Ymd').'-'.str_pad((string) (PaymentLedger::count() + 1), 5, '0', STR_PAD_LEFT);
    }
}
