@extends('layouts.master')
@section('page_title', 'Leave Management')
@section('content')

<div class="card">
    <div class="card-header header-elements-inline">
        <h6 class="card-title">Leave Requests</h6>
        {!! Qs::getPanelOptions() !!}
    </div>

    <div class="card-body">
        <div class="mb-3">
            <a href="{{ route('hr.leaves.create') }}" class="btn btn-primary"><i class="icon-plus2 mr-2"></i> Apply for Leave</a>
        </div>
        
        <div class="table-responsive">
            <table class="table table-bordered table-striped datatable-button-html5-basic">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Applicant</th>
                        <th>Type</th>
                        <th>Duration</th>
                        <th>Dates</th>
                        <th>Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($leaves as $leave)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $leave->staff->name ?? 'N/A' }}</td>
                            <td>{{ $leave->type->name ?? 'N/A' }}</td>
                            <td>{{ $leave->days_requested }} Days</td>
                            <td>
                                {{ $leave->start_date->format('M d') }} - {{ $leave->end_date->format('M d, Y') }}
                            </td>
                            <td>
                                @if($leave->status == 'Approved')
                                    <span class="badge badge-success">Approved</span>
                                @elseif($leave->status == 'Rejected')
                                    <span class="badge badge-danger">Rejected</span>
                                @else
                                    <span class="badge badge-warning">Pending</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="list-icons">
                                    <div class="dropdown">
                                        <a href="#" class="list-icons-item" data-toggle="dropdown">
                                            <i class="icon-menu9"></i>
                                        </a>

                                        <div class="dropdown-menu dropdown-menu-right">
                                            {{-- View Attachment if exists (logic not fully implemented but placed here) --}}
                                            @if($leave->attachment)
                                                <a href="{{ asset('storage/'.$leave->attachment) }}" target="_blank" class="dropdown-item"><i class="icon-file-eye"></i> View Attachment</a>
                                            @endif

                                            @if(Qs::userIsTeamSA() && $leave->status == 'Pending')
                                                <a href="#" class="dropdown-item" onclick="document.getElementById('approve-{{ $leave->id }}').submit();"><i class="icon-checkmark3 text-success"></i> Approve</a>
                                                <form id="approve-{{ $leave->id }}" action="{{ route('hr.leaves.update', $leave->id) }}" method="POST" style="display: none;">
                                                    @csrf @method('PUT')
                                                    <input type="hidden" name="status" value="Approved">
                                                </form>

                                                <a href="#" class="dropdown-item" onclick="document.getElementById('reject-{{ $leave->id }}').submit();"><i class="icon-cross2 text-danger"></i> Reject</a>
                                                <form id="reject-{{ $leave->id }}" action="{{ route('hr.leaves.update', $leave->id) }}" method="POST" style="display: none;">
                                                    @csrf @method('PUT')
                                                    <input type="hidden" name="status" value="Rejected">
                                                </form>
                                            @endif
                                            
                                            {{-- Delete Action can be added here --}}
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
