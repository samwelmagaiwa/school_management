<?php

namespace App\Models;

use Eloquent;

class UserType extends Eloquent
{
    protected $fillable = ['title', 'name', 'level'];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }
}
