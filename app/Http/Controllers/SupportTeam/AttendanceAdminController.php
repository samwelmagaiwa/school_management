<?php

namespace App\Http\Controllers\SupportTeam;

use App\Helpers\Qs;
use App\Http\Controllers\Controller;
use App\Http\Requests\Attendance\AttendanceOverrideRequest;
use App\Models\AttendanceEvent;
use App\Models\AttendanceOverride;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AttendanceAdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('teamSA'); // Admin/super_admin only
    }

    /**
     * Unlock a submitted/locked session back to open state.
     */
    public function unlock($sessionId)
    {
        $session = AttendanceSession::findOrFail($sessionId);

        if ($session->status === 'open') {
            return response()->json(['message' => 'Session is already open.'], 422);
        }

        $userId = Auth::id();

        $session->status     = 'open';
        $session->locked_by  = null;
        $session->locked_at  = null;
        $session->updated_by = $userId;
        $session->save();

        AttendanceEvent::create([
            'attendance_session_id' => $session->id,
            'attendance_record_id' => null,
            'action'              => 'session_unlocked',
            'performed_by'        => $userId,
            'role'                => Auth::user()->user_type,
            'reason'              => null,
            'meta'                => null,
        ]);

        return response()->json(['message' => 'Attendance session has been unlocked.']);
    }

    /**
     * Override a single attendance record with an admin-supplied reason.
     */
    public function overrideRecord(AttendanceOverrideRequest $request, $recordId)
    {
        $record = AttendanceRecord::with('session')->findOrFail($recordId);

        return DB::transaction(function () use ($request, $record) {
            $userId = Auth::id();

            AttendanceOverride::create([
                'attendance_record_id'    => $record->id,
                'previous_status'         => $record->status,
                'previous_absence_reason' => $record->absence_reason,
                'new_status'              => $request->status,
                'new_absence_reason'      => $request->absence_reason,
                'previous_remarks'        => $record->remarks,
                'new_remarks'             => $request->remarks,
                'reason'                  => $request->reason,
                'performed_by'            => $userId,
            ]);

            AttendanceEvent::create([
                'attendance_session_id' => $record->attendance_session_id,
                'attendance_record_id'  => $record->id,
                'action'               => 'record_overridden',
                'performed_by'         => $userId,
                'role'                 => Auth::user()->user_type,
                'reason'               => $request->reason,
                'meta'                 => [
                    'previous_status' => $record->status,
                    'new_status'      => $request->status,
                ],
            ]);

            $record->status         = $request->status;
            $record->absence_reason = $request->absence_reason;
            $record->remarks        = $request->remarks;
            $record->marked_by      = $userId; // last modifier
            $record->save();

            $session = $record->session;
            $session->status     = 'locked';
            $session->locked_by  = $userId;
            $session->locked_at  = now();
            $session->updated_by = $userId;
            $session->save();

            return response()->json(['message' => 'Attendance record overridden successfully.']);
        });
    }
}
