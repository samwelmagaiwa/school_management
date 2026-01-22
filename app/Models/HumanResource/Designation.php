<?php

namespace App\Models\HumanResource;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\StaffRecord;

class Designation extends Model
{
    use HasFactory;

    protected $table = 'staff_designations';
    protected $fillable = ['title'];

    public function staff()
    {
        return $this->hasMany(StaffRecord::class);
    }
}
