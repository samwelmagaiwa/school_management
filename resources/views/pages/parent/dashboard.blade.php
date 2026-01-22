@extends('layouts.master')
@section('page_title', 'Parent Dashboard')
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
        .border-left-danger { border-left: 4px solid #ef5350; }
        .bg-soft-primary { background: rgba(33,150,243,.08); }
        .bg-soft-success { background: rgba(102,187,106,.08); }
        .bg-soft-danger { background: rgba(239,83,80,.08); }
        .text-primary { color: #1565c0 !important; }
        .text-success { color: #2e7d32 !important; }
        .text-danger { color: #c62828 !important; }
    </style>

<div class="row">
    @php
        $stats = [
            ['label' => 'My Children', 'count' => $children->count(), 'icon' => 'icon-users', 'accent' => 'border-left-primary bg-soft-primary text-primary'],
            ['label' => 'Attendance', 'count' => ($attendance_percentage ?? 0) . '%', 'icon' => 'icon-calendar', 'accent' => 'border-left-success bg-soft-success text-success'],
            ['label' => 'Total Fees Due', 'count' => number_format($fee_summary->total_outstanding ?? 0, 0), 'icon' => 'icon-cash', 'accent' => ($fee_summary->total_outstanding ?? 0) > 0 ? 'border-left-danger bg-soft-danger text-danger' : 'border-left-success bg-soft-success text-success'],
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
                <h5 class="card-title">My Children</h5>
                {!! Qs::getPanelOptions() !!}
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Class</th>
                                <th>Latest Exam</th>
                                <th>Average</th>
                                <th>Position</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($children as $child)
                                <tr>
                                    <td><strong>{{ $child->user->name }}</strong></td>
                                    <td><span class="badge badge-info">{{ $child->my_class->name }}</span></td>
                                    <td>{{ $child->latest_result->exam_name ?? 'N/A' }}</td>
                                    <td>{{ $child->latest_result ? number_format($child->latest_result->ave, 1) . '%' : '-' }}</td>
                                    <td>{{ $child->latest_result->pos ?? '-' }}</td>
                                    <td>
                                        <a href="{{ route('marks.year_selector', Qs::hash($child->user_id)) }}" class="btn btn-sm btn-outline-primary">View Results</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
         <div class="card">
            <div class="card-header header-elements-inline">
                <h6 class="card-title">Quick Actions</h6>
            </div>
            <div class="card-body">
                @if($children->count() > 0)
                    <a href="{{ route('payments.invoice', [$children->first()->user_id]) }}" class="btn btn-primary btn-block mb-3">
                        <i class="icon-cash mr-2"></i> Pay Fees
                    </a>
                @endif
                <a href="{{ route('my_account') }}" class="btn btn-info btn-block mb-3">
                    <i class="icon-user mr-2"></i> My Account
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
