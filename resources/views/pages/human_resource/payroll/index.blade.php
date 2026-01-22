@extends('layouts.master')
@section('page_title', 'Payroll Readiness')
@section('content')

<div class="card">
    <div class="card-header header-elements-inline">
        <h6 class="card-title">Staff Salary List</h6>
        {!! Qs::getPanelOptions() !!}
    </div>

    <div class="card-body">
        <div class="mb-3">
            <a href="{{ route('hr.payroll.export') }}" class="btn btn-success"><i class="icon-download"></i> Export to CSV</a>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped datatable-button-html5-basic">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Employee ID</th>
                        <th>Name</th>
                        <th>Department</th>
                        <th>Designation</th>
                        <th>Basic Salary</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($staffs as $staff)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $staff->code }}</td>
                            <td>{{ $staff->user->name }}</td>
                            <td>{{ $staff->department->name ?? '-' }}</td>
                            <td>{{ $staff->designation->name ?? '-' }}</td>
                            <td>{{ number_format($staff->basic_salary, 2) }}</td>
                            <td>{{ ucfirst($staff->status) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
