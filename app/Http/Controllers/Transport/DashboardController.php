<?php

namespace App\Http\Controllers\Transport;

use App\Http\Controllers\Controller;
use App\Helpers\Qs;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $d = [];
        
        // Transport Stats
        $d['total_vehicles'] = DB::table('transport_vehicles')->count();
        $d['active_vehicles'] = DB::table('transport_vehicles')->where('status', 'active')->count();
        $d['total_trips'] = DB::table('transport_trips')->count();
        $d['pending_trips'] = DB::table('transport_trips')->where('status', 'pending')->count();
        $d['completed_trips'] = DB::table('transport_trips')->where('status', 'completed')->count();

        // Recent Trips
        $d['recent_trips'] = DB::table('transport_trips')
            ->join('transport_vehicles', 'transport_trips.vehicle_id', '=', 'transport_vehicles.id')
            ->select('transport_vehicles.plate_no', 'transport_trips.route_name', 'transport_trips.trip_date', 'transport_trips.status')
            ->orderBy('transport_trips.created_at', 'desc')
            ->limit(5)
            ->get();

        return view('pages.transport.dashboard', $d);
    }
}
