<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentFeeAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_record_id',
        'fee_structure_id',
        'academic_period_id',
        'scope',
        'my_class_id',
        'section_id',
        'group_tag',
        'status',
    ];

    public function structure()
    {
        return $this->belongsTo(FeeStructure::class, 'fee_structure_id');
    }

    public function period()
    {
        return $this->belongsTo(AcademicPeriod::class, 'academic_period_id');
    }
}
