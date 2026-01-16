<?php

namespace App\Http\Requests\Library;

use Illuminate\Foundation\Http\FormRequest;

class BorrowRequestDecision extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

    public function rules()
    {
        return [
            'book_copy_id' => 'nullable|integer|exists:book_copies,id',
            'borrower_id' => 'nullable|integer|exists:users,id',
            'reason' => 'nullable|string|min:5',
            'due_at' => 'nullable|date|after_or_equal:today',
        ];
    }
}
