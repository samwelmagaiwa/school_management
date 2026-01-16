<?php

namespace App\Http\Requests\Library;

use Illuminate\Foundation\Http\FormRequest;

class BookCreate extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

    public function rules()
    {
        $currentYear = (int) date('Y') + 1; // allow next-year publications

        return [
            'name'        => 'required|string|max:191',
            'author'      => 'required|string|max:191',
            'isbn'        => 'nullable|string|max:191',
'category'    => 'nullable|exists:book_categories,name',
            'subject'     => 'nullable|string|max:191',
            'edition'     => 'nullable|string|max:100',
            'publisher'   => 'nullable|string|max:191',
            'publication_year' => 'nullable|integer|min:1000|max:'.$currentYear,
            'language'    => 'nullable|string|max:100',
            'book_type'   => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'my_class_id' => 'nullable|integer|exists:my_classes,id',
            'location'    => 'nullable|string|max:191',
            'url'         => 'nullable|string|max:191',
            'is_reference_only' => 'nullable|boolean',
            'total_copies'      => 'nullable|integer|min:0',
            'cover_image'       => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ];
    }
}
