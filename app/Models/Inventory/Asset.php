<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asset extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'inventory_assets';
    protected $fillable = [
        'item_id', 'warehouse_id', 'unique_tag', 'serial_number', 
        'condition', 'status', 'purchase_date', 'purchase_cost', 
        'depreciation_rate', 'supplier_id', 'notes'
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
