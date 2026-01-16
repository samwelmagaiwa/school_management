<?php

namespace App\Http\Requests\Dorm;

use Illuminate\Foundation\Http\FormRequest;

class DormBedRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        if ($this->isMethod('post')) {
            return [
                'labels' => 'required|string',
            ];
        }

        return [
            'label' => 'required|string|max:100',
            'status' => 'required|in:available,occupied,reserved,maintenance',
            'is_active' => 'sometimes|boolean',
        ];
    }
}
