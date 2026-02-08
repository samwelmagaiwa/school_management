@extends('layouts.master')
@section('page_title', 'School Details')
@section('content')

    <div class="row">
        <div class="col-md-3">
            {{-- School Info Card --}}
            <div class="card">
                <div class="card-header bg-{{ $school->status === 'active' ? 'success' : 'danger' }} text-white header-elements-inline">
                    <h6 class="card-title font-weight-semibold">School Info</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>School Code:</strong><br>
                        <span class="text-primary text-uppercase" style="font-size: 1.2em;">{{ $school->school_code }}</span>
                    </div>
                    <div class="mb-3">
                        <strong>School Name:</strong><br>
                        {{ $school->name }}
                    </div>
                    <div class="mb-3">
                        <strong>Status:</strong><br>
                        @if($school->status === 'active')
                            <span class="badge badge-success">Active</span>
                        @else
                            <span class="badge badge-danger">Inactive</span>
                        @endif
                    </div>
                    
                    {{-- Settings Display --}}
                    @if(!empty($school->settings))
                        <hr>
                        @if(!empty($school->settings['motto']))
                            <div class="mb-2">
                                <strong>Motto:</strong><br>
                                <em class="text-muted">{{ $school->settings['motto'] }}</em>
                            </div>
                        @endif
                        @if(!empty($school->settings['phone']))
                            <div class="mb-2">
                                <strong>Phone:</strong> {{ $school->settings['phone'] }}
                            </div>
                        @endif
                        @if(!empty($school->settings['email']))
                            <div class="mb-2">
                                <strong>Email:</strong> {{ $school->settings['email'] }}
                            </div>
                        @endif
                        @if(!empty($school->settings['theme_color']))
                            <div class="mb-2">
                                <strong>Theme:</strong> 
                                <span class="badge" style="background-color: {{ $school->settings['theme_color'] }}; color: #fff;">
                                    {{ $school->settings['theme_color'] }}
                                </span>
                            </div>
                        @endif
                    @endif

                    {{-- Primary Contact --}}
                    @if($super_admin)
                        <div class="mb-3">
                            <strong>Primary Admin:</strong><br>
                            {{ $super_admin->name }}<br>
                            <span class="text-muted" style="font-size: 0.9em;">{{ $super_admin->email }}</span>
                        </div>
                    @endif

                    <div class="mb-3">
                        <strong>Created:</strong><br>
                        {{ optional($school->created_at)->format('D, M d, Y h:i A') ?? 'N/A' }}
                    </div>
                    <div class="mb-3">
                        <strong>Last Updated:</strong><br>
                        {{ optional($school->updated_at)->format('M d, Y') ?? 'N/A' }}
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('nexoryatech.schools.edit', $school->id) }}" class="btn btn-primary btn-block">
                        <i class="icon-pencil mr-1"></i> Edit School
                    </a>
                    <form method="POST" action="{{ route('nexoryatech.schools.toggle_status', $school->id) }}">
                        @csrf
                        <button type="submit" class="btn btn-{{ $school->status === 'active' ? 'danger' : 'success' }} btn-block mt-2">
                            <i class="icon-{{ $school->status === 'active' ? 'cross2' : 'checkmark2' }} mr-1"></i>
                            {{ $school->status === 'active' ? 'Deactivate' : 'Activate' }}
                        </button>
                    </form>
                </div>
            </div>

            {{-- Statistics Card --}}
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title font-weight-semibold">Statistics</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <i class="icon-users mr-2"></i>
                        <strong>Total Users:</strong> {{ $school->users_count }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            {{-- Users Table --}}
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title font-weight-semibold">Recent Users</h6>
                </div>
                <div class="card-body">
                    <table class="table table-hover">
                        <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Username</th>
                            <th>User Type</th>
                            <th>Created</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($school->users as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->username }}</td>
                                <td>
                                    <span class="badge badge-{{ $user->user_type === 'super_admin' ? 'danger' : 'info' }}">
                                        {{ ucfirst(str_replace('_', ' ', $user->user_type)) }}
                                    </span>
                                </td>
                                <td>{{ optional($user->created_at)->format('M d, Y') ?? 'N/A' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted"><em>No users found.</em></td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="text-center mt-3">
        <a href="{{ route('nexoryatech.schools.index') }}" class="btn btn-light">
            <i class="icon-arrow-left13 mr-1"></i> Back to Schools
        </a>
    </div>

@endsection
