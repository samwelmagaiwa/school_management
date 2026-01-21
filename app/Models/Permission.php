<?php

namespace App\Models;

use Eloquent;

class Permission extends Eloquent
{
    protected $fillable = ['name', 'title', 'description', 'slug'];

    public function user_types()
    {
        return $this->belongsToMany(UserType::class);
    }
}
