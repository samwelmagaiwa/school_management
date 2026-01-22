<?php

namespace App\Http\Controllers\HumanResource;

use App\Http\Controllers\Controller;
use App\Models\HumanResource\StaffAttendance;
use App\Models\HumanResource\Department;
use App\User;
use App\Helpers\Qs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class StaffAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->input('date', date('Y-m-d'));
        $department_id = $request->input('department_id', 'all');

        // Fetch all users who have a staff record (Teachers, Admins, etc.)
        $query = User::whereHas('staff_record');

        if ($department_id != 'all') {
            $query->whereHas('staff_record', function($q) use ($department_id) {
                $q->where('department_id', $department_id);
            });
        }

        $staffs = $query->with(['staff_record', 'staff_attendances' => function($q) use ($date) {
            $q->whereDate('date', $date);
        }])->get();

        $departments = Department::all();

        return view('pages.human_resource.attendance.index', compact('staffs', 'date', 'departments', 'department_id'));
    }

    public function store(Request $request)
    {
        $date = $request->date;
        $attendances = $request->attendance; // array [staff_id => status]
        $remarks = $request->remarks; // array [staff_id => remark]
        $clock_in = $request->clock_in_time;
        $clock_out = $request->clock_out_time;

        if (empty($attendances) || !is_array($attendances)) {
            return redirect()->back()->with('flash_error', 'No attendance data submitted.');
        }

        foreach ($attendances as $staff_id => $status) {
            $is_late = false;
            // Simple logic: if status is late
            if ($status === 'late') {
                $is_late = true;
            }
            // Auto-flag late if clock_in > 8:00 (assuming 8:00 AM start)
            if (isset($clock_in[$staff_id]) && $clock_in[$staff_id] > '08:00') {
                $is_late = true;
                if($status == 'present') $status = 'late'; // Auto-correct status if marked present but late
            }

            StaffAttendance::updateOrCreate(
                ['staff_id' => $staff_id, 'date' => $date],
                [
                    'status' => $status,
                    'remarks' => $remarks[$staff_id] ?? null,
                    'clock_in_time' => $clock_in[$staff_id] ?? null,
                    'clock_out_time' => $clock_out[$staff_id] ?? null,
                    'recorded_by' => Auth::id(),
                    'is_late' => $is_late,
                ]
            );
        }

        return redirect()->back()->with('flash_success', 'Attendance Recorded Successfully');
    }

    public function my_attendance()
    {
        $user = Auth::user();
        $attendances = StaffAttendance::where('staff_id', $user->id)
            ->orderBy('date', 'desc')
            ->limit(30) // Show last 30 entries
            ->get();

        return view('pages.human_resource.attendance.my_attendance', compact('attendances'));
    }
}
