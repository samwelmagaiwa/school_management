<?php

namespace App\Models;

use App\User;
use Eloquent;

class StaffRecord extends Eloquent
{
    protected $fillable = ['code', 'emp_date', 'user_id', 'department_id', 'designation_id', 'employment_type', 'basic_salary', 'date_of_hire', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
