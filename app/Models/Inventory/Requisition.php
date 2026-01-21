<?php

namespace App\Models\Inventory;

use App\Models\Department;
use App\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Requisition extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'inventory_requisitions';
    protected $fillable = [
        'reference_code', 'requester_id', 'department_id', 'type', 
        'status', 'date_needed', 'reason', 
        'approved_by', 'approved_at', 'rejection_reason'
    ];

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items()
    {
        return $this->hasMany(RequisitionItem::class);
    }

    public function issuances()
    {
        return $this->hasMany(Issuance::class);
    }
}
