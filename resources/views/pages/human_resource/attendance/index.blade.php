@extends('layouts.master')
@section('page_title', 'Staff Attendance')
@section('content')

<div class="card">
    <div class="card-header header-elements-inline">
        <h6 class="card-title">Mark Staff Attendance</h6>
        {!! Qs::getPanelOptions() !!}
    </div>

    <div class="card-body">
        <form method="GET" action="{{ route('hr.attendance.index') }}">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="date" class="font-weight-bold">Date:</label>
                        <input type="date" name="date" class="form-control" value="{{ $date }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="department_id" class="font-weight-bold">Department:</label>
                        <select class="form-control select" name="department_id" onchange="this.form.submit()">
                            <option value="all">All Departments</option>
                            @foreach($departments as $dept)
                                <option {{ $department_id == $dept->id ? 'selected' : '' }} value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-4 mt-4">
                    <button type="submit" class="btn btn-primary">Filter</button>
                </div>
            </div>
        </form>

        <form method="POST" action="{{ route('hr.attendance.store') }}">
            @csrf
            <input type="hidden" name="date" value="{{ $date }}">
            
            <div class="table-responsive mt-3">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Staff Name</th>
                            <th>Status</th>
                            <th>Time In</th>
                            <th>Time Out</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($staffs as $staff)
                            @php
                                $att = $staff->staff_attendances->first();
                                $status = $att ? $att->status : 'absent'; 
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $staff->name }} <br> <small>{{ $staff->staff_record->code ?? '' }}</small></td>
                                <td>
                                    @foreach(['present', 'absent', 'late', 'half_day', 'on_leave'] as $st)
                                        <div class="form-check form-check-inline">
                                            <label class="form-check-label">
                                                <input type="radio" name="attendance[{{ $staff->id }}]" value="{{ $st }}" class="form-check-input" {{ $status == $st ? 'checked' : '' }}>
                                                {{ ucfirst(str_replace('_', ' ', $st)) }}
                                            </label>
                                        </div>
                                    @endforeach
                                </td>
                                <td>
                                    <input type="time" name="clock_in_time[{{ $staff->id }}]" class="form-control form-control-sm" value="{{ $att->clock_in_time ?? '' }}">
                                </td>
                                <td>
                                    <input type="time" name="clock_out_time[{{ $staff->id }}]" class="form-control form-control-sm" value="{{ $att->clock_out_time ?? '' }}">
                                </td>
                                <td>
                                    <input type="text" name="remarks[{{ $staff->id }}]" class="form-control form-control-sm" placeholder="Remarks" value="{{ $att->remarks ?? '' }}">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="text-right mt-3">
                <button type="submit" class="btn btn-primary">Save Attendance</button>
            </div>
        </form>
    </div>
</div>

@endsection
