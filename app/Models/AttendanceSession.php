<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class AttendanceSession extends Model
{
    protected $fillable = [
        'date', 'my_class_id', 'section_id', 'subject_id', 'time_slot_id',
        'type', 'taken_by', 'status', 'submitted_by', 'submitted_at',
        'locked_by', 'locked_at', 'created_by', 'updated_by',
    ];

    protected $dates = ['date', 'submitted_at', 'locked_at'];

    public function my_class()
    {
        return $this->belongsTo(MyClass::class);
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function time_slot()
    {
        return $this->belongsTo(TimeSlot::class, 'time_slot_id');
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'taken_by');
    }

    public function records()
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function events()
    {
        return $this->hasMany(AttendanceEvent::class, 'attendance_session_id');
    }
}
