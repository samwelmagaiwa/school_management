<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeStructureItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'fee_structure_id',
        'fee_item_id',
        'my_class_id',
        'section_id',
        'amount',
        'is_optional',
        'is_recurring',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_optional' => 'boolean',
        'is_recurring' => 'boolean',
    ];

    public function structure()
    {
        return $this->belongsTo(FeeStructure::class, 'fee_structure_id');
    }

    public function item()
    {
        return $this->belongsTo(FeeItem::class, 'fee_item_id');
    }
}
