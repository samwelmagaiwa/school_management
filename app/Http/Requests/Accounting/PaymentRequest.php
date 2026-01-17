<?php

namespace App\Http\Requests\Accounting;

use Illuminate\Foundation\Http\FormRequest;

class PaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id' => 'nullable|exists:users,id',
            'invoice_id' => 'nullable|exists:invoices,id',
            'student_installment_id' => 'nullable|exists:student_installments,id',
            'allocation_strategy' => 'nullable|in:oldest,specific',
            'amount' => 'required|numeric|min:0.01',
            'method' => 'required|in:cash,bank_transfer,mobile_money,cheque',
            'reference' => 'nullable|string|max:120',
            'received_at' => 'nullable|date',
            'notes' => 'nullable|string',
        ];
    }
}
