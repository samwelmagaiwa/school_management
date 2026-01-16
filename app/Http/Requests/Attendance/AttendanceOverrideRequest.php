<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceOverrideRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

    public function rules()
    {
        return [
            'status'         => 'required|in:present,absent,late,excused',
            'absence_reason' => 'nullable|in:sick,family_emergency,school_activity,unexcused,other',
            'remarks'        => 'nullable|string|max:500',
            'reason'         => 'required|string|min:5',
        ];
    }
}
