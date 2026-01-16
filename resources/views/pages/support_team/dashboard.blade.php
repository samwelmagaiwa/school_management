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

    {{--Statistics Charts Begins--}}
    <div class="card">
        <div class="card-header header-elements-inline">
            <h5 class="card-title">Performance Insights</h5>
            {!! Qs::getPanelOptions() !!}
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <div class="chart-tile h-100">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <p class="text-muted text-uppercase font-size-xs mb-1">Enrollment Overview</p>
                                <h6 class="font-weight-semibold mb-0">New vs Returning Students</h6>
                            </div>
                            <span class="badge badge-pill badge-light">This Term</span>
                        </div>
                        <div id="enrollment_bar_chart" class="chart-canvas"></div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="chart-tile h-100">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <p class="text-muted text-uppercase font-size-xs mb-1">Attendance Trends</p>
                                <h6 class="font-weight-semibold mb-0">Average Daily Presence</h6>
                            </div>
                            <span class="badge badge-pill badge-light">Last 6 Months</span>
                        </div>
                        <div id="attendance_multi_line_chart" class="chart-canvas"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{--Statistics Charts Ends--}}

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

@php
    $demoEnrollment = [
        'labels' => ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
        'new' => [25, 32, 41, 38],
        'returning' => [42, 39, 45, 48],
    ];

    $attendanceTrend = [
        'months' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        'junior' => [88, 91, 90, 93, 95, 96],
        'senior' => [85, 87, 89, 88, 90, 92],
    ];
@endphp

@section('scripts')
    @parent
    <script src="{{ asset('global_assets/js/plugins/visualization/echarts/echarts.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var enrollmentChartEl = document.getElementById('enrollment_bar_chart');
            var attendanceChartEl = document.getElementById('attendance_multi_line_chart');

            if (typeof echarts === 'undefined') {
                console.warn('ECharts is required for dashboard charts.');
                return;
            }

            if (enrollmentChartEl) {
                var enrollmentChart = echarts.init(enrollmentChartEl);
                var enrollmentData = @json($demoEnrollment);
                enrollmentChart.setOption({
                    color: ['#42a5f5', '#ef6c00'],
                    grid: { left: 0, right: 0, top: 20, bottom: 0, containLabel: true },
                    tooltip: { trigger: 'axis', backgroundColor: 'rgba(0,0,0,0.75)', padding: [10, 15] },
                    legend: { data: ['New', 'Returning'] },
                    xAxis: [{
                        type: 'category',
                        data: enrollmentData.labels,
                        axisTick: { alignWithLabel: true }
                    }],
                    yAxis: [{ type: 'value', axisLabel: { formatter: '{value} students' } }],
                    series: [
                        { name: 'New', type: 'bar', barWidth: '35%', data: enrollmentData.new },
                        { name: 'Returning', type: 'bar', barWidth: '35%', data: enrollmentData.returning }
                    ]
                });
            }

            if (attendanceChartEl) {
                var attendanceChart = echarts.init(attendanceChartEl);
                var attendanceData = @json($attendanceTrend);
                attendanceChart.setOption({
                    color: ['#00bcd4', '#5c6bc0'],
                    tooltip: { trigger: 'axis', backgroundColor: 'rgba(0,0,0,0.75)', padding: [10, 15] },
                    legend: { data: ['Junior School', 'Senior School'] },
                    grid: { left: 0, right: 0, top: 30, bottom: 0, containLabel: true },
                    xAxis: [{ type: 'category', boundaryGap: false, data: attendanceData.months }],
                    yAxis: [{ type: 'value', axisLabel: { formatter: '{value}%' } }],
                    series: [
                        { name: 'Junior School', type: 'line', smooth: true, areaStyle: { opacity: 0.05 }, data: attendanceData.junior },
                        { name: 'Senior School', type: 'line', smooth: true, areaStyle: { opacity: 0.05 }, data: attendanceData.senior }
                    ]
                });
            }
        });
    </script>
@endsection
