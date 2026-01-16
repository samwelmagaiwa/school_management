<?php

namespace App\Models;

use Eloquent;

class Village extends Eloquent
{
    protected $fillable = ['ward_id', 'name'];

    public function places()
    {
        return $this->hasMany(Place::class);
    }
}
