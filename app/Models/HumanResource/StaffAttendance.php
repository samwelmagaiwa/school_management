<?php

namespace App\Models\HumanResource;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\User;

class StaffAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_id', 'date', 'clock_in_time', 'clock_out_time',
        'status', 'remarks', 'is_late', 'is_early_departure', 'recorded_by'
    ];

    protected $casts = [
        'date' => 'date',
        'is_late' => 'boolean',
        'is_early_departure' => 'boolean',
    ];

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
