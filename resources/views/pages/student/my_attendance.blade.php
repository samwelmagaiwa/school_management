@extends('layouts.master')

@section('page_title', 'My Attendance')

@section('content')

    <div class="card mb-3">
        <div class="card-header">
            <h6 class="card-title mb-0">My Attendance Summary</h6>
        </div>
        <div class="card-body">
            <form method="get" action="{{ route('student.attendance') }}">
                <div class="form-row">
                    <div class="col-md-3 mb-2">
                        <label class="font-weight-semibold">From</label>
                        <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="form-control">
                    </div>
                    <div class="col-md-3 mb-2">
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
                    <div class="col-md-3 mb-2">
                        <label class="font-weight-semibold">Type</label>
                        <select name="type" class="form-control">
                            <option value="">All</option>
                            <option value="daily" {{ ($filters['type'] ?? '') === 'daily' ? 'selected' : '' }}>Daily</option>
                            <option value="subject" {{ ($filters['type'] ?? '') === 'subject' ? 'selected' : '' }}>Subject</option>
                            <option value="event" {{ ($filters['type'] ?? '') === 'event' ? 'selected' : '' }}>Event</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-2">Filter</button>
            </form>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">
            <h6 class="card-title mb-0">Summary</h6>
        </div>
        <div class="card-body">
            <div class="row mb-2">
                <div class="col-md-3"><strong>Total Sessions:</strong> <span class="badge badge-light">{{ $summary['total'] }}</span></div>
                <div class="col-md-3"><strong>Present:</strong> <span class="badge badge-success">{{ $summary['present'] }}</span></div>
                <div class="col-md-3"><strong>Absent:</strong> <span class="badge badge-danger">{{ $summary['absent'] }}</span></div>
                <div class="col-md-3"><strong>Late:</strong> <span class="badge badge-warning">{{ $summary['late'] }}</span></div>
            </div>
            <div class="row mb-2">
                <div class="col-md-3"><strong>Excused:</strong> <span class="badge badge-info">{{ $summary['excused'] }}</span></div>
                <div class="col-md-3"><strong>Percentage Present:</strong>
                    @if($summary['percentage'] !== null)
                        <span class="badge badge-primary">{{ $summary['percentage'] }}%</span>
                    @else
                        <span class="text-muted">N/A</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h6 class="card-title mb-0">Attendance Details</h6>
        </div>
        <div class="card-body">
            <p class="mb-2 text-muted">
                Showing your attendance
                @if(($filters['from'] ?? null) || ($filters['to'] ?? null))
                    from <strong>{{ $filters['from'] ?? 'Start' }}</strong>
                    to <strong>{{ $filters['to'] ?? 'End' }}</strong>
                @else
                    across <span class="text-muted">all dates</span>
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

@endsection
