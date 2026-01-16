<?php

namespace App\Http\Controllers\SupportTeam;

use App\Helpers\Qs;
use App\Http\Controllers\Controller;
use App\Http\Requests\Attendance\AttendanceSessionStoreRequest;
use App\Models\AttendanceSession;
use App\Models\AttendanceEvent;
use App\Models\AttendanceRecord;
use App\Models\MyClass;
use App\Models\Section;
use App\Models\Subject;
use App\Repositories\StudentRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AttendanceSessionController extends Controller
{
    protected $students;

    public function __construct(StudentRepo $students)
    {
        $this->middleware('auth');
        // Teachers and academic/admin team can access attendance endpoints
        $this->middleware('teamSAT');
        $this->students = $students;
    }

    /**
     * List attendance sessions for the current user.
     * Teachers see only their sessions; admins see all.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = AttendanceSession::with(['my_class', 'section', 'subject', 'teacher'])
            ->orderByDesc('date');

        if (Qs::userIsTeacher()) {
            $query->where('taken_by', $user->id);
        }

        if ($request->filled('class_id')) {
            $query->where('my_class_id', $request->class_id);
        }
        if ($request->filled('section_id')) {
            $query->where('section_id', $request->section_id);
        }
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('from')) {
            $query->whereDate('date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('date', '<=', $request->to);
        }

        $sessions = $query->paginate(50);

        // JSON for API clients
        if ($request->wantsJson()) {
            return response()->json($sessions);
        }

        // Blade view for browser users (teachers/admins)
        $classes  = MyClass::orderBy('name')->get();
        $subjects = Subject::orderBy('name')->get();

        return view('pages.attendance.sessions.index', [
            'sessions' => $sessions,
            'classes'  => $classes,
            'subjects' => $subjects,
            'filters'  => $request->only(['class_id', 'section_id', 'subject_id', 'type', 'from', 'to']),
        ]);
    }

    /**
     * Create a new attendance session (daily or subject-based).
     * Only the assigned class/subject teacher or an admin can create it.
     */
    public function store(AttendanceSessionStoreRequest $request)
    {
        $user = Auth::user();
        $data = $request->validated();

        $this->assertTeacherAuthorizedForSession(
            $user->id,
            $data['my_class_id'],
            $data['section_id'] ?? null,
            $data['subject_id'] ?? null
        );

        // Enforce one session per class/period/date
        $existing = AttendanceSession::whereDate('date', $data['date'])
            ->where('my_class_id', $data['my_class_id'])
            ->where('section_id', $data['section_id'] ?? null)
            ->where('subject_id', $data['subject_id'] ?? null)
            ->where('time_slot_id', $data['time_slot_id'] ?? null)
            ->first();

        if ($existing) {
            if ($request->wantsJson()) {
                return response()->json([
                    'message'    => 'An attendance session for this class/section/subject and date already exists.',
                    'session_id' => $existing->id,
                ], 422);
            }

            return redirect()->route('attendance.sessions.index')
                ->with('flash_warning', 'An attendance session for this class/section/subject and date already exists.');
        }

        $response = DB::transaction(function () use ($data, $user) {
            $session = AttendanceSession::create([
                'date'         => $data['date'],
                'my_class_id'  => $data['my_class_id'],
                'section_id'   => $data['section_id'] ?? null,
                'subject_id'   => $data['subject_id'] ?? null,
                'time_slot_id' => $data['time_slot_id'] ?? null,
                'type'         => $data['type'],
                'taken_by'     => $user->id,
                'status'       => 'open',
                'created_by'   => $user->id,
                'updated_by'   => $user->id,
            ]);

            // Snapshot enrolled students at creation time as unmarked records
            $studentRecords = $this->students
                ->getRecord(['my_class_id' => $session->my_class_id, 'section_id' => $session->section_id])
                ->get();

            $now = now();
            $rows = [];
            foreach ($studentRecords as $sr) {
                $rows[] = [
                    'attendance_session_id' => $session->id,
                    'student_id'           => $sr->user_id,
                    'status'               => 'unmarked',
                    'remarks'              => null,
                    'marked_by'            => $session->taken_by,
                    'created_at'           => $now,
                    'updated_at'           => $now,
                ];
            }

            if (! empty($rows)) {
                AttendanceRecord::insert($rows);
            }

            AttendanceEvent::create([
                'attendance_session_id' => $session->id,
                'attendance_record_id'  => null,
                'action'               => 'session_created',
                'performed_by'         => $user->id,
                'role'                 => $user->user_type,
                'reason'               => null,
                'meta'                 => [
                    'type'       => $session->type,
                    'class_id'   => $session->my_class_id,
                    'section_id' => $session->section_id,
                    'subject_id' => $session->subject_id,
                    'date'       => $session->date,
                    'students'   => $studentRecords->pluck('user_id')->values(),
                ],
            ]);

            return $session;
        });

        if ($request->wantsJson()) {
            return response()->json($response, 201);
        }

        return redirect()->route('attendance.sessions.index')
            ->with('flash_success', 'Attendance session created successfully.');
    }

    /**
     * Show a single attendance session with its records.
     */
    public function show(Request $request, $id)
    {
        $session = AttendanceSession::with([
                'my_class',
                'section',
                'subject',
                'teacher',
                'records.student',
                'records.overrides.performedBy',
                'events.performer',
            ])->findOrFail($id);

        $user = Auth::user();
        if (Qs::userIsTeacher() && $session->taken_by !== $user->id) {
            abort(403, 'You are not allowed to view this attendance session.');
        }

        if ($request->wantsJson()) {
            return response()->json($session);
        }

        return view('pages.attendance.sessions.show', [
            'session' => $session,
        ]);
    }

    /**
     * Ensure only assigned teachers (or admins) can take attendance.
     */
    protected function assertTeacherAuthorizedForSession(int $userId, int $classId, ?int $sectionId, ?int $subjectId): void
    {
        if (Qs::userIsTeamSA()) {
            return; // Admin/super_admin always allowed
        }

        if (! Qs::userIsTeacher()) {
            abort(403, 'Only teachers or admins can take attendance.');
        }

        if ($subjectId) {
            $subject = Subject::where('id', $subjectId)
                ->where('my_class_id', $classId)
                ->where('teacher_id', $userId)
                ->first();

            if (! $subject) {
                abort(403, 'You are not assigned to this subject for the selected class.');
            }
        } else {
            // Daily/class attendance uses section teacher assignment
            if (! $sectionId) {
                abort(422, 'Section is required for daily attendance.');
            }

            $section = Section::where('id', $sectionId)
                ->where('my_class_id', $classId)
                ->where('teacher_id', $userId)
                ->first();

            if (! $section) {
                abort(403, 'You are not the class teacher for this section.');
            }
        }
    }
}
