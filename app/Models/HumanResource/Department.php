<?php

namespace App\Models\HumanResource;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\StaffRecord;

class Department extends Model
{
    use HasFactory;

    protected $table = 'staff_departments';
    protected $fillable = ['name', 'slug'];

    public function staff()
    {
        return $this->hasMany(StaffRecord::class);
    }
}
