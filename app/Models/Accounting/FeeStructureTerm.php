<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeStructureTerm extends Model
{
    use HasFactory;

    protected $fillable = [
        'fee_structure_id',
        'name',
        'sequence',
        'total_amount',
        'installments_enabled',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'installments_enabled' => 'boolean',
        'sequence' => 'integer',
    ];

    /**
     * Get the fee structure that owns this term.
     */
    public function structure()
    {
        return $this->belongsTo(FeeStructure::class, 'fee_structure_id');
    }

    /**
     * Get all installments for this term.
     */
    public function installments()
    {
        return $this->hasMany(FeeInstallment::class, 'fee_structure_term_id');
    }

    /**
     * Calculate the sum of all installment amounts for this term.
     */
    public function getTotalInstallmentsAttribute()
    {
        return $this->installments()->sum('fixed_amount');
    }

    /**
     * Check if installments match the term total.
     */
    public function isBalanced()
    {
        return $this->total_installments == $this->total_amount;
    }
}
