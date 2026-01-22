<?php

namespace App\Http\Controllers\HumanResource;

use App\Http\Controllers\Controller;
use App\Models\HumanResource\LeaveRequest;
use App\Models\HumanResource\LeaveType;
use App\Helpers\Qs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LeaveRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:leave.manage')->only(['update']);
    }

    public function index()
    {
        $user = Auth::user();
        
        // If has permission to manage leaves, show all. Else show own.
        if($user->can('leave.manage')) {
            $leaves = LeaveRequest::with(['staff', 'type'])->orderBy('created_at', 'desc')->get();
        } else {
            $leaves = LeaveRequest::where('staff_id', $user->id)->with('type')->orderBy('created_at', 'desc')->get();
        }

        return view('pages.human_resource.leaves.index', compact('leaves'));
    }

    public function create()
    {
        $types = LeaveType::all();
        return view('pages.human_resource.leaves.create', compact('types'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $start = Carbon::parse($request->start_date);
        $end = Carbon::parse($request->end_date);
        $days = $start->diffInDays($end) + 1; // Inclusive

        $leave = new LeaveRequest();
        $leave->staff_id = Auth::id();
        $leave->leave_type_id = $request->leave_type_id;
        $leave->start_date = $request->start_date;
        $leave->end_date = $request->end_date;
        $leave->days_requested = $days;
        $leave->reason = $request->reason;
        $leave->status = 'Pending';
        
        if($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $f = Qs::getFileMetaData($file);
            $f['name'] = 'leave_att_' . time() . '.' . $f['ext'];
            $f['path'] = $file->storeAs(Qs::getUploadPath('leave_attachments'), $f['name']);
            $leave->attachment = $f['path'];
        }

        $leave->save();

        return redirect()->route('hr.leaves.index')->with('flash_success', 'Leave Request Submitted Successfully');
    }

    public function update(Request $request, $id)
    {
        $leave = LeaveRequest::findOrFail($id);
        
        // Approve actions
        if($request->has('status')) {
            $status = $request->status; // Approved or Rejected
            $leave->status = $status;
            
            if($status == 'Approved') {
                $leave->approved_by = Auth::id();
                $leave->approved_at = now();
                // TODO: Logic to auto-fill attendance for these dates?
            } elseif ($status == 'Rejected') {
                $leave->rejected_by = Auth::id();
                $leave->rejected_at = now();
                // $leave->rejection_reason = $request->reason ?? '';
            }
            $leave->save();
        }

        return redirect()->back()->with('flash_success', 'Leave Status Updated');
    }
}
