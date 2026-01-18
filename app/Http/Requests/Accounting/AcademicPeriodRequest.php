<?php

namespace App\Http\Requests\Accounting;

use Illuminate\Foundation\Http\FormRequest;

class AcademicPeriodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $period = $this->route('period');
        $periodId = $period instanceof \App\Models\Accounting\AcademicPeriod ? $period->id : $period;

        return [
            'name' => 'required|string|max:150',
            'code' => ($this->isMethod('post') ? 'nullable' : 'required') . '|string|max:50|unique:academic_periods,code,' . ($periodId ?? 'NULL') . ',id',
            'academic_year' => 'required|string|max:25',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'due_date' => 'required|date|after_or_equal:start_date|before_or_equal:end_date',
            'ordering' => 'required|integer|min:1',
            'is_locked' => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'code.unique' => 'Another academic period already uses this generated code. Please adjust the name or edit the existing period instead.',
            'due_date.before_or_equal' => 'The due date must fall within the selected period.',
        ];
    }
}
