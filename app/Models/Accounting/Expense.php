<?php

namespace App\Models\Accounting;

use App\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'title',
        'category',
        'amount',
        'expense_date',
        'payment_method',
        'reference',
        'status',
        'recorded_by',
        'approved_by',
        'approved_at',
        'notes',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'approved_at' => 'datetime',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
