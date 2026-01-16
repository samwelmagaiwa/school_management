@extends('layouts.master')
@section('page_title', 'Attendance Sessions')
@section('content')

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title">Attendance Sessions</h6>
            {!! Qs::getPanelOptions() !!}
        </div>

        <div class="card-body">
            <form method="get" action="{{ route('attendance.sessions.index') }}" class="mb-3">
                <div class="form-row">
                    <div class="col-md-2 mb-2">
                        <label class="font-weight-semibold">Class</label>
                        <select name="class_id" class="form-control select">
                            <option value="">All</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ ($filters['class_id'] ?? '') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="font-weight-semibold">Subject</label>
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
                        <select name="type" class="form-control select">
                            <option value="">All</option>
                            <option value="daily" {{ ($filters['type'] ?? '') === 'daily' ? 'selected' : '' }}>Daily</option>
                            <option value="subject" {{ ($filters['type'] ?? '') === 'subject' ? 'selected' : '' }}>Subject</option>
                            <option value="event" {{ ($filters['type'] ?? '') === 'event' ? 'selected' : '' }}>Event</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="font-weight-semibold">From</label>
                        <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="form-control">
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="font-weight-semibold">To</label>
                        <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="form-control">
                    </div>
                    <div class="col-md-1 mb-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-block">Filter</button>
                    </div>
                </div>
            </form>

            <form method="post" action="{{ route('attendance.sessions.store') }}" class="mb-4">
                @csrf
                <div class="form-row align-items-end">
                    <div class="col-md-2 mb-2">
                        <label class="font-weight-semibold">Date</label>
                        <input type="date" name="date" class="form-control" required>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="font-weight-semibold">Class</label>
                        <select name="my_class_id" class="form-control select" required>
                            <option value="">Select</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="font-weight-semibold">Subject (optional)</label>
                        <select name="subject_id" class="form-control select-search">
                            <option value="">Daily / Homeroom</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}">{{ $subject->name }} (Class {{ optional($subject->my_class)->name }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="font-weight-semibold">Type</label>
                        <select name="type" class="form-control" required>
                            <option value="daily">Daily</option>
                            <option value="subject">Subject</option>
                            <option value="event">Event</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <button type="submit" class="btn btn-success mt-3">Create Session</button>
                    </div>
                </div>
                <small class="form-text text-muted">Only the assigned teacher for the selected class/subject will be allowed to create the session; others will receive a 403.</small>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                    <tr>
                        <th>Date</th>
                        <th>Class</th>
                        <th>Section</th>
                        <th>Subject</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Taken By</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($sessions as $session)
                        <tr>
                            <td>{{ \Illuminate\Support\Carbon::parse($session->date)->format('Y-m-d') }}</td>
                            <td>{{ optional($session->my_class)->name }}</td>
                            <td>{{ optional($session->section)->name ?: '—' }}</td>
                            <td>{{ optional($session->subject)->name ?: '—' }}</td>
                            <td>{{ ucfirst($session->type) }}</td>
                            <td>
                                <span class="badge badge-{{ $session->status == 'open' ? 'warning' : ($session->status == 'submitted' ? 'info' : 'success') }}">
                                    {{ ucfirst($session->status) }}
                                </span>
                            </td>
                            <td>{{ optional($session->teacher)->name }}</td>
                            <td>
                                <div class="d-flex">
                                    @if($session->status === 'open')
                                        <a href="{{ route('attendance.sessions.mark', $session->id) }}"
                                           class="btn btn-sm btn-outline-primary mr-1">Mark Attendance</a>
                                        <form method="post" action="{{ route('attendance.sessions.submit', $session->id) }}" onsubmit="return confirm('Submit and lock this attendance session?');" class="mr-1">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success">Submit</button>
                                        </form>
                                    @else
                                        <a href="{{ route('attendance.sessions.show', $session->id) }}"
                                           class="btn btn-sm btn-outline-secondary mr-1">View</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">No attendance sessions found.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            {{ $sessions->appends(request()->query())->links() }}
        </div>
    </div>

@endsection
