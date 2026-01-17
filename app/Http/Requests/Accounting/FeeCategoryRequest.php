<?php

namespace App\Http\Requests\Accounting;

use Illuminate\Foundation\Http\FormRequest;

class FeeCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('fee_category')?->id ?? null;

        return [
            'name' => 'required|string|max:120',
            'code' => 'nullable|string|max:50|unique:fee_categories,code,' . $id,
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ];
    }
}
