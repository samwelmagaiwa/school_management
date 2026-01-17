<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'fee_category_id',
        'name',
        'code',
        'default_amount',
        'is_optional',
        'is_recurring',
        'gl_code',
        'description',
        'is_active',
        'allows_installments',
    ];

    protected $casts = [
        'default_amount' => 'decimal:2',
        'is_optional' => 'boolean',
        'is_recurring' => 'boolean',
        'is_active' => 'boolean',
        'allows_installments' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(FeeCategory::class, 'fee_category_id');
    }
}
