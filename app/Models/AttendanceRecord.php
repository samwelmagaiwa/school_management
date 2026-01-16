<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class AttendanceRecord extends Model
{
    protected $fillable = [
        'attendance_session_id', 'student_id', 'status', 'absence_reason', 'remarks', 'marked_by',
    ];

    public function session()
    {
        return $this->belongsTo(AttendanceSession::class, 'attendance_session_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function overrides()
    {
        return $this->hasMany(AttendanceOverride::class);
    }
}
