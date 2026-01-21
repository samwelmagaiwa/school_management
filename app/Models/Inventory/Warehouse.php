<?php

namespace App\Models\Inventory;

use App\User; // Or App\Models\User depending on project structure
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warehouse extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'inventory_warehouses';
    protected $fillable = ['name', 'location', 'description', 'contact_number', 'keeper_id', 'is_active'];

    public function keeper()
    {
        return $this->belongsTo(User::class, 'keeper_id');
    }

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }

    public function assets()
    {
        return $this->hasMany(Asset::class);
    }
}
