@extends('layouts.master')

@section('page_title', 'Teacher Compliance Report')

@section('content')

    <div class="card mb-3">
        <div class="card-header header-elements-inline">
            <h5 class="card-title"><i class="icon-user-tie mr-2"></i> Teacher Compliance Report</h5>
            <div class="header-elements">
                <a href="{{ route('attendance.reports.index') }}" class="btn btn-sm btn-secondary">Back to Reports</a>
            </div>
        </div>
        <div class="card-body">
            <form method="get" action="{{ route('attendance.reports.teacher_compliance') }}">
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
                <button type="submit" class="btn btn-primary mt-2">Generate Report</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h6 class="card-title mb-0">Compliance Summary</h6>
        </div>
        <div class="card-body">
            <p class="mb-2 text-muted">
                Sessions taken per teacher
                @if(($filters['from'] ?? null) || ($filters['to'] ?? null))
                    in date range
                    <strong>{{ $filters['from'] ?? 'Start' }}</strong>
                    to
                    <strong>{{ $filters['to'] ?? 'End' }}</strong>
                @else
                    across <span class="text-muted">all dates</span>
                @endif
            </p>
            @if(empty($rows))
                <p class="text-muted mb-0">No sessions found for the selected criteria.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-striped datatable-button-html5-columns">
                        <thead>
                        <tr>
                            <th style="width: 3rem;" class="text-center">#</th>
                            <th>Teacher</th>
                            <th class="text-center">Sessions Taken</th>
                        </tr>
                        </thead>
                        <tbody>
                        @php $i = 1; @endphp
                        @foreach($rows as $row)
                            <tr>
                                <td class="text-center">{{ $i++ }}</td>
                                <td>{{ $row['teacher_name'] ?? 'Unknown' }}</td>
                                <td class="text-center">
                                    <span class="badge badge-primary">{{ $row['sessions_taken'] }}</span>
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
