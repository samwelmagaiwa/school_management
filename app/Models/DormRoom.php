<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DormRoom extends Model
{
    use HasFactory;

    protected $fillable = [
        'dorm_id',
        'name',
        'floor',
        'capacity',
        'gender',
        'is_active',
        'bed_count',
        'notes',
    ];

    public function dorm()
    {
        return $this->belongsTo(Dorm::class);
    }

    public function beds()
    {
        return $this->hasMany(DormBed::class);
    }
}
