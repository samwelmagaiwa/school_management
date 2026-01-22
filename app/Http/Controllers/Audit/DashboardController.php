<?php

namespace App\Http\Controllers\Audit;

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
        
        // Audit Stats
        $d['total_logs'] = DB::table('activity_logs')->count();
        $d['recent_logs_count'] = DB::table('activity_logs')->where('created_at', '>=', now()->subDays(7))->count();
        $d['total_payments'] = DB::table('payment_records')->sum('amt_paid');
        $d['total_expenses'] = DB::table('accounting_expenses')->sum('amount');

        // Recent Activity Logs
        $d['recent_logs'] = DB::table('activity_logs')
            ->join('users', 'activity_logs.user_id', '=', 'users.id')
            ->select('users.name as user_name', 'activity_logs.action', 'activity_logs.description', 'activity_logs.created_at')
            ->orderBy('activity_logs.created_at', 'desc')
            ->limit(10)
            ->get();

        return view('pages.audit.dashboard', $d);
    }
}
