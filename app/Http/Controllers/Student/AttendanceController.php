<?php

namespace App\Http\Controllers\Student;

use App\Helpers\Qs;
use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        if (! Qs::userIsStudent()) {
            abort(403, 'Only students can view this page.');
        }

        $studentId = Auth::id();

        $query = AttendanceRecord::with(['session.my_class', 'session.section', 'session.subject'])
            ->where('student_id', $studentId)
            ->whereHas('session', function ($q) use ($request) {
                if ($request->filled('from')) {
                    $q->whereDate('date', '>=', $request->from);
                }
                if ($request->filled('to')) {
                    $q->whereDate('date', '<=', $request->to);
                }
                if ($request->filled('subject_id')) {
                    $q->where('subject_id', $request->subject_id);
                }
                if ($request->filled('type')) {
                    $q->where('type', $request->type);
                }
            });

        $records = $query->orderByDesc('id')->get();

        // Build summary similar to AttendanceReportController::buildStudentSummary
        $total   = $records->count();
        $present = $records->where('status', 'present')->count();
        $absent  = $records->where('status', 'absent')->count();
        $late    = $records->where('status', 'late')->count();
        $excused = $records->where('status', 'excused')->count();

        $percentage = $total > 0 ? round(($present / $total) * 100, 2) : null;

        $summary = [
            'total'      => $total,
            'present'    => $present,
            'absent'     => $absent,
            'late'       => $late,
            'excused'    => $excused,
            'percentage' => $percentage,
        ];

        $subjects = Subject::orderBy('name')->get();

        return view('pages.student.my_attendance', [
            'summary' => $summary,
            'records' => $records,
            'subjects'=> $subjects,
            'filters' => $request->only(['from', 'to', 'subject_id', 'type']),
        ]);
    }
}
