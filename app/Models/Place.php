<?php

namespace App\Models;

use Eloquent;

class Place extends Eloquent
{
    protected $fillable = ['village_id', 'name'];

    public function village()
    {
        return $this->belongsTo(Village::class);
    }
}
