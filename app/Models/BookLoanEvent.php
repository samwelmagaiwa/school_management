<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookLoanEvent extends Model
{
    protected $fillable = [
        'book_loan_id',
        'performed_by',
        'event_type',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function loan()
    {
        return $this->belongsTo(BookLoan::class, 'book_loan_id');
    }

    public function actor()
    {
        return $this->belongsTo(\App\User::class, 'performed_by');
    }
}
