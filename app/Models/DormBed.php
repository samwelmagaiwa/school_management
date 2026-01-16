<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DormBed extends Model
{
    use HasFactory;

    protected $fillable = [
        'dorm_id',
        'dorm_room_id',
        'label',
        'status',
        'is_active',
        'current_allocation_id',
    ];

    public function dorm()
    {
        return $this->belongsTo(Dorm::class);
    }

    public function room()
    {
        return $this->belongsTo(DormRoom::class, 'dorm_room_id');
    }

    public function currentAllocation()
    {
        return $this->belongsTo(DormAllocation::class, 'current_allocation_id');
    }
}
