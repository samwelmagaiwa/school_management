<?php

namespace App\Models\Transport;

use App\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'transport_vehicles';
    protected $fillable = [
        'plate_number', 'make', 'model', 'type', 'year', 'driver_id', 
        'status', 'fuel_type', 'current_mileage', 
        'insurance_expiry', 'last_service_date', 'next_service_date'
    ];

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function fuelLogs()
    {
        return $this->hasMany(FuelLog::class);
    }

    public function trips()
    {
        return $this->hasMany(Trip::class);
    }

    public function maintenanceLogs()
    {
        return $this->morphMany(MaintenanceLog::class, 'maintainable');
    }
}
