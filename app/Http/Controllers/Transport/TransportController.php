<?php

namespace App\Http\Controllers\Transport;

use App\Http\Controllers\Controller;
use App\Models\Transport\Vehicle;
use App\Models\Transport\Trip;
use App\Models\Transport\FuelLog;
use App\Helpers\Qs;
use Illuminate\Http\Request;

class TransportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:transport.manage');
    }

    public function index()
    {
        $d['vehicles'] = Vehicle::with('driver')->get();
        return view('pages.transport.index', $d);
    }

    public function storeVehicle(Request $req)
    {
        $data = $req->validate([
            'plate_number' => 'required|unique:transport_vehicles,plate_number',
            'type' => 'required|string',
            'model' => 'nullable|string',
            'driver_id' => 'nullable|exists:users,id',
        ]);

        Vehicle::create($data);
        return Qs::jsonStoreOk();
    }

    public function storeTrip(Request $req)
    {
        // Simple logic for booking a trip
        $data = $req->validate([
            'vehicle_id' => 'required|exists:transport_vehicles,id',
            'purpose' => 'required|string',
            'destination' => 'required|string',
            'start_odometer' => 'required|numeric',
            'departure_time' => 'required|date',
        ]);

        $data['status'] = 'Ongoing';
        Trip::create($data);
        return Qs::jsonStoreOk();
    }

    public function completeTrip(Request $req, $id)
    {
        $trip = Trip::findOrFail($id);
        
        if ($trip->status !== 'Ongoing') {
            return back()->with('flash_danger', 'This trip is already completed.');
        }

        $validated = $req->validate([
            'end_odometer' => 'required|numeric|gt:' . $trip->start_odometer,
            'fuel_consumed' => 'nullable|numeric|min:0',
        ]);

        $distance = $validated['end_odometer'] - $trip->start_odometer;

        $trip->update([
            'end_odometer' => $validated['end_odometer'],
            'end_time' => now(),
            'distance_covered' => $distance,
            'fuel_consumed_liters' => $validated['fuel_consumed'] ?? null,
            'status' => 'Completed',
        ]);

        return back()->with('flash_success', 'Trip completed successfully!');
    }

    public function storeFuel(Request $req)
    {
        $data = $req->validate([
            'vehicle_id' => 'required|exists:transport_vehicles,id',
            'date' => 'required|date',
            'liters' => 'required|numeric|min:0',
            'cost_per_liter' => 'required|numeric|min:0',
            'odometer_reading' => 'required|numeric|min:0',
            'invoice_number' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $data['total_cost'] = $data['liters'] * $data['cost_per_liter'];
        $data['issued_by'] = auth()->id();

        FuelLog::create($data);
        return Qs::jsonStoreOk();
    }
}
