<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeInstallmentPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'fee_structure_id',
        'name',
        'is_active',
    ];

    public function structure()
    {
        return $this->belongsTo(FeeStructure::class, 'fee_structure_id');
    }

    public function installments()
    {
        return $this->hasMany(FeeInstallment::class);
    }
}
