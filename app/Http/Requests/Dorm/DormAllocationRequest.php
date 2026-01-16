<?php

namespace App\Http\Requests\Dorm;

use Illuminate\Foundation\Http\FormRequest;

class DormAllocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'dorm_bed_id' => 'required|exists:dorm_beds,id',
            'notes' => 'nullable|string',
        ];
    }
}
