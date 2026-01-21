<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\User;

class StockMovement extends Model
{
    use HasFactory;

    protected $table = 'inventory_stock_movements';
    protected $fillable = [
        'item_id',
        'warehouse_id',
        'user_id',
        'type',
        'quantity',
        'reference',
        'description'
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
