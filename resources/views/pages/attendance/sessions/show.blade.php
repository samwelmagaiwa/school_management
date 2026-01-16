@extends('layouts.master')

@section('page_title', 'Attendance Session Details')

@section('content')

    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="card-title mb-0">Attendance Session Details</h6>
            <a href="{{ route('attendance.sessions.index') }}" class="btn btn-sm btn-secondary">Back to Sessions</a>
        </div>
        <div class="card-body">
            <div class="row mb-2">
                <div class="col-md-3">
                    <strong>Date:</strong>
                    {{ \Illuminate\Support\Carbon::parse($session->date)->format('Y-m-d') }}
                </div>
                <div class="col-md-3">
                    <strong>Class:</strong>
                    {{ optional($session->my_class)->name ?? '-' }}
                </div>
                <div class="col-md-3">
                    <strong>Section:</strong>
                    {{ optional($session->section)->name ?? '-' }}
                </div>
                <div class="col-md-3">
                    <strong>Subject:</strong>
                    {{ optional($session->subject)->name ?? 'Daily Attendance' }}
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-md-3">
                    <strong>Type:</strong>
                    {{ ucfirst($session->type) }}
                </div>
                <div class="col-md-3">
                    <strong>Status:</strong>
                    @if($session->status === 'open')
                        <span class="badge badge-warning">Open</span>
                    @elseif($session->status === 'submitted')
                        <span class="badge badge-info">Submitted</span>
                    @elseif($session->status === 'locked')
                        <span class="badge badge-danger">Locked</span>
                    @else
                        <span class="badge badge-secondary">{{ ucfirst($session->status) }}</span>
                    @endif
                </div>
                <div class="col-md-3">
                    <strong>Taken By:</strong>
                    {{ optional($session->teacher)->name ?? '-' }}
                </div>
                <div class="col-md-3">
                    <strong>Submitted At:</strong>
                    {{ $session->submitted_at ? $session->submitted_at->format('Y-m-d H:i') : '—' }}
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">
            <h6 class="card-title mb-0">Student Attendance Records</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Admission No</th>
                        <th>Student Name</th>
                        <th>Status</th>
                        <th>Reason</th>
                        <th>Remarks</th>
                        <th>Override?</th>
                    </tr>
                    </thead>
                    <tbody>
                    @php $i = 1; @endphp
                    @foreach($session->records->sortBy(function($r){ return optional($r->student)->name; }) as $record)
                        @php
                            $student   = $record->student;
                            $override  = $record->overrides->sortByDesc('created_at')->first();
                        @endphp
                        @if(!$student)
                            @continue
                        @endif
                        <tr @if($record->status === 'absent') class="table-danger" @elseif($record->status === 'late') class="table-warning" @endif>
                            <td>{{ $i++ }}</td>
                            <td>{{ $student->adm_no ?? '-' }}</td>
                            <td>{{ $student->name }}</td>
                            <td>{{ ucfirst($record->status) }}</td>
                            <td>
                                @if($record->absence_reason)
                                    @php
                                        $reasons = [
                                            'sick' => 'Sick',
                                            'family_emergency' => 'Family Emergency',
                                            'school_activity' => 'School Activity',
                                            'unexcused' => 'Unexcused',
                                            'other' => 'Other',
                                        ];
                                    @endphp
                                    {{ $reasons[$record->absence_reason] ?? $record->absence_reason }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>{{ $record->remarks }}</td>
                            <td>
                                @if($override)
                                    <span class="badge badge-info">Overridden</span>
                                    <br>
                                    <small>
                                        From <strong>{{ ucfirst($override->previous_status) }}</strong>
                                        to <strong>{{ ucfirst($override->new_status) }}</strong>
                                        by {{ optional($override->performedBy)->name }}
                                    </small>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h6 class="card-title mb-0">Audit Log</h6>
        </div>
        <div class="card-body">
            @if($session->events->isEmpty())
                <p class="text-muted mb-0">No events recorded for this session.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-sm table-striped">
                        <thead>
                        <tr>
                            <th>Time</th>
                            <th>Action</th>
                            <th>Performed By</th>
                            <th>Role</th>
                            <th>Reason / Meta</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($session->events->sortBy('created_at') as $event)
                            <tr>
                                <td>{{ $event->created_at ? $event->created_at->format('Y-m-d H:i') : '' }}</td>
                                <td>{{ $event->action }}</td>
                                <td>{{ optional($event->performer)->name ?? 'System' }}</td>
                                <td>{{ $event->role }}</td>
                                <td>
                                    @if($event->reason)
                                        <div><strong>Reason:</strong> {{ $event->reason }}</div>
                                    @endif
                                    @if($event->meta)
                                        <small class="text-muted">{{ json_encode($event->meta) }}</small>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

@endsection
