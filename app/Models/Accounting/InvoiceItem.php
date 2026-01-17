<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'fee_item_id',
        'description',
        'quantity',
        'unit_amount',
        'total_amount',
        'discount_amount',
        'waiver_amount',
        'is_optional',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
