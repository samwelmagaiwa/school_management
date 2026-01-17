<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeInstallment extends Model
{
    use HasFactory;

    protected $fillable = [
        'fee_installment_plan_id',
        'sequence',
        'label',
        'percentage',
        'fixed_amount',
        'due_date',
        'grace_days',
        'late_penalty_type',
        'late_penalty_value',
    ];

    protected $casts = [
        'percentage' => 'decimal:2',
        'fixed_amount' => 'decimal:2',
        'due_date' => 'date',
        'grace_days' => 'integer',
        'late_penalty_value' => 'decimal:2',
    ];

    public function plan()
    {
        return $this->belongsTo(FeeInstallmentPlan::class, 'fee_installment_plan_id');
    }

    public function studentInstallments()
    {
        return $this->hasMany(StudentInstallment::class);
    }
}
