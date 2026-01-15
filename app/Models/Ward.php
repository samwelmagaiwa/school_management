<?php

namespace App\Models;

use Eloquent;

class Ward extends Eloquent
{
    protected $fillable = ['lga_id', 'name'];
}
