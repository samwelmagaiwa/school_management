<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = ['name', 'head_id'];

    public function head()
    {
        return $this->belongsTo(User::class, 'head_id');
    }

    public function classes()
    {
        return $this->hasMany(MyClass::class, 'department_id');
    }

    public function subjects()
    {
        return $this->hasMany(Subject::class, 'department_id');
    }
}
