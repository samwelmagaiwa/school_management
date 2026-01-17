<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicPeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'code', 'academic_year', 'start_date', 'end_date', 'due_date', 'ordering', 'is_locked'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'due_date' => 'date',
        'is_locked' => 'boolean',
    ];

    public function feeStructures()
    {
        return $this->hasMany(FeeStructure::class);
    }
}
