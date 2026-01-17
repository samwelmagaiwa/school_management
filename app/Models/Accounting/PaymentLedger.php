<?php

namespace App\Models\Accounting;

use App\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentLedger extends Model
{
    use HasFactory;

    protected $table = 'payments_ledger';

    protected $fillable = [
        'receipt_number',
        'student_id',
        'amount',
        'method',
        'reference',
        'received_at',
        'recorded_by',
        'currency',
        'source',
        'status',
        'notes',
    ];

    protected $casts = [
        'received_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function allocations()
    {
        return $this->hasMany(PaymentAllocation::class, 'payment_id');
    }
}
