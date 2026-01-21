<?php

namespace App\Models\Transport;

use App\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FuelLog extends Model
{
    use HasFactory;

    protected $table = 'transport_fuel_logs';
    protected $fillable = [
        'vehicle_id', 'date', 'liters', 'cost_per_liter', 'total_cost', 
        'odometer_reading', 'is_full_tank', 'issued_by', 'invoice_number', 'notes'
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function issuer()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }
}
