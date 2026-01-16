<?php

namespace App\Http\Controllers\SupportTeam;

use App\Helpers\Qs;
use App\Http\Controllers\Controller;
use App\Http\Requests\Attendance\AttendanceMarkRequest;
use App\Http\Requests\Attendance\AttendanceSubmitRequest;
use App\Models\AttendanceEvent;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Repositories\StudentRepo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AttendanceMarkController extends Controller
{
    protected $students;

    public function __construct(StudentRepo $students)
    {
        $this->middleware('auth');
        $this->middleware('teamSAT');
        $this->students = $students;
    }

    /**
     * Show the marking UI for a given session.
     */
    public function mark($sessionId)
    {
        $session = AttendanceSession::with(['my_class', 'section', 'subject', 'records.student'])->findOrFail($sessionId);
        $user    = Auth::user();

        if (Qs::userIsTeacher() && $session->taken_by !== $user->id && ! Qs::userIsTeamSA()) {
            abort(403, 'You are not allowed to mark this attendance session.');
        }

        if ($session->status !== 'open') {
            return redirect()->route('attendance.sessions.index')
                ->with('flash_warning', 'This attendance session is not open for editing.');
        }

        // Ensure we have records for all active students in the class/section (safety net)
        $activeStudentIds = $this->students
            ->getRecord(['my_class_id' => $session->my_class_id, 'section_id' => $session->section_id])
            ->pluck('user_id')
            ->all();

        $existingRecords = $session->records->keyBy('student_id');

        $now  = now();
        $rows = [];
        foreach ($activeStudentIds as $sid) {
            if (! $existingRecords->has($sid)) {
                $rows[] = [
                    'attendance_session_id' => $session->id,
                    'student_id'           => $sid,
                    'status'               => 'unmarked',
                    'remarks'              => null,
                    'marked_by'            => $session->taken_by,
                    'created_at'           => $now,
                    'updated_at'           => $now,
                ];
            }
        }
        if (! empty($rows)) {
            AttendanceRecord::insert($rows);
            $session->load('records.student');
        }

        return view('pages.attendance.sessions.mark', [
            'session'  => $session,
            'records'  => $session->records,
        ]);
    }

    /**
     * Mark attendance for students in a session.
     */
    public function storeRecords(AttendanceMarkRequest $request, $sessionId)
    {
        $session = AttendanceSession::with('records')->findOrFail($sessionId);
        $user = Auth::user();

        if ($session->status !== 'open') {
            return response()->json(['message' => 'Attendance session is not open for editing.'], 422);
        }

        if (Qs::userIsTeacher() && $session->taken_by !== $user->id && !Qs::userIsTeamSA()) {
            abort(403, 'You are not allowed to modify this attendance session.');
        }

        $data = $request->validated();

        // Ensure that only active students of the class/section are marked
        $validStudentIds = $this->students
            ->getRecord(['my_class_id' => $session->my_class_id, 'section_id' => $session->section_id])
            ->pluck('user_id')
            ->all();

        return DB::transaction(function () use ($data, $session, $user, $validStudentIds, $request) {
            $count = 0;
            foreach ($data['records'] as $recordData) {
                if (! in_array($recordData['student_id'], $validStudentIds, true)) {
                    continue; // Skip students not in this class/section
                }

                AttendanceRecord::updateOrCreate(
                    [
                        'attendance_session_id' => $session->id,
                        'student_id'           => $recordData['student_id'],
                    ],
                    [
                        'status'         => $recordData['status'],
                        'absence_reason' => $recordData['absence_reason'] ?? null,
                        'remarks'        => $recordData['remarks'] ?? null,
                        'marked_by'      => $user->id,
                    ]
                );
                $count++;
            }

            $session->updated_by = $user->id;
            $session->save();

            AttendanceEvent::create([
                'attendance_session_id' => $session->id,
                'attendance_record_id' => null,
                'action'              => 'records_marked',
                'performed_by'        => $user->id,
                'role'                => $user->user_type,
                'reason'              => null,
                'meta'                => [
                    'records_count' => $count,
                ],
            ]);

            if ($request->wantsJson()) {
                return response()->json(['message' => 'Attendance records saved.']);
            }

            return redirect()->back()->with('flash_success', 'Attendance records saved.');
        });
    }

    /**
     * Submit an attendance session (locks it from further teacher edits).
     */
    public function submit(AttendanceSubmitRequest $request, $sessionId)
    {
        $session = AttendanceSession::with('records')->findOrFail($sessionId);
        $user = Auth::user();

        if ($session->status !== 'open') {
            return response()->json(['message' => 'Attendance session is not open for submission.'], 422);
        }

        if (Qs::userIsTeacher() && $session->taken_by !== $user->id && !Qs::userIsTeamSA()) {
            abort(403, 'You are not allowed to submit this attendance session.');
        }

        // If no records yet, redirect back with a friendly message for web users
        if ($session->records()->count() === 0) {
            $message = 'No attendance records to submit for this session. Please mark attendance first.';

            if ($request->wantsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return redirect()->back()->with('flash_warning', $message);
        }

        // Do not allow submission while any student is still unmarked
        if ($session->records()->where('status', 'unmarked')->exists()) {
            $message = 'Some students are still unmarked. Please mark all students before submitting attendance.';

            if ($request->wantsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return redirect()->back()->with('flash_warning', $message);
        }

        return DB::transaction(function () use ($session, $user, $request) {
            $session->status       = 'submitted';
            $session->submitted_by = $user->id;
            $session->submitted_at = now();
            $session->updated_by   = $user->id;
            $session->save();

            AttendanceEvent::create([
                'attendance_session_id' => $session->id,
                'attendance_record_id' => null,
                'action'              => 'session_submitted',
                'performed_by'        => $user->id,
                'role'                => $user->user_type,
                'reason'              => null,
                'meta'                => null,
            ]);

            if ($request->wantsJson()) {
                return response()->json(['message' => 'Attendance submitted successfully.']);
            }

            return redirect()->back()->with('flash_success', 'Attendance submitted successfully.');
        });
    }
}
