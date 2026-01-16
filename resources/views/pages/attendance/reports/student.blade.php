@extends('layouts.master')

@section('page_title', 'Student Attendance Report')

@section('content')

    <div class="card mb-3">
        <div class="card-header header-elements-inline">
            <h5 class="card-title"><i class="icon-user-check mr-2"></i> Student Attendance Report</h5>
            <div class="header-elements">
                <a href="{{ route('attendance.reports.index') }}" class="btn btn-sm btn-secondary">Back to Reports</a>
            </div>
        </div>
        <div class="card-body">
            <form method="get" action="{{ route('attendance.reports.student_page') }}" id="studentReportForm">
                <div class="form-row">
                    <div class="col-md-3 mb-2">
                        <label class="font-weight-semibold">Class</label>
                        <select name="class_id" id="class_id" class="form-control select">
                            <option value="">Select Class</option>
                            @foreach($classes as $c)
                                <option value="{{ $c->id }}" {{ ($filters['class_id'] ?? '') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="font-weight-semibold">Section</label>
                        <select name="section_id" id="section_id" class="form-control select">
                            <option value="">Select Section</option>
                            @foreach($sections as $s)
                                <option value="{{ $s->id }}" {{ ($filters['section_id'] ?? '') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="font-weight-semibold">Student</label>
                        <select name="student_id" id="student_id" class="form-control select-search" required>
                            <option value="">Select Student</option>
                            @foreach($students as $sr)
                                @if($sr->user)
                                    <option value="{{ $sr->user_id }}" {{ $studentId == $sr->user_id ? 'selected' : '' }}>
                                        {{ $sr->user->name }} ({{ $sr->user->adm_no ?? 'N/A' }})
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-row mt-2">
                    <div class="col-md-2 mb-2">
                        <label class="font-weight-semibold">From</label>
                        <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="form-control">
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="font-weight-semibold">To</label>
                        <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="form-control">
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="font-weight-semibold">Subject (optional)</label>
                        <select name="subject_id" class="form-control select-search">
                            <option value="">All</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" {{ ($filters['subject_id'] ?? '') == $subject->id ? 'selected' : '' }}>
                                    {{ $subject->name }} (Class {{ optional($subject->my_class)->name }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="font-weight-semibold">Type</label>
                        <select name="type" class="form-control">
                            <option value="">All</option>
                            <option value="daily" {{ ($filters['type'] ?? '') === 'daily' ? 'selected' : '' }}>Daily</option>
                            <option value="subject" {{ ($filters['type'] ?? '') === 'subject' ? 'selected' : '' }}>Subject</option>
                            <option value="event" {{ ($filters['type'] ?? '') === 'event' ? 'selected' : '' }}>Event</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-2">Generate Report</button>
            </form>
        </div>
    </div>

    @if($summary)
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="card-title mb-0">Summary @if($student) for {{ $student->name }} @endif</h6>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Total Sessions:</strong> {{ $summary['total'] }}
                    </div>
                    <div class="col-md-4">
                        <strong>Present:</strong> {{ $summary['present'] }}
                    </div>
                    <div class="col-md-4">
                        <strong>Absent:</strong> {{ $summary['absent'] }}
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Late:</strong> {{ $summary['late'] }}
                    </div>
                    <div class="col-md-4">
                        <strong>Excused:</strong> {{ $summary['excused'] }}
                    </div>
                    <div class="col-md-4">
                        <strong>Percentage Present:</strong>
                        {{ $summary['percentage'] !== null ? $summary['percentage'].'%' : 'N/A' }}
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">Per-Session Attendance Detail</h6>
            </div>
            <div class="card-body">
                <p class="mb-2 text-muted">
                    @if($student)
                        Showing sessions for <strong>{{ $student->name }}</strong>
                    @else
                        Showing sessions for selected student
                    @endif
                    @if(($filters['from'] ?? null) || ($filters['to'] ?? null))
                        &mdash; Date Range:
                        <strong>{{ $filters['from'] ?? 'Start' }}</strong>
                        to
                        <strong>{{ $filters['to'] ?? 'End' }}</strong>
                    @else
                        &mdash; <span class="text-muted">All dates</span>
                    @endif
                </p>
                @if($records->isEmpty())
                    <p class="text-muted mb-0">No attendance records found for the selected criteria.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped datatable-button-html5-columns">
                            <thead>
                            <tr>
                                <th style="width: 3rem;" class="text-center">#</th>
                                <th>Date</th>
                                <th>Class</th>
                                <th>Section</th>
                                <th>Subject</th>
                                <th class="text-center">Type</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Reason</th>
                                <th>Remarks</th>
                            </tr>
                            </thead>
                            <tbody>
                            @php $i = 1; @endphp
                            @foreach($records as $record)
                                @php $s = $record->session; @endphp
                                <tr @if($record->status === 'absent') class="table-danger" @elseif($record->status === 'late') class="table-warning" @endif>
                                    <td class="text-center">{{ $i++ }}</td>
                                    <td>{{ optional($s->date)->format('Y-m-d') ?? $s->date }}</td>
                                    <td>{{ optional($s->my_class)->name }}</td>
                                    <td>{{ optional($s->section)->name }}</td>
                                    <td>{{ optional($s->subject)->name ?? 'Daily' }}</td>
                                    <td class="text-center">{{ ucfirst($s->type) }}</td>
                                    <td class="text-center">{{ ucfirst($record->status) }}</td>
                                    <td class="text-center">
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
                                            <span class="badge badge-light">{{ $reasons[$record->absence_reason] ?? $record->absence_reason }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>{{ $record->remarks }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    @elseif($studentId)
        <div class="alert alert-info">No attendance records found for the selected criteria.</div>
    @endif

@endsection
