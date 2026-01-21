<?php

namespace App\Models\Inventory;

use App\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    use HasFactory;

    protected $table = 'inventory_transfers';
    protected $fillable = [
        'transfer_code', 'source_warehouse_id', 'destination_warehouse_id', 
        'item_id', 'quantity', 'status', 'requested_by', 'approved_by', 'notes'
    ];

    public function sourceWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'source_warehouse_id');
    }

    public function destinationWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'destination_warehouse_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
