<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentAllocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id',
        'invoice_id',
        'invoice_item_id',
        'amount_applied',
        'strategy',
        'applied_at',
    ];

    protected $casts = [
        'amount_applied' => 'decimal:2',
        'applied_at' => 'datetime',
    ];

    public function payment()
    {
        return $this->belongsTo(PaymentLedger::class, 'payment_id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
