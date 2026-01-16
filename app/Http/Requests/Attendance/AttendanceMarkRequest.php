<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceMarkRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

    public function rules()
    {
        return [
            'records'                  => 'required|array|min:1',
            'records.*.student_id'     => 'required|integer|exists:users,id',
            // "unmarked" is internal only; teachers must pick a concrete status.
            'records.*.status'         => 'required|in:present,absent,late,excused',
            'records.*.absence_reason' => 'nullable|in:sick,family_emergency,school_activity,unexcused,other',
            'records.*.remarks'        => 'nullable|string|max:500',
        ];
    }
}
