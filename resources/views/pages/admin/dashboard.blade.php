@extends('layouts.master')
@section('page_title', 'My Dashboard')

@section('content')
    <style>
        .chart-card {
            border-radius: .75rem;
            border: 1px solid rgba(0,0,0,.05);
            box-shadow: 0 15px 35px rgba(15,25,45,.07);
        }
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

    <div class="card chart-card mb-4">
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
                        <div id="admin_enrollment_chart" class="chart-canvas"></div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="chart-tile h-100">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <p class="text-muted text-uppercase font-size-xs mb-1">Attendance Trends</p>
                                <h6 class="font-weight-semibold mb-0">Average Daily Presence</h6>
                            </div>
                            <span class="badge badge-pill badge-light">Rolling 6 Months</span>
                        </div>
                        <div id="admin_attendance_chart" class="chart-canvas"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card chart-card">
        <div class="card-header header-elements-inline">
            <h5 class="card-title">School Events Calendar</h5>
            {!! Qs::getPanelOptions() !!}
        </div>
        <div class="card-body">
            <div class="fullcalendar-basic"></div>
        </div>
    </div>
@endsection

@php
    $adminEnrollment = [
        'labels' => ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
        'new' => [18, 26, 32, 30],
        'returning' => [55, 52, 58, 61],
    ];

    $adminAttendance = [
        'months' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        'middle' => [87, 90, 91, 92, 93, 95],
        'senior' => [82, 85, 84, 87, 88, 90],
    ];
@endphp

@section('scripts')
    @parent
    <script src="{{ asset('global_assets/js/plugins/visualization/echarts/echarts.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var barEl = document.getElementById('admin_enrollment_chart');
            var lineEl = document.getElementById('admin_attendance_chart');

            if (typeof echarts === 'undefined') {
                console.warn('ECharts is required for dashboard charts.');
                return;
            }

            if (barEl) {
                var barChart = echarts.init(barEl);
                var enrollment = @json($adminEnrollment);
                barChart.setOption({
                    color: ['#42a5f5', '#ef6c00'],
                    tooltip: { trigger: 'axis', backgroundColor: 'rgba(0,0,0,0.75)', padding: [10, 15] },
                    legend: { data: ['New', 'Returning'] },
                    grid: { left: 0, right: 0, top: 25, bottom: 0, containLabel: true },
                    xAxis: [{ type: 'category', data: enrollment.labels, axisTick: { alignWithLabel: true } }],
                    yAxis: [{ type: 'value', axisLabel: { formatter: '{value} students' } }],
                    series: [
                        { name: 'New', type: 'bar', barWidth: '35%', data: enrollment.new },
                        { name: 'Returning', type: 'bar', barWidth: '35%', data: enrollment.returning }
                    ]
                });
            }

            if (lineEl) {
                var lineChart = echarts.init(lineEl);
                var attendance = @json($adminAttendance);
                lineChart.setOption({
                    color: ['#00bcd4', '#5c6bc0'],
                    tooltip: { trigger: 'axis', backgroundColor: 'rgba(0,0,0,0.75)', padding: [10, 15] },
                    legend: { data: ['Middle School', 'Senior School'] },
                    grid: { left: 0, right: 0, top: 30, bottom: 0, containLabel: true },
                    xAxis: [{ type: 'category', boundaryGap: false, data: attendance.months }],
                    yAxis: [{ type: 'value', axisLabel: { formatter: '{value}%' } }],
                    series: [
                        { name: 'Middle School', type: 'line', smooth: true, areaStyle: { opacity: 0.05 }, data: attendance.middle },
                        { name: 'Senior School', type: 'line', smooth: true, areaStyle: { opacity: 0.05 }, data: attendance.senior }
                    ]
                });
            }
        });
    </script>
@endsection
