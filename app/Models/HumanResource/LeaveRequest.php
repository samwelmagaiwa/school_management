<?php

namespace App\Models\HumanResource;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\User;

class LeaveRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_id', 'leave_type_id', 'start_date', 'end_date', 
        'days_requested', 'reason', 'attachment', 'status',
        'approved_by', 'approved_at', 'rejected_by', 'rejected_at', 'rejection_reason'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function type()
    {
        return $this->belongsTo(LeaveType::class, 'leave_type_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
