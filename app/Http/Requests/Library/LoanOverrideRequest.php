<?php

namespace App\Http\Requests\Library;

use Illuminate\Foundation\Http\FormRequest;

class LoanOverrideRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

    public function rules()
    {
        return [
            'reason' => 'required|string|min:5',
            'mark_status' => 'nullable|in:available,damaged,lost',
            'returned_at' => 'nullable|date',
        ];
    }
}
