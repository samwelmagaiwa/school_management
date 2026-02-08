<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'school_code',
        'status',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function users()
    {
        return $this->hasMany(\App\User::class);
    }
}
