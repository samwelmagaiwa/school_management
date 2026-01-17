<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountingAuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'event',
        'model_type',
        'model_id',
        'old_values',
        'new_values',
        'user_id',
        'ip_address',
        'description',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(\App\User::class);
    }
}
