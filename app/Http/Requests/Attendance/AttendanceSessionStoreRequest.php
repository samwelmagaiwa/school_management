<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceSessionStoreRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

    public function rules()
    {
        return [
            'date'        => 'required|date',
            'my_class_id' => 'required|integer|exists:my_classes,id',
            'section_id'  => 'nullable|integer|exists:sections,id',
            'subject_id'  => 'nullable|integer|exists:subjects,id',
            'time_slot_id'=> 'nullable|integer|exists:time_slots,id',
            'type'        => 'required|in:daily,subject,event',
        ];
    }
}
