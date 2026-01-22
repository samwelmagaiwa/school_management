@extends('layouts.master')
@section('page_title', 'My Attendance')
@section('content')

<div class="card">
    <div class="card-header header-elements-inline">
        <h6 class="card-title">My Attendance History</h6>
        {!! Qs::getPanelOptions() !!}
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped datatable-button-html5-basic">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Time In</th>
                        <th>Time Out</th>
                        <th>Late?</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($attendances as $att)
                        <tr>
                            <td>{{ $att->date->format('D, M d, Y') }}</td>
                            <td>
                                @if($att->status == 'present')
                                    <span class="badge badge-success">Present</span>
                                @elseif($att->status == 'absent')
                                    <span class="badge badge-danger">Absent</span>
                                @elseif($att->status == 'late')
                                    <span class="badge badge-warning">Late</span>
                                @else
                                    <span class="badge badge-secondary">{{ ucfirst($att->status) }}</span>
                                @endif
                            </td>
                            <td>{{ $att->clock_in_time ? date('h:i A', strtotime($att->clock_in_time)) : '-' }}</td>
                            <td>{{ $att->clock_out_time ? date('h:i A', strtotime($att->clock_out_time)) : '-' }}</td>
                            <td>
                                @if($att->is_late)
                                    <span class="text-danger font-weight-bold">Yes</span>
                                @else
                                    No
                                @endif
                            </td>
                            <td>{{ $att->remarks }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
