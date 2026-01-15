<?php

namespace App\Models;

use Eloquent;

class Lga extends Eloquent
{
    // Underlying table is now "districts" instead of "lgas"
    protected $table = 'districts';

    public function ministry()
    {
       // return $this->hasMany(Ministry::class);
    }
}
