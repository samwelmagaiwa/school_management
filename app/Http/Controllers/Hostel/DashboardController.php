<?php

namespace App\Http\Controllers\Hostel;

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
        
        // Hostel Stats
        $d['total_dorms'] = DB::table('dorms')->count();
        $d['total_rooms'] = DB::table('dorm_rooms')->count();
        $d['total_beds'] = DB::table('dorm_beds')->count();
        $d['occupied_beds'] = DB::table('dorm_beds')->where('is_occupied', true)->count();
        $d['free_beds'] = $d['total_beds'] - $d['occupied_beds'];

        // Recent Allocations
        $d['recent_allocations'] = DB::table('dorm_allocations')
            ->join('users', 'dorm_allocations.student_id', '=', 'users.id')
            ->join('dorm_beds', 'dorm_allocations.bed_id', '=', 'dorm_beds.id')
            ->join('dorm_rooms', 'dorm_beds.room_id', '=', 'dorm_rooms.id')
            ->join('dorms', 'dorm_rooms.dorm_id', '=', 'dorms.id')
            ->select('users.name as student_name', 'dorms.name as dorm_name', 'dorm_rooms.room_no', 'dorm_allocations.created_at')
            ->orderBy('dorm_allocations.created_at', 'desc')
            ->limit(5)
            ->get();

        return view('pages.hostel.dashboard', $d);
    }
}
