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

    /**
     * Get the term that owns this installment (NEW HIERARCHY).
     */
    public function term()
    {
        return $this->belongsTo(FeeStructureTerm::class, 'fee_structure_term_id');
    }

    /**
     * Get all items for this installment (NEW HIERARCHY).
     */
    public function items()
    {
        return $this->hasMany(FeeInstallmentItem::class);
    }

    /**
     * Calculate the sum of all item amounts for this installment.
     */
    public function getTotalItemsAttribute()
    {
        return $this->items()->sum('amount');
    }

    /**
     * Check if items match the installment total.
     */
    public function isBalanced()
    {
        return $this->total_items == $this->fixed_amount;
    }
}
