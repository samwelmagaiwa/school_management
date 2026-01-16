<?php

namespace App\Http\Controllers\MyParent;

use App\Helpers\Qs;
use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\Subject;
use App\Repositories\StudentRepo;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MyController extends Controller
{
    protected $student;

    public function __construct(StudentRepo $student)
    {
        $this->student = $student;
    }

    public function children()
    {
        $data['students'] = $this->student->getRecord(['my_parent_id' => Auth::user()->id])->with(['my_class', 'section'])->get();

        return view('pages.parent.children', $data);
    }

    public function childAttendance(Request $request)
    {
        $childId = $request->input('child_id');

        // Get all children of the current parent
        $myChildren = $this->student->getRecord(['my_parent_id' => Auth::user()->id])
            ->with('user')
            ->get();

        if ($myChildren->isEmpty()) {
            abort(404, 'You have no children registered in the system.');
        }

        // If no child selected, default to the first child
        if (! $childId) {
            $childId = $myChildren->first()->user_id;
        }

        // Verify that the selected child belongs to this parent
        if (! Qs::userIsMyChild($childId, Auth::id())) {
            abort(403, 'You are not authorized to view this child\'s attendance.');
        }

        $child = User::find($childId);

        $query = AttendanceRecord::with(['session.my_class', 'session.section', 'session.subject'])
            ->where('student_id', $childId)
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

        return view('pages.parent.child_attendance', [
            'myChildren' => $myChildren,
            'child'      => $child,
            'childId'    => $childId,
            'summary'    => $summary,
            'records'    => $records,
            'subjects'   => $subjects,
            'filters'    => $request->only(['from', 'to', 'subject_id', 'type']),
        ]);
    }
}
