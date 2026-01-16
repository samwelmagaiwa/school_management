@extends('layouts.master')

@section('page_title', 'Attendance Reports')

@section('content')

    <div class="card">
        <div class="card-header header-elements-inline">
            <h5 class="card-title"><i class="icon-stats-bars mr-2"></i> Attendance Reports</h5>
            {!! Qs::getPanelOptions() !!}
        </div>

        <div class="card-body">
            <p class="mb-3">Select the attendance report you want to view. Use the links below to open a specific report.</p>

            <table class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th style="width: 25%">Report</th>
                    <th>Description</th>
                    <th style="width: 15%" class="text-center">Action</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td><strong>Class Summary</strong></td>
                    <td>Attendance percentages per student in a specific class &amp; section over a selected date range.</td>
                    <td class="text-center">
                        <a href="{{ route('attendance.reports.class_page') }}" class="btn btn-primary btn-sm">Open</a>
                    </td>
                </tr>
                <tr>
                    <td><strong>Student History</strong></td>
                    <td>Overall attendance statistics for a single student (any class/section you choose) within a date range.</td>
                    <td class="text-center">
                        <a href="{{ route('attendance.reports.student_page') }}" class="btn btn-primary btn-sm">Open</a>
                    </td>
                </tr>
                <tr>
                    <td><strong>Teacher Compliance</strong></td>
                    <td>Number of attendance sessions taken per teacher in the selected period (HODs see only their departments).</td>
                    <td class="text-center">
                        <a href="{{ route('attendance.reports.teacher_compliance') }}" class="btn btn-primary btn-sm">Open</a>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>

@endsection
