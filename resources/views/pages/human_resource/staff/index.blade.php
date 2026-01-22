@extends('layouts.master')
@section('page_title', 'Staff Management')
@section('content')

<div class="card">
    <div class="card-header header-elements-inline">
        <h6 class="card-title">Staff List</h6>
        {!! Qs::getPanelOptions() !!}
    </div>

    <div class="card-body">
        <div class="mb-3">
            <a href="{{ route('hr.staff.create') }}" class="btn btn-primary"><i class="icon-plus2 mr-2"></i> Add New Staff</a>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped datatable-button-html5-basic">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Photo</th>
                        <th>Name</th>
                        <th>Code</th>
                        <th>Department</th>
                        <th>Designation</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($staff as $s)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><img class="rounded-circle" style="height: 40px; width: 40px;" src="{{ $s->photo }}" alt="photo"></td>
                            <td>{{ $s->name }}</td>
                            <td>{{ $s->staff_record->code ?? $s->code }}</td>
                            <td>{{ $s->staff_record->department->name ?? '-' }}</td>
                            <td>{{ $s->staff_record->designation->name ?? '-' }}</td>
                            <td>{{ $s->phone }}</td>
                            <td>
                                @if($s->staff_record && $s->staff_record->status == 'active')
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-secondary">Inactive</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="list-icons">
                                    <div class="dropdown">
                                        <a href="#" class="list-icons-item" data-toggle="dropdown">
                                            <i class="icon-menu9"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <a href="{{ route('hr.staff.edit', $s->id) }}" class="dropdown-item"><i class="icon-pencil"></i> Edit</a>
                                            {{-- Add Show/Delete if needed --}}
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
