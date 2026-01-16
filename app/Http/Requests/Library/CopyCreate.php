<?php

namespace App\Http\Requests\Library;

use Illuminate\Foundation\Http\FormRequest;

class CopyCreate extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

    public function rules()
    {
        return [
            // Book is determined from the route parameter; we only need quantity here
            'quantity' => 'required|integer|min:1|max:100',
        ];
    }
}
