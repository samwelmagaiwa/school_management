@extends('layouts.master')
@section('page_title', 'Student Dashboard')
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
        .border-left-danger { border-left: 4px solid #ef5350; }
        .bg-soft-primary { background: rgba(33,150,243,.08); }
        .bg-soft-success { background: rgba(102,187,106,.08); }
        .bg-soft-warning { background: rgba(255,179,0,.08); }
        .bg-soft-danger { background: rgba(239,83,80,.08); }
        .text-primary { color: #1565c0 !important; }
        .text-success { color: #2e7d32 !important; }
        .text-warning { color: #ef6c00 !important; }
        .text-danger { color: #c62828 !important; }
    </style>

<div class="row">
    @php
        $avg = $latest_exam_record->ave ?? 0;
        $position = $latest_exam_record->pos ?? '-';
        $attendance = $attendance_percentage ?? 0;
        $outstanding = $fee_summary->total_outstanding ?? 0;
        
        $stats = [
            ['label' => 'Current Average', 'count' => number_format($avg, 1) . '%', 'icon' => 'icon-graduation2', 'accent' => 'border-left-primary bg-soft-primary text-primary'],
            ['label' => 'Class Position', 'count' => $position, 'icon' => 'icon-trophy3', 'accent' => 'border-left-success bg-soft-success text-success'],
            ['label' => 'Attendance', 'count' => $attendance . '%', 'icon' => 'icon-calendar', 'accent' => $attendance >= 75 ? 'border-left-success bg-soft-success text-success' : 'border-left-warning bg-soft-warning text-warning'],
            ['label' => 'Fee Balance', 'count' => number_format($outstanding, 0), 'icon' => 'icon-cash', 'accent' => $outstanding > 0 ? 'border-left-danger bg-soft-danger text-danger' : 'border-left-success bg-soft-success text-success'],
        ];
    @endphp

    @foreach($stats as $stat)
        <div class="col-sm-6 col-md-3">
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
        @if($latest_exam_record)
        <div class="card">
            <div class="card-header header-elements-inline">
                <h5 class="card-title">Latest Exam Performance - {{ $latest_exam_record->exam_name }}</h5>
                {!! Qs::getPanelOptions() !!}
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-4">
                        <h2 class="font-weight-semibold text-primary">{{ number_format($latest_exam_record->ave, 1) }}%</h2>
                        <p class="text-muted">Average Score</p>
                    </div>
                    <div class="col-md-4">
                        <h2 class="font-weight-semibold text-success">{{ $latest_exam_record->pos }}</h2>
                        <p class="text-muted">Position</p>
                    </div>
                    <div class="col-md-4">
                        <h2 class="font-weight-semibold text-info">{{ $latest_exam_record->total }}</h2>
                        <p class="text-muted">Total Marks</p>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if($borrowed_books && $borrowed_books->count() > 0)
        <div class="card mt-3">
            <div class="card-header header-elements-inline">
                <h5 class="card-title">Borrowed Books</h5>
                {!! Qs::getPanelOptions() !!}
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Book Title</th>
                                <th>Author</th>
                                <th>Due Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($borrowed_books as $book)
                                <tr>
                                    <td>{{ $book->title }}</td>
                                    <td>{{ $book->author }}</td>
                                    <td>{{ \Carbon\Carbon::parse($book->due_date)->format('d M Y') }}</td>
                                    <td>
                                        @if(\Carbon\Carbon::parse($book->due_date)->isPast())
                                            <span class="badge badge-danger">Overdue</span>
                                        @else
                                            <span class="badge badge-success">On Time</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="col-lg-4">
         <div class="card">
            <div class="card-header header-elements-inline">
                <h6 class="card-title">Quick Actions</h6>
            </div>
            <div class="card-body">
                <a href="{{ route('marks.year_selector', Qs::hash($sr->user_id)) }}" class="btn btn-primary btn-block mb-3">
                    <i class="icon-eye mr-2"></i> View My Results
                </a>
                <a href="{{ route('student.attendance') }}" class="btn btn-info btn-block mb-3">
                    <i class="icon-calendar mr-2"></i> My Attendance
                </a>
                @if(($fee_summary->total_outstanding ?? 0) > 0)
                <a href="{{ route('payments.invoice', [$sr->user_id]) }}" class="btn btn-warning btn-block mb-3">
                    <i class="icon-cash mr-2"></i> Pay Fees
                </a>
                @endif
            </div>
        </div>

        @if($upcoming_exams && $upcoming_exams->count() > 0)
        <div class="card mt-3">
            <div class="card-header header-elements-inline">
                <h6 class="card-title">Upcoming Exams</h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled">
                    @foreach($upcoming_exams as $exam)
                        <li class="mb-2">
                            <i class="icon-book mr-2"></i>
                            <strong>{{ $exam->name }}</strong>
                            <small class="text-muted d-block ml-4">Term {{ $exam->term }}</small>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif
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
