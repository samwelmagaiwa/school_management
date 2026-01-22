<?php

namespace App\Http\Controllers\MyParent;

use App\Http\Controllers\Controller;
use App\Repositories\StudentRepo;
use App\Helpers\Qs;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    protected $student;

    public function __construct(StudentRepo $student)
    {
        $this->middleware('auth');
        $this->student = $student;
    }

    public function index()
    {
        $d = [];
        $parent_id = Auth::id();
        
        // Get My Children
        $d['children'] = Qs::findMyChildren($parent_id);
        
        $child_ids = $d['children']->pluck('user_id');
        $currentYear = Qs::getSetting('current_session');
        
        if ($d['children']->count() == 0) {
            $d['attendance_percentage'] = 0;
            $d['attendance_stats'] = (object) ['total_days' => 0, 'present' => 0, 'absent' => 0];
            $d['fee_summary'] = (object) ['total_paid' => 0, 'total_outstanding' => 0];
            return view('pages.parent.dashboard', $d);
        }
        
        // Combined Attendance Summary
        $d['attendance_stats'] = DB::table('attendance_records')
            ->select(
                DB::raw('COUNT(*) as total_days'),
                DB::raw('SUM(CASE WHEN attendance_type = "present" THEN 1 ELSE 0 END) as present'),
                DB::raw('SUM(CASE WHEN attendance_type = "absent" THEN 1 ELSE 0 END) as absent')
            )
            ->whereIn('student_id', $child_ids)
            ->whereYear('created_at', date('Y'))
            ->first();
        
        $total = $d['attendance_stats']->total_days ?? 0;
        $present = $d['attendance_stats']->present ?? 0;
        $d['attendance_percentage'] = $total > 0 ? round(($present / $total) * 100, 1) : 0;
        
        // Combined Fee Status
        $d['fee_summary'] = DB::table('payment_records')
            ->select(
                DB::raw('SUM(amt_paid) as total_paid'),
                DB::raw('SUM(balance) as total_outstanding')
            )
            ->whereIn('student_id', $child_ids)
            ->where('year', $currentYear)
            ->first();
        
        // Academic Performance for each child
        foreach ($d['children'] as $child) {
            $child->latest_result = DB::table('exam_records')
                ->join('exams', 'exam_records.exam_id', '=', 'exams.id')
                ->select('exams.name as exam_name', 'exam_records.ave', 'exam_records.pos')
                ->where('exam_records.student_id', $child->user_id)
                ->where('exam_records.year', $currentYear)
                ->orderBy('exams.id', 'desc')
                ->first();
        }
        
        return view('pages.parent.dashboard', $d);
    }
}
