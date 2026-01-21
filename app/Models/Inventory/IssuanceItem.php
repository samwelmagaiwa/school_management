<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IssuanceItem extends Model
{
    use HasFactory;

    protected $table = 'inventory_issuance_items';
    protected $fillable = [
        'issuance_id', 'item_id', 'inventory_asset_id', 
        'quantity', 'source_warehouse_id'
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class, 'inventory_asset_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'source_warehouse_id');
    }
}
