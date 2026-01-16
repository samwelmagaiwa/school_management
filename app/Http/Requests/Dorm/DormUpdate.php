<?php

namespace App\Http\Requests\Dorm;

use Illuminate\Foundation\Http\FormRequest;

class DormUpdate extends FormRequest
{

    public function authorize()
    {
        return true;
    }


    public function rules()
    {
        return [
            'name' => 'required|string|unique:dorms,id,name',
            'gender' => 'required|in:male,female,mixed',
            'capacity' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
        ];
    }

}
