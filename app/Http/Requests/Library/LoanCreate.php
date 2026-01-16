<?php

namespace App\Http\Requests\Library;

use Illuminate\Foundation\Http\FormRequest;

class LoanCreate extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

    public function rules()
    {
        return [
            'book_id' => 'nullable|integer|exists:books,id',
            'book_copy_id' => 'nullable|integer|exists:book_copies,id',
            'borrower_id' => 'nullable|integer|exists:users,id',
            'due_at' => 'nullable|date|after_or_equal:today',
        ];
    }
}
