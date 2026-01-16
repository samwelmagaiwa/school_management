@extends('layouts.master')
@section('page_title', 'Advanced Activity Logs')

@push('styles')
    <style>
        .activity-sidebar-card {
            background: linear-gradient(135deg, #1e88e5, #1976d2);
            background-color: #1e88e5; /* fallback for devices/browsers that ignore gradients */
            color: #fff;
            box-shadow: 0 15px 35px rgba(25, 118, 210, 0.25);
        }

        .activity-sidebar-card .card-header,
        .activity-sidebar-card .card-body {
            background: transparent !important;
            color: inherit;
            border-color: rgba(255, 255, 255, 0.25) !important;
        }

        .activity-sidebar-card .card-header .card-title,
        .activity-sidebar-card .card-header i,
        .activity-sidebar-card .font-size-sm,
        .activity-sidebar-card .text-muted,
        .activity-sidebar-card h3 {
            color: #fff !important;
        }

        .activity-sidebar-card .media-list {
            border-color: rgba(255, 255, 255, 0.2);
        }

        .activity-sidebar-card .media-list .media {
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
        }

        .activity-sidebar-card .badge-success,
        .activity-sidebar-card .badge-secondary {
            background: rgba(255, 255, 255, 0.25);
            color: #fff;
        }

        .activity-sidebar-card .badge-success {
            background: rgba(46, 204, 113, 0.8);
        }

        .activity-sidebar-card .badge-secondary {
            background: rgba(255, 255, 255, 0.35);
        }

        .activity-sidebar-card .text-sm,
        .activity-sidebar-card small,
        .activity-sidebar-card .media-body {
            color: rgba(255, 255, 255, 0.85) !important;
        }

        .activity-sidebar-card .media-list li:last-child {
            border-bottom: none;
        }
    </style>
@endpush

@section('content')
    @php($onlineCount = $onlineUsers->count())
    @php($offlineCount = $offlineUsers->count())

    <div class="row">
        <div class="col-md-4">
            <div class="card activity-sidebar-card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title"><i class="icon-meter-fast mr-2"></i> Live Usage</h6>
                    {!! Qs::getPanelOptions() !!}
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <div class="font-size-sm text-muted">Currently Online</div>
                            <h3 class="mb-0 text-success">{{ $onlineCount }}</h3>
                        </div>
                        <div class="text-right">
                            <div class="font-size-sm text-muted">Offline</div>
                            <h3 class="mb-0 text-muted">{{ $offlineCount }}</h3>
                        </div>
                    </div>

                    <div class="font-size-sm text-muted mb-2">Online Users</div>
                    <ul class="media-list media-list-bordered" style="max-height: 220px; overflow-y: auto;">
                        @forelse($onlineUsers as $user)
                            <li class="media align-items-center py-2">
                                <div class="mr-3">
                                    <span class="badge badge-pill badge-success">●</span>
                                </div>
                                <div class="media-body">
                                    <div class="font-weight-semibold">{{ $user->name }}</div>
                                    <div class="text-muted text-sm">{{ ucfirst($user->user_type) }} • Active now</div>
                                </div>
                            </li>
                        @empty
                            <li class="media py-2 text-muted">No users online right now.</li>
                        @endforelse
                    </ul>

                    <div class="font-size-sm text-muted mt-3 mb-2">Recently Offline</div>
                    <ul class="media-list media-list-bordered" style="max-height: 160px; overflow-y: auto;">
                        @forelse($offlineUsers->take(10) as $user)
                            <li class="media align-items-center py-2">
                                <div class="mr-3">
                                    <span class="badge badge-pill badge-secondary">●</span>
                                </div>
                                <div class="media-body">
                                    <div class="font-weight-semibold">{{ $user->name }}</div>
                                    <div class="text-muted text-sm">
                                        Last seen {{ optional($user->last_seen_at)->diffForHumans() ?? '—' }}
                                    </div>
                                </div>
                            </li>
                        @empty
                            <li class="media py-2 text-muted">No history available.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title"><i class="icon-history mr-2"></i> Activity Filters</h6>
                </div>
                <div class="card-body">
                    <form method="get" class="form-inline flex-column flex-md-row">
                        <div class="form-group mb-2 mr-md-2 w-100">
                            <label class="mr-2 mb-0">User</label>
                            <select name="user_id" class="form-control select" data-placeholder="Any" style="width:100%">
                                <option value="">All Users</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ (int) ($filters['user_id'] ?? 0) === $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ ucfirst($user->user_type) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mb-2 mr-md-2">
                            <label class="mr-2 mb-0">Method</label>
                            <select name="method" class="form-control">
                                <option value="">Any</option>
                                @foreach(['POST','PUT','PATCH','DELETE'] as $method)
                                    <option value="{{ $method }}" {{ ($filters['method'] ?? '') === $method ? 'selected' : '' }}>{{ $method }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mb-2 mr-md-2">
                            <label class="mr-2 mb-0">From</label>
                            <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="form-control">
                        </div>
                        <div class="form-group mb-2 mr-md-2">
                            <label class="mr-2 mb-0">To</label>
                            <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="form-control">
                        </div>
                        <div class="form-group mb-2 mr-md-2 flex-fill">
                            <label class="mr-2 mb-0">Search</label>
                            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control w-100" placeholder="Action, route, description">
                        </div>
                        <div class="form-group mb-2">
                            <button type="submit" class="btn btn-primary">Apply</button>
                            <a href="{{ route('activity.logs.index') }}" class="btn btn-light ml-2">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title"><i class="icon-list-unordered mr-2"></i> Recent Activity</h6>
                    {!! Qs::getPanelOptions() !!}
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th>User</th>
                                <th>Action</th>
                                <th>Method</th>
                                <th>Status</th>
                                <th>When</th>
                                <th>Details</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($logs as $log)
                                <tr>
                                    <td>{{ $loop->iteration + ($logs->currentPage() - 1) * $logs->perPage() }}</td>
                                    <td>
                                        <div class="font-weight-semibold">{{ optional($log->user)->name ?? 'System' }}</div>
                                        <small class="text-muted">{{ optional($log->user)->user_type ? ucfirst(optional($log->user)->user_type) : '—' }}</small>
                                    </td>
                                    <td>
                                        <div class="font-weight-semibold">{{ $log->action }}</div>
                                        <small class="text-muted">
                                            {{ $log->description ?: $log->route ?: $log->url }}
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge badge-flat border-{{ $log->method === 'DELETE' ? 'danger' : ($log->method === 'POST' ? 'primary' : 'warning') }} text-{{ $log->method === 'DELETE' ? 'danger' : ($log->method === 'POST' ? 'primary' : 'warning') }}">
                                            {{ $log->method }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ ($log->status_code ?? 200) >= 400 ? 'danger' : 'success' }}">
                                            {{ $log->status_code ?? '—' }}
                                        </span>
                                    </td>
                                    <td>{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                                    <td>
                                        @if($log->changes)
                                            <details>
                                                <summary class="text-muted">Payload</summary>
                                                <pre class="mb-0">{{ json_encode($log->changes, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                            </details>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">No activity recorded yet.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer text-right">
                    {{ $logs->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
