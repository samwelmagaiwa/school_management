<?php

namespace App\Services\Accounting;

use App\Models\Accounting\FeeInstallment;
use App\Models\Accounting\FeeInstallmentPlan;
use App\Models\Accounting\FeeItem;
use App\Models\Accounting\Invoice;
use App\Models\Accounting\StudentInstallment;
use Illuminate\Support\Facades\DB;

class InstallmentScheduler
{
    /**
     * Generate installment invoices and schedules for a parent invoice when a fee structure
     * is configured with an active installment plan.
     */
    public function generateForInvoice(Invoice $invoice): void
    {
        if (! $invoice->fee_structure_id) {
            return;
        }

        $plan = FeeInstallmentPlan::where('fee_structure_id', $invoice->fee_structure_id)
            ->where('is_active', true)
            ->with('installments')
            ->first();

        if (! $plan || $plan->installments->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($invoice, $plan) {
            $invoice->loadMissing('items');
            $installmentInvoices = [];

            // Sum all fee items to get total invoice amount for proration
            $totalInvoiceAmount = $invoice->items->sum(function ($item) {
                return $item->total_amount ?? ($item->quantity * $item->unit_amount);
            });

            foreach ($invoice->items as $item) {
                if (! $item->fee_item_id) {
                    continue;
                }

                /** @var FeeItem|null $feeItem */
                $feeItem = FeeItem::find($item->fee_item_id);
                if (! $feeItem || ! $feeItem->allows_installments) {
                    continue;
                }

                $baseAmount = $item->total_amount ?? ($item->quantity * $item->unit_amount);

                foreach ($plan->installments as $definition) {
                    // Pass totalInvoiceAmount for fixed installment proration
                    $portion = $this->calculatePortion($definition, (float) $baseAmount, (float) $totalInvoiceAmount);

                    if ($portion <= 0) {
                        continue;
                    }

                    $installmentInvoice = $installmentInvoices[$definition->sequence] ??= $this->createInstallmentInvoice($invoice, $definition);

                    $installmentInvoice->items()->create([
                        'fee_item_id' => $item->fee_item_id,
                        'description' => ($item->description ?? 'Installment').' - '.$definition->label,
                        'quantity' => $item->quantity,
                        'unit_amount' => $portion,
                        'total_amount' => $portion,
                        'discount_amount' => 0,
                        'waiver_amount' => 0,
                        'is_optional' => $item->is_optional,
                    ]);

                    StudentInstallment::create([
                        'student_id'         => $invoice->student_id,
                        'invoice_id'         => $installmentInvoice->id,
                        'invoice_item_id'    => $item->id,
                        'fee_structure_id'   => $invoice->fee_structure_id,
                        'fee_installment_id' => $definition->id,
                        'amount'             => $portion,
                        'amount_paid'        => 0,
                        'due_date'           => $definition->due_date ?? $invoice->due_date,
                        'status'             => 'pending',
                        'last_payment_at'    => null,
                    ]);

                    $installmentInvoice->update([
                        'subtotal_amount' => $installmentInvoice->subtotal_amount + $portion,
                        'total_amount' => $installmentInvoice->total_amount + $portion,
                        'balance_due' => $installmentInvoice->balance_due + $portion,
                    ]);
                }
            }

            if (! empty($installmentInvoices)) {
                $invoice->update([
                    'is_installment' => false,
                    'status' => 'issued',
                ]);
            }
        });
    }

    protected function calculatePortion(FeeInstallment $definition, float $baseAmount, float $totalInvoiceAmount = 0.0): float
    {
        if ($definition->fixed_amount !== null) {
            if ($totalInvoiceAmount > 0) {
                $ratio = $baseAmount / $totalInvoiceAmount;
                return round((float) $definition->fixed_amount * $ratio, 2);
            }
            return 0.0;
        }

        if ($definition->percentage !== null) {
            return round($baseAmount * ((float) $definition->percentage / 100), 2);
        }

        return 0.0;
    }

    protected function createInstallmentInvoice(Invoice $parent, FeeInstallment $definition): Invoice
    {
        return Invoice::create([
            'invoice_number' => $parent->invoice_number.'-I'.$definition->sequence,
            'parent_invoice_id' => $parent->id,
            'is_installment' => true,
            'installment_sequence' => $definition->sequence,
            'installment_label' => $definition->label,
            'student_id' => $parent->student_id,
            'student_record_id' => $parent->student_record_id,
            'fee_structure_id' => $parent->fee_structure_id,
            'academic_period_id' => $parent->academic_period_id,
            'status' => 'issued',
            'issued_by' => $parent->issued_by,
            'issued_at' => $parent->issued_at,
            'due_date' => $definition->due_date ?? $parent->due_date,
            'subtotal_amount' => 0,
            'discount_total' => 0,
            'penalty_total' => 0,
            'total_amount' => 0,
            'amount_paid' => 0,
            'balance_due' => 0,
            'currency' => $parent->currency,
            'notes' => 'Auto installment invoice: '.$definition->label,
        ]);
    }
}
