<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'inventory_suppliers';
    protected $fillable = ['name', 'contact_person', 'email', 'phone', 'address'];

    public function assets()
    {
        return $this->hasMany(Asset::class);
    }
}
