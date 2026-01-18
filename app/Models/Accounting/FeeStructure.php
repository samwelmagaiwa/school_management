<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeStructure extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'academic_year',
        'academic_period_id',
        'total_year_amount',
        'due_date',
        'status',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    public function academicPeriod()
    {
        return $this->belongsTo(AcademicPeriod::class);
    }

    public function installmentPlans()
    {
        return $this->hasMany(FeeInstallmentPlan::class);
    }

    public function items()
    {
        return $this->hasMany(FeeStructureItem::class);
    }

    /**
     * Get all terms for this fee structure (NEW HIERARCHY).
     */
    public function terms()
    {
        return $this->hasMany(FeeStructureTerm::class);
    }
}
