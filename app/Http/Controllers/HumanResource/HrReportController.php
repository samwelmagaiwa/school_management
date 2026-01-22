<?php

namespace App\Http\Controllers\HumanResource;

use App\Http\Controllers\Controller;
use App\Models\HumanResource\StaffAttendance;
use App\Models\HumanResource\LeaveRequest;
use Illuminate\Http\Request;
use Carbon\Carbon;

class HrReportController extends Controller
{
    public function summary()
    {
        // Quick Dashboard-like summary for HR
        $today = Carbon::today();
        
        $attendance_today = StaffAttendance::whereDate('date', $today)->get();
        $present_count = $attendance_today->where('status', 'present')->count();
        $absent_count = $attendance_today->where('status', 'absent')->count();
        $late_count = $attendance_today->where('is_late', true)->count();
        
        $pending_leaves = LeaveRequest::where('status', 'Pending')->count();
        $active_leaves = LeaveRequest::where('status', 'Approved')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->count();

        return view('pages.human_resource.reports.summary', compact('present_count', 'absent_count', 'late_count', 'pending_leaves', 'active_leaves'));
    }
}
