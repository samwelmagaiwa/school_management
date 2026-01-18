<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeInstallmentItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'fee_installment_id',
        'fee_item_id',
        'name',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /**
     * Get the installment that owns this item.
     */
    public function installment()
    {
        return $this->belongsTo(FeeInstallment::class, 'fee_installment_id');
    }

    /**
     * Get the fee item template (optional).
     */
    public function feeItem()
    {
        return $this->belongsTo(FeeItem::class);
    }
}
