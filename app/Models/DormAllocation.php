<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DormAllocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_record_id',
        'dorm_id',
        'dorm_room_id',
        'dorm_bed_id',
        'assigned_by',
        'vacated_by',
        'assigned_at',
        'vacated_at',
        'status',
        'notes',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'vacated_at' => 'datetime',
    ];

    public function dorm()
    {
        return $this->belongsTo(Dorm::class);
    }

    public function room()
    {
        return $this->belongsTo(DormRoom::class, 'dorm_room_id');
    }

    public function bed()
    {
        return $this->belongsTo(DormBed::class, 'dorm_bed_id');
    }

    public function studentRecord()
    {
        return $this->belongsTo(StudentRecord::class);
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function vacatedBy()
    {
        return $this->belongsTo(User::class, 'vacated_by');
    }
}
