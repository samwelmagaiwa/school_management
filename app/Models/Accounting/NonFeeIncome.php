<?php

namespace App\Models\Accounting;

use App\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NonFeeIncome extends Model
{
    use HasFactory;

    protected $table = 'non_fee_incomes';

    protected $fillable = [
        'category',
        'amount',
        'payment_method',
        'receipt_number',
        'received_on',
        'recorded_by',
        'reference',
        'description',
    ];

    protected $casts = [
        'received_on' => 'date',
    ];

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
