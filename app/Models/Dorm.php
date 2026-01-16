<?php

namespace App\Models;

use Eloquent;

class Dorm extends Eloquent
{
    protected $fillable = [
        'name',
        'description',
        'gender',
        'capacity',
        'room_count',
        'bed_count',
        'notes',
    ];

    public function rooms()
    {
        return $this->hasMany(DormRoom::class);
    }

    public function beds()
    {
        return $this->hasManyThrough(DormBed::class, DormRoom::class);
    }
}
