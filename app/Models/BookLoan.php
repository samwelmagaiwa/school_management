<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class BookLoan extends Model
{
    protected $fillable = [
        'book_copy_id',
        'user_id',
        'processed_by',
        'returned_by',
        'borrowed_at',
        'due_at',
        'returned_at',
        'fine_amount',
        'status',
        'has_override',
        'override_notes',
    ];

    protected $dates = [
        'borrowed_at',
        'due_at',
        'returned_at',
    ];

    protected $casts = [
        'fine_amount' => 'float',
        'has_override' => 'boolean',
    ];

    public function copy()
    {
        return $this->belongsTo(BookCopy::class, 'book_copy_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\User::class);
    }

    public function processedBy()
    {
        return $this->belongsTo(\App\User::class, 'processed_by');
    }

    public function returnedBy()
    {
        return $this->belongsTo(\App\User::class, 'returned_by');
    }

    public function request()
    {
        return $this->hasOne(BookRequest::class, 'book_loan_id');
    }

    public function events()
    {
        return $this->hasMany(BookLoanEvent::class, 'book_loan_id');
    }

    public function getIsOverdueAttribute(): bool
    {
        if ($this->returned_at) {
            return false;
        }

        return now()->greaterThan(Carbon::parse($this->due_at));
    }

    public function getDaysOverdueAttribute(): int
    {
        if ($this->returned_at || ! $this->due_at) {
            return 0;
        }

        $due = Carbon::parse($this->due_at);
        if (now()->lessThanOrEqualTo($due)) {
            return 0;
        }

        return max(1, $due->diffInDays(now()));
    }
}
