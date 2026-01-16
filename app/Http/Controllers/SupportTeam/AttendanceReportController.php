<?php

namespace App\Http\Controllers\SupportTeam;

use App\Helpers\Qs;
use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\MyClass;
use App\Models\Section;
use App\Models\StudentRecord;
use App\Models\Subject;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('teamSAT');
    }

    /**
     * Reports landing page: choose which attendance report to view.
     */
    public function reportsIndex()
    {
        return view('pages.attendance.reports.index');
    }

    /**
     * Student attendance percentage within an optional date range.
     * JSON API endpoint (preserved for programmatic use).
     */
    public function studentReport($studentId, Request $request)
    {
        $this->authorizeStudentReport((int) $studentId);

        $summary = $this->buildStudentSummary((int) $studentId, $request);

        return response()->json($summary);
    }

    /**
     * HTML page for student attendance report (for teachers/admins).
     */
    public function studentReportPage(Request $request)
    {
        $studentId = $request->input('student_id');
        $classId   = $request->input('class_id');
        $sectionId = $request->input('section_id');

        $summary = null;
        $student = null;

        $classes  = MyClass::orderBy('name')->get();
        $sections = collect();
        $students = collect();

        if ($classId) {
            $sections = Section::where('my_class_id', $classId)->orderBy('name')->get();
        }

        if ($classId && $sectionId) {
            $students = StudentRecord::with('user')
                ->where('my_class_id', $classId)
                ->where('section_id', $sectionId)
                ->get();
        }

        $records = collect();

        if ($studentId) {
            $this->authorizeStudentReport((int) $studentId);
            $summary = $this->buildStudentSummary((int) $studentId, $request);

            // Detailed per-session records for datatable
            $records = AttendanceRecord::with(['session.my_class', 'session.section', 'session.subject'])
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
                })
                ->orderByDesc('id')
                ->get();

            $student = User::find($studentId);
        }

        $subjects = Subject::orderBy('name')->get();

        return view('pages.attendance.reports.student', [
            'summary'   => $summary,
            'student'   => $student,
            'studentId' => $studentId,
            'records'   => $records,
            'classes'   => $classes,
            'sections'  => $sections,
            'students'  => $students,
            'subjects'  => $subjects,
            'filters'   => $request->only(['class_id', 'section_id', 'from', 'to', 'subject_id', 'type']),
        ]);
    }

    /**
     * Class attendance summary by date range.
     * JSON API endpoint (preserved for programmatic use).
     */
    public function classReport($classId, $sectionId, Request $request)
    {
        [$summary, $perStudent] = $this->buildClassSummary((int) $classId, (int) $sectionId, $request);

        return response()->json([
            'class_id'    => (int) $classId,
            'section_id'  => (int) $sectionId,
            'summary'     => $summary,
            'per_student' => array_values($perStudent),
        ]);
    }

    /**
     * HTML page for class attendance summary.
     */
    public function classReportPage(Request $request)
    {
        $classId   = $request->input('class_id');
        $sectionId = $request->input('section_id');

        $classes  = MyClass::orderBy('name')->get();
        $sections = collect();
        $subjects = Subject::orderBy('name')->get();

        $summary    = null;
        $perStudent = [];
        $class      = null;
        $section    = null;

        if ($classId) {
            $sections = Section::where('my_class_id', $classId)->orderBy('name')->get();
        }

        if ($classId && $sectionId) {
            [$summary, $perStudent] = $this->buildClassSummary((int) $classId, (int) $sectionId, $request);
            $class   = MyClass::find($classId);
            $section = Section::find($sectionId);
        }

        return view('pages.attendance.reports.class', [
            'classes'    => $classes,
            'sections'   => $sections,
            'subjects'   => $subjects,
            'summary'    => $summary,
            'perStudent' => $perStudent,
            'class'      => $class,
            'section'    => $section,
            'filters'    => $request->only(['class_id', 'section_id', 'from', 'to', 'subject_id', 'type']),
        ]);
    }

    /**
     * Teacher compliance report: number of sessions taken per teacher in a range.
     * Returns JSON for API clients or Blade view for browser users.
     */
    public function teacherCompliance(Request $request)
    {
        // Admins see all; HODs are allowed but only within their departments
        if (! Qs::userIsTeamSA() && ! Qs::userIsHOD()) {
            abort(403, 'Only administrators or heads of department can view teacher compliance reports.');
        }

        $data = $this->buildTeacherComplianceData($request);

        if ($request->wantsJson()) {
            return response()->json($data);
        }

        return view('pages.attendance.reports.teacher_compliance', [
            'rows'    => $data,
            'filters' => $request->only(['from', 'to', 'subject_id', 'type']),
            'subjects'=> Subject::orderBy('name')->get(),
        ]);
    }

    /**
     * Shared: enforce access rules for viewing a student's attendance.
     */
    protected function authorizeStudentReport(int $studentId): void
    {
        $user = Auth::user();
        if ($user->id !== $studentId && ! Qs::userIsTeamSAT() && ! Qs::userIsMyChild($studentId, $user->id)) {
            abort(403, 'You are not allowed to view this student attendance report.');
        }
    }

    /**
     * Shared: build student summary stats.
     */
    protected function buildStudentSummary(int $studentId, Request $request): array
    {
        $query = AttendanceRecord::where('student_id', $studentId)
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

        $total   = (clone $query)->count();
        $present = (clone $query)->where('status', 'present')->count();
        $absent  = (clone $query)->where('status', 'absent')->count();
        $late    = (clone $query)->where('status', 'late')->count();
        $excused = (clone $query)->where('status', 'excused')->count();

        $percentage = $total > 0 ? round(($present / $total) * 100, 2) : null;

        return [
            'student_id' => $studentId,
            'total'      => $total,
            'present'    => $present,
            'absent'     => $absent,
            'late'       => $late,
            'excused'    => $excused,
            'percentage' => $percentage,
        ];
    }

    /**
     * Shared: build class-level summary and per-student breakdown.
     */
    protected function buildClassSummary(int $classId, int $sectionId, Request $request): array
    {
        $sessionsQuery = AttendanceSession::where('my_class_id', $classId)
            ->where('section_id', $sectionId);

        // HODs can only see classes belonging to their departments
        if (Qs::userIsHOD() && ! Qs::userIsTeamSA()) {
            $deptIds = Qs::hodDepartmentIds();
            $sessionsQuery->whereHas('my_class', function ($q) use ($deptIds) {
                $q->whereIn('department_id', $deptIds);
            });
        }

        if ($request->filled('from')) {
            $sessionsQuery->whereDate('date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $sessionsQuery->whereDate('date', '<=', $request->to);
        }
        if ($request->filled('subject_id')) {
            $sessionsQuery->where('subject_id', $request->subject_id);
        }
        if ($request->filled('type')) {
            $sessionsQuery->where('type', $request->type);
        }

        $sessionIds = $sessionsQuery->pluck('id');

        $records = AttendanceRecord::with('student')
            ->whereIn('attendance_session_id', $sessionIds)
            ->get();

        $summary = [
            'present' => 0,
            'absent'  => 0,
            'late'    => 0,
            'excused' => 0,
            'total'   => 0,
        ];

        $perStudent = [];

        foreach ($records as $record) {
            $summary['total']++;
            if (isset($summary[$record->status])) {
                $summary[$record->status]++;
            }

            $sid = $record->student_id;
            if (! isset($perStudent[$sid])) {
                $perStudent[$sid] = [
                    'student_id' => $sid,
                    'student'    => $record->student,
                    'present'    => 0,
                    'absent'     => 0,
                    'late'       => 0,
                    'excused'    => 0,
                    'total'      => 0,
                    'percentage' => null,
                ];
            }

            $perStudent[$sid]['total']++;
            if (isset($perStudent[$sid][$record->status])) {
                $perStudent[$sid][$record->status]++;
            }
        }

        foreach ($perStudent as &$row) {
            $row['percentage'] = $row['total'] > 0
                ? round(($row['present'] / $row['total']) * 100, 2)
                : null;
        }
        unset($row);

        return [$summary, $perStudent];
    }

    /**
     * Shared: build teacher compliance dataset.
     */
    protected function buildTeacherComplianceData(Request $request)
    {
        $query = AttendanceSession::selectRaw('taken_by, COUNT(*) as sessions_taken')
            ->groupBy('taken_by');

        if (Qs::userIsHOD() && ! Qs::userIsTeamSA()) {
            $deptIds = Qs::hodDepartmentIds();
            // For subject-based sessions, use subject.department_id;
            // for daily sessions, fall back to class.department_id.
            $query->where(function ($q) use ($deptIds) {
                $q->whereHas('subject', function ($sub) use ($deptIds) {
                        $sub->whereIn('department_id', $deptIds);
                    })
                  ->orWhere(function ($qq) use ($deptIds) {
                        $qq->whereNull('subject_id')
                           ->whereHas('my_class', function ($c) use ($deptIds) {
                               $c->whereIn('department_id', $deptIds);
                           });
                    });
            });
        }

        if ($request->filled('from')) {
            $query->whereDate('date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('date', '<=', $request->to);
        }
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        return $query->with(['teacher'])->get()->map(function ($row) {
            return [
                'teacher_id'     => $row->taken_by,
                'teacher_name'   => optional($row->teacher)->name,
                'sessions_taken' => (int) $row->sessions_taken,
            ];
        })->all();
    }
}
