@extends('layouts.master')
@section('page_title', 'Hostel Dashboard')
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
        .border-left-danger { border-left: 4px solid #ef5350; }
        .border-left-success { border-left: 4px solid #66bb6a; }
        .border-left-warning { border-left: 4px solid #ffb300; }
        .bg-soft-primary { background: rgba(33,150,243,.08); }
        .bg-soft-danger { background: rgba(239,83,80,.08); }
        .bg-soft-success { background: rgba(102,187,106,.08); }
        .bg-soft-warning { background: rgba(255,179,0,.08); }
        .text-primary { color: #1565c0 !important; }
        .text-danger { color: #c62828 !important; }
        .text-success { color: #2e7d32 !important; }
        .text-warning { color: #ef6c00 !important; }
    </style>

<div class="row">
    @php
        $stats = [
            ['label' => 'Total Dorms', 'count' => $total_dorms, 'icon' => 'icon-home', 'accent' => 'border-left-primary bg-soft-primary text-primary'],
            ['label' => 'Total Rooms', 'count' => $total_rooms, 'icon' => 'icon-office', 'accent' => 'border-left-success bg-soft-success text-success'],
            ['label' => 'Occupied Beds', 'count' => $occupied_beds, 'icon' => 'icon-bed', 'accent' => 'border-left-warning bg-soft-warning text-warning'],
            ['label' => 'Free Beds', 'count' => $free_beds, 'icon' => 'icon-bed2', 'accent' => 'border-left-danger bg-soft-danger text-danger'],
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
        <div class="card">
            <div class="card-header header-elements-inline">
                <h5 class="card-title">Recent Bed Allocations</h5>
                {!! Qs::getPanelOptions() !!}
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Dorm</th>
                                <th>Room No</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recent_allocations as $alloc)
                                <tr>
                                    <td>{{ $alloc->student_name }}</td>
                                    <td>{{ $alloc->dorm_name }}</td>
                                    <td>{{ $alloc->room_no }}</td>
                                    <td>{{ date('d M Y', strtotime($alloc->created_at)) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No recent allocations</td>
                                </tr>
                            @endforelse
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
                <a href="{{ route('dorms.index') }}" class="btn btn-primary btn-block mb-3">
                    <i class="icon-home mr-2"></i> Manage Dorms
                </a>
                <a href="{{ route('students.index') }}" class="btn btn-success btn-block mb-3">
                    <i class="icon-users mr-2"></i> Student List
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
