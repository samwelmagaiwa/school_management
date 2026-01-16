<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class AttendanceOverride extends Model
{
    protected $fillable = [
        'attendance_record_id', 'previous_status', 'previous_absence_reason', 'new_status', 'new_absence_reason',
        'previous_remarks', 'new_remarks', 'reason', 'performed_by',
    ];

    public function record()
    {
        return $this->belongsTo(AttendanceRecord::class, 'attendance_record_id');
    }

    public function performedBy()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
