<?php

namespace App\Http\Requests\Dorm;

use Illuminate\Foundation\Http\FormRequest;

class DormRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100',
            'floor' => 'nullable|integer|min:0',
            'capacity' => 'nullable|integer|min:0',
            'gender' => 'required|in:male,female,mixed',
            'is_active' => 'sometimes|boolean',
            'notes' => 'nullable|string',
            'bed_labels' => 'nullable|string',
        ];
    }
}
