<?php

namespace App\Models\Transport;

use App\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceLog extends Model
{
    use HasFactory;

    protected $table = 'maintenance_logs';
    protected $fillable = [
        'maintainable_id', 'maintainable_type', 'type', 'title', 'description', 
        'cost', 'service_provider', 'service_date', 'next_due_date', 
        'invoice_file', 'reported_by'
    ];

    public function maintainable()
    {
        return $this->morphTo();
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }
}
