@extends('layouts.master')
@section('page_title', 'Manage Schools')
@section('content')

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title font-weight-semibold">All Schools</h6>
            <div class="header-elements">
                <div class="list-icons">
                    <a href="{{ route('nexoryatech.schools.create') }}" class="btn btn-primary btn-sm"><i class="icon-plus2 mr-1"></i> Create New School</a>
                </div>
            </div>
        </div>

        <div class="card-body">
            <table class="table datatable-button-html5-columns">
                <thead>
                <tr>
                    <th>S/N</th>
                    <th>School Code</th>
                    <th>School Name</th>
                    <th>Status</th>
                    <th>Total Users</th>
                    <th>Created</th>
                    <th class="text-center">Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse($schools as $school)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td><strong>{{ $school->school_code }}</strong></td>
                        <td>{{ $school->name }}</td>
                        <td>
                            @if($school->status === 'active')
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-danger">Inactive</span>
                            @endif
                        </td>
                        <td>{{ $school->users_count ?? 0 }}</td>
                        <td>{{ optional($school->created_at)->format('M d, Y') ?? 'N/A' }}</td>
                        <td class="text-center">
                            <div class="list-icons">
                                <div class="dropdown">
                                    <a href="#" class="list-icons-item" data-toggle="dropdown">
                                        <i class="icon-menu9"></i>
                                    </a>

                                    <div class="dropdown-menu dropdown-menu-right">
                                        <a href="{{ route('nexoryatech.schools.show', $school->id) }}" class="dropdown-item"><i class="icon-eye"></i> View Details</a>
                                        <a href="{{ route('nexoryatech.schools.edit', $school->id) }}" class="dropdown-item"><i class="icon-pencil"></i> Edit</a>
                                        
                                        <form method="POST" action="{{ route('nexoryatech.schools.toggle_status', $school->id) }}">
                                            @csrf
                                            <button type="submit" class="dropdown-item">
                                                <i class="icon-{{ $school->status === 'active' ? 'cross2' : 'checkmark2' }}"></i> 
                                                {{ $school->status === 'active' ? 'Deactivate' : 'Activate' }}
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted"><em>No schools found.</em></td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($schools->hasPages())
            <div class="card-footer">
                {{ $schools->links() }}
            </div>
        @endif
    </div>

@endsection
