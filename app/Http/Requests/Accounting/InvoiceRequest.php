<?php

namespace App\Http\Requests\Accounting;

use Illuminate\Foundation\Http\FormRequest;

class InvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id' => 'required|exists:users,id',
            'student_record_id' => 'nullable|exists:student_records,id',
            'fee_structure_id' => 'nullable|exists:fee_structures,id',
            'academic_period_id' => 'required|exists:academic_periods,id',
            'due_date' => 'nullable|date',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_amount' => 'required|numeric|min:0',
            'items.*.fee_item_id' => 'nullable|exists:fee_items,id',
        ];
    }
}
