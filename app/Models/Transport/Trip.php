<?php

namespace App\Models\Transport;

use App\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    use HasFactory;

    protected $table = 'transport_trips';
    protected $fillable = [
        'vehicle_id', 'driver_id', 'departure_time', 'return_time', 
        'purpose', 'destination', 'start_odometer', 'end_odometer', 
        'distance_covered', 'notes', 'status', 'end_time', 'fuel_consumed_liters'
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }
}
