@extends('layouts.master')
@section('page_title', 'HR Reports Summary')
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
        .chart-tile {
            border: 1px solid rgba(0,0,0,.06);
            border-radius: .65rem;
            padding: 1.5rem;
            box-shadow: 0 10px 25px rgba(0,0,0,.05);
            background: #fff;
            height: 100%;
        }
        .chart-canvas {
            width: 100%;
            height: 320px;
        }
    </style>

<div class="row">
    @php
        $stats = [
            ['label' => 'Present Today', 'count' => $present_count, 'icon' => 'icon-users4', 'accent' => 'border-left-success bg-soft-success text-success'],
            ['label' => 'Absent Today', 'count' => $absent_count, 'icon' => 'icon-user-minus', 'accent' => 'border-left-danger bg-soft-danger text-danger'],
            ['label' => 'Late Arrivals', 'count' => $late_count, 'icon' => 'icon-alarm', 'accent' => 'border-left-warning bg-soft-warning text-warning'],
            ['label' => 'Pending Leaves', 'count' => $pending_leaves, 'icon' => 'icon-file-text2', 'accent' => 'border-left-primary bg-soft-primary text-primary'],
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
                <h5 class="card-title">Attendance Trends (Last 7 Days)</h5>
                {!! Qs::getPanelOptions() !!}
            </div>
            <div class="card-body">
                <div id="attendance_trend_chart" class="chart-canvas"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
         <div class="card">
            <div class="card-header header-elements-inline">
                <h6 class="card-title">Quick Actions</h6>
            </div>
            <div class="card-body">
                <a href="{{ route('hr.attendance.index') }}" class="btn btn-primary btn-block mb-3">
                    <i class="icon-calendar mr-2"></i> Mark Attendance
                </a>
                <a href="{{ route('hr.leaves.index') }}" class="btn btn-info btn-block mb-3">
                    <i class="icon-stack mr-2"></i> Manage Leaves
                </a>
                <a href="{{ route('hr.payroll.index') }}" class="btn btn-success btn-block mb-3">
                    <i class="icon-cash mr-2"></i> Payroll
                </a>
                <a href="{{ route('hr.staff.create') }}" class="btn btn-warning btn-block mb-3">
                    <i class="icon-user-plus mr-2"></i> Add New Staff
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

@section('scripts')
    @parent
    <script src="{{ asset('global_assets/js/plugins/visualization/echarts/echarts.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var chartEl = document.getElementById('attendance_trend_chart');

            if (chartEl && typeof echarts !== 'undefined') {
                var chart = echarts.init(chartEl);
                // Placeholder data - in a real app, pass this from controller
                var data = {
                    days: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                    present: [90, 92, 85, 88, 95, 20, 10] 
                };

                chart.setOption({
                    color: ['#66bb6a'],
                    tooltip: { trigger: 'axis', backgroundColor: 'rgba(0,0,0,0.75)', padding: [10, 15] },
                    grid: { left: 0, right: 0, top: 10, bottom: 0, containLabel: true },
                    xAxis: [{ type: 'category', boundaryGap: false, data: data.days }],
                    yAxis: [{ type: 'value' }],
                    series: [
                        { name: 'Present %', type: 'line', smooth: true, areaStyle: { opacity: 0.1 }, data: data.present }
                    ]
                });
            }
        });
    </script>
@endsection
