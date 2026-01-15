@extends('layouts.master')
@section('page_title', 'My Dashboard')

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
        .border-left-indigo { border-left: 4px solid #5c6bc0; }
        .border-left-warning { border-left: 4px solid #ffb300; }
        .border-left-teal { border-left: 4px solid #26a69a; }
        .bg-soft-primary { background: rgba(33,150,243,.08); }
        .bg-soft-danger { background: rgba(239,83,80,.08); }
        .bg-soft-success { background: rgba(102,187,106,.08); }
        .bg-soft-indigo { background: rgba(92,107,192,.08); }
        .bg-soft-warning { background: rgba(255,179,0,.08); }
        .bg-soft-teal { background: rgba(38,166,154,.08); }
        .text-primary { color: #1565c0 !important; }
        .text-danger { color: #c62828 !important; }
        .text-success { color: #2e7d32 !important; }
        .text-indigo { color: #3949ab !important; }
        .text-warning { color: #ef6c00 !important; }
        .text-teal { color: #00695c !important; }
    </style>

    @if(Qs::userIsTeamSA())
        <div class="row">
            @php
                $metrics = [
                    ['label' => 'Total Students', 'count' => $users->where('user_type', 'student')->count(), 'icon' => 'icon-users4', 'accent' => 'border-left-primary bg-soft-primary text-primary'],
                    ['label' => 'Total Teachers', 'count' => $users->where('user_type', 'teacher')->count(), 'icon' => 'icon-users2', 'accent' => 'border-left-danger bg-soft-danger text-danger'],
                    ['label' => 'Total Administrators', 'count' => $users->where('user_type', 'admin')->count(), 'icon' => 'icon-pointer', 'accent' => 'border-left-success bg-soft-success text-success'],
                    ['label' => 'Total Parents', 'count' => $users->where('user_type', 'parent')->count(), 'icon' => 'icon-user', 'accent' => 'border-left-indigo bg-soft-indigo text-indigo'],
                    ['label' => 'Total Staff', 'count' => $users->whereIn('user_type', ['teacher','admin','accountant','librarian'])->count(), 'icon' => 'icon-briefcase', 'accent' => 'border-left-warning bg-soft-warning text-warning'],
                    ['label' => 'User Accounts', 'count' => $users->count(), 'icon' => 'icon-database', 'accent' => 'border-left-teal bg-soft-teal text-teal'],
                ];
            @endphp

            @foreach($metrics as $metric)
                <div class="col-6 col-lg-4 col-xl-2">
                    <div class="card mini-stat {{ $metric['accent'] }}">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted text-uppercase font-size-xs mb-1">{{ $metric['label'] }}</p>
                                <h3 class="font-weight-semibold mb-0">{{ $metric['count'] }}</h3>
                            </div>
                            <div class="stat-icon">
                                <i class="{{ $metric['icon'] }}"></i>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{--Events Calendar Begins--}}
    <div class="card">
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
