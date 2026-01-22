@extends('layouts.master')
@section('page_title', 'Teacher Dashboard')
@section('content')

    <style>
        .mini-stat {
            border-radius: .5rem;
            border: 1px solid rgba(0,0,0,.05);
            box-shadow: 0 8px 14px rgba(0,0,0,.03);
            transition: all .2s ease;
        }
        .mini-stat .card-body {
            padding: 1.25rem 1.5rem;
            min-height: 110px;
        }
        .mini-stat .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: rgba(0,0,0,.04);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            color: inherit;
        }
        .mini-stat h3 {
            font-size: 1.7rem;
        }
        .mini-stat:hover {
            transform: translateY(-3px);
            box-shadow: 0 18px 30px rgba(0,0,0,.08);
        }
        .border-left-primary { border-left: 4px solid #2196f3; }
        .border-left-success { border-left: 4px solid #66bb6a; }
        .border-left-warning { border-left: 4px solid #ffb300; }
        .bg-soft-primary { background: rgba(33,150,243,.08); }
        .bg-soft-success { background: rgba(102,187,106,.08); }
        .bg-soft-warning { background: rgba(255,179,0,.08); }
        .text-primary { color: #1565c0 !important; }
        .text-success { color: #2e7d32 !important; }
        .text-warning { color: #ef6c00 !important; }
    </style>

<div class="row">
    @php
        $stats = [
            ['label' => 'My Subjects', 'count' => $total_subjects ?? 0, 'icon' => 'icon-book', 'accent' => 'border-left-primary bg-soft-primary text-primary'],
            ['label' => 'Total Students', 'count' => $total_students ?? 0, 'icon' => 'icon-users', 'accent' => 'border-left-success bg-soft-success text-success'],
            ['label' => 'Pending Marks', 'count' => $pending_marks_count ?? 0, 'icon' => 'icon-file-text2', 'accent' => 'border-left-warning bg-soft-warning text-warning'],
        ];
    @endphp

    @foreach($stats as $stat)
        <div class="col-sm-6 col-md-4">
             <div class="card mini-stat {{ $stat['accent'] }}">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted text-uppercase font-size-xs mb-1">{{ $stat['label'] }}</p>
                        <h3 class="font-weight-semibold mb-0">{{ $stat['count'] }}</h3>
                    </div>
                    <div class="stat-icon">
                        <i class="{{ $stat['icon'] }}"></i>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header header-elements-inline">
                <h5 class="card-title">Today's Timetable</h5>
                {!! Qs::getPanelOptions() !!}
            </div>
            <div class="card-body">
                @if($todays_schedule && $todays_schedule->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Subject</th>
                                    <th>Class</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($todays_schedule as $schedule)
                                    <tr>
                                        <td><span class="badge badge-primary">{{ date('h:i A', strtotime($schedule->time_from)) }} - {{ date('h:i A', strtotime($schedule->time_to)) }}</span></td>
                                        <td><strong>{{ $schedule->subject_name }}</strong></td>
                                        <td>{{ $schedule->class_name }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted text-center py-4">No classes scheduled for today</p>
                @endif
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header header-elements-inline">
                <h5 class="card-title">My Subjects</h5>
                {!! Qs::getPanelOptions() !!}
            </div>
            <div class="card-body">
                @if($subjects && $subjects->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Subject</th>
                                    <th>Class</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($subjects as $index => $subject)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $subject->subject_name }}</td>
                                        <td><span class="badge badge-info">{{ $subject->class_name }}</span></td>
                                        <td>
                                            <a href="{{ route('marks.index') }}" class="btn btn-sm btn-outline-primary">Enter Marks</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted text-center">No subjects assigned</p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
         <div class="card">
            <div class="card-header header-elements-inline">
                <h6 class="card-title">Quick Actions</h6>
            </div>
            <div class="card-body">
                <a href="{{ route('attendance.sessions.index') }}" class="btn btn-primary btn-block mb-3">
                    <i class="icon-calendar mr-2"></i> Mark Attendance
                </a>
                <a href="{{ route('marks.index') }}" class="btn btn-success btn-block mb-3">
                    <i class="icon-pencil mr-2"></i> Enter Marks
                </a>
                <a href="{{ route('tt.index') }}" class="btn btn-info btn-block mb-3">
                    <i class="icon-table2 mr-2"></i> View Timetable
                </a>
            </div>
        </div>
    </div>
</div>

    {{--Events Calendar Begins--}}
    <div class="card mt-4">
        <div class="card-header header-elements-inline">
            <h5 class="card-title">School Events Calendar</h5>
         {!! Qs::getPanelOptions() !!}
        </div>

        <div class="card-body">
            <div class="fullcalendar-basic"></div>
        </div>
    </div>
    {{--Events Calendar Ends--}}

@endsection
