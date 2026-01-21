<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory;

    protected $table = 'inventory_units';
    protected $fillable = ['name', 'abbreviation'];

    public function items()
    {
        return $this->hasMany(Item::class);
    }
}
