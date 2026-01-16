@extends('layouts.master')

@section('page_title', 'Class Attendance Report')

@section('content')

    <div class="card mb-3">
        <div class="card-header header-elements-inline">
            <h5 class="card-title"><i class="icon-stats-bars2 mr-2"></i> Class Attendance Report</h5>
            <div class="header-elements">
                <a href="{{ route('attendance.reports.index') }}" class="btn btn-sm btn-secondary">Back to Reports</a>
            </div>
        </div>
        <div class="card-body">
            <form method="get" action="{{ route('attendance.reports.class_page') }}">
                <div class="form-row">
                    <div class="col-md-3 mb-2">
                        <label class="font-weight-semibold">Class</label>
                        <select name="class_id" class="form-control select" required>
                            <option value="">Select Class</option>
                            @foreach($classes as $c)
                                <option value="{{ $c->id }}" {{ ($filters['class_id'] ?? '') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="font-weight-semibold">Section</label>
                        <select name="section_id" class="form-control select" required>
                            <option value="">Select Section</option>
                            @foreach($sections as $s)
                                <option value="{{ $s->id }}" {{ ($filters['section_id'] ?? '') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Sections list depends on selected class.</small>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="font-weight-semibold">From</label>
                        <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="form-control">
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="font-weight-semibold">To</label>
                        <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="form-control">
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
                <div class="form-row mt-2">
                    <div class="col-md-4 mb-2">
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
                </div>
                <button type="submit" class="btn btn-primary mt-2">Generate Report</button>
            </form>
        </div>
    </div>

    @if($summary)
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="card-title mb-0">Overall Summary for
                    @if($class) Class {{ $class->name }} @endif
                    @if($section) (Section {{ $section->name }}) @endif
                </h6>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-3"><strong>Total Marks:</strong> {{ $summary['total'] }}</div>
                    <div class="col-md-3"><strong>Present:</strong> {{ $summary['present'] }}</div>
                    <div class="col-md-3"><strong>Absent:</strong> {{ $summary['absent'] }}</div>
                    <div class="col-md-3"><strong>Late:</strong> {{ $summary['late'] }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3"><strong>Excused:</strong> {{ $summary['excused'] }}</div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">Per-Student Attendance Summary</h6>
            </div>
            <div class="card-body">
                <p class="mb-2 text-muted">
                    Showing attendance for
                    @if($class) <strong>Class {{ $class->name }}</strong> @endif
                    @if($section) <strong>(Section {{ $section->name }})</strong> @endif
                    @if(($filters['from'] ?? null) || ($filters['to'] ?? null))
                        &mdash; Date Range:
                        <strong>{{ $filters['from'] ?? 'Start' }}</strong>
                        to
                        <strong>{{ $filters['to'] ?? 'End' }}</strong>
                    @else
                        &mdash; <span class="text-muted">All dates</span>
                    @endif
                </p>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped datatable-button-html5-columns">
                        <thead>
                        <tr>
                            <th style="width: 3rem;" class="text-center">#</th>
                            <th>Student</th>
                            <th>Admission No</th>
                            <th class="text-center">Total</th>
                            <th class="text-center">Present</th>
                            <th class="text-center">Absent</th>
                            <th class="text-center">Late</th>
                            <th class="text-center">Excused</th>
                            <th class="text-center">% Present</th>
                        </tr>
                        </thead>
                        <tbody>
                        @php $i = 1; @endphp
                        @foreach($perStudent as $row)
                            @php $stu = $row['student']; @endphp
                            @if(!$stu)
                                @continue
                            @endif
                            <tr>
                                <td class="text-center">{{ $i++ }}</td>
                                <td>{{ $stu->name }}</td>
                                <td>{{ $stu->adm_no ?? '-' }}</td>
                                <td class="text-center"><span class="badge badge-light">{{ $row['total'] }}</span></td>
                                <td class="text-center"><span class="badge badge-success">{{ $row['present'] }}</span></td>
                                <td class="text-center"><span class="badge badge-danger">{{ $row['absent'] }}</span></td>
                                <td class="text-center"><span class="badge badge-warning">{{ $row['late'] }}</span></td>
                                <td class="text-center"><span class="badge badge-info">{{ $row['excused'] }}</span></td>
                                <td class="text-center">
                                    @if($row['percentage'] !== null)
                                        <span class="badge badge-primary">{{ $row['percentage'] }}%</span>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @elseif(($filters['class_id'] ?? null) && ($filters['section_id'] ?? null))
        <div class="alert alert-info">No attendance records found for the selected criteria.</div>
    @endif

@endsection
