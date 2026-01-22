@extends('layouts.master')
@section('page_title', 'Librarian Dashboard')
@section('content')

    <style>
        .mini-stat {
            border-radius: .5rem;
            border: 1px solid rgba(0,0,0,.05);
            box-shadow: 0 8px 14px rgba(0,0,0,.03);
            transition: all .2s ease;
        }
        .mini-stat .card-body {
            padding: 1.25rem 1.5rem;
            min-height: 110px;
        }
        .mini-stat .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: rgba(0,0,0,.04);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            color: inherit;
        }
        .mini-stat h3 {
            font-size: 1.7rem;
        }
        .mini-stat:hover {
            transform: translateY(-3px);
            box-shadow: 0 18px 30px rgba(0,0,0,.08);
        }
        .border-left-primary { border-left: 4px solid #2196f3; }
        .border-left-danger { border-left: 4px solid #ef5350; }
        .border-left-success { border-left: 4px solid #66bb6a; }
        .border-left-warning { border-left: 4px solid #ffb300; }
        .bg-soft-primary { background: rgba(33,150,243,.08); }
        .bg-soft-danger { background: rgba(239,83,80,.08); }
        .bg-soft-success { background: rgba(102,187,106,.08); }
        .bg-soft-warning { background: rgba(255,179,0,.08); }
        .text-primary { color: #1565c0 !important; }
        .text-danger { color: #c62828 !important; }
        .text-success { color: #2e7d32 !important; }
        .text-warning { color: #ef6c00 !important; }
    </style>

<div class="row">
    @php
        $stats = [
            ['label' => 'Total Books', 'count' => $total_books, 'icon' => 'icon-book', 'accent' => 'border-left-primary bg-soft-primary text-primary'],
            ['label' => 'Total Copies', 'count' => $total_copies, 'icon' => 'icon-books', 'accent' => 'border-left-success bg-soft-success text-success'],
            ['label' => 'Active Loans', 'count' => $active_loans, 'icon' => 'icon-reading', 'accent' => 'border-left-warning bg-soft-warning text-warning'],
            ['label' => 'Overdue Loans', 'count' => $overdue_loans, 'icon' => 'icon-alarm', 'accent' => 'border-left-danger bg-soft-danger text-danger'],
        ];
    @endphp

    @foreach($stats as $stat)
        <div class="col-sm-6 col-md-3">
             <div class="card mini-stat {{ $stat['accent'] }}">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted text-uppercase font-size-xs mb-1">{{ $stat['label'] }}</p>
                        <h3 class="font-weight-semibold mb-0">{{ $stat['count'] }}</h3>
                    </div>
                    <div class="stat-icon">
                        <i class="{{ $stat['icon'] }}"></i>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header header-elements-inline">
                <h5 class="card-title">Recent Book Loans</h5>
                {!! Qs::getPanelOptions() !!}
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Book Title</th>
                                <th>Loan Date</th>
                                <th>Due Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recent_loans as $loan)
                                <tr>
                                    <td>{{ $loan->user_name }}</td>
                                    <td>{{ $loan->title }}</td>
                                    <td>{{ date('d M Y', strtotime($loan->loan_date)) }}</td>
                                    <td>{{ date('d M Y', strtotime($loan->due_date)) }}</td>
                                    <td>
                                        <span class="badge {{ $loan->status == 'active' ? 'badge-success' : ($loan->status == 'overdue' ? 'badge-danger' : 'badge-secondary') }}">
                                            {{ ucfirst($loan->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No recent loans</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
         <div class="card">
            <div class="card-header header-elements-inline">
                <h6 class="card-title">Quick Actions</h6>
            </div>
            <div class="card-body">
                <a href="{{ route('library.books.index') }}" class="btn btn-primary btn-block mb-3">
                    <i class="icon-books mr-2"></i> Manage Books
                </a>
                <a href="{{ route('library.loans.index') }}" class="btn btn-success btn-block mb-3">
                    <i class="icon-reading mr-2"></i> Manage Loans
                </a>
                <a href="{{ route('library.requests.index') }}" class="btn btn-info btn-block mb-3">
                    <i class="icon-file-text2 mr-2"></i> Book Requests
                    @if($pending_requests > 0)
                        <span class="badge badge-pill badge-danger ml-2">{{ $pending_requests }}</span>
                    @endif
                </a>
            </div>
        </div>
    </div>
</div>

    {{--Events Calendar Begins--}}
    <div class="card mt-4">
        <div class="card-header header-elements-inline">
            <h5 class="card-title">School Events Calendar</h5>
         {!! Qs::getPanelOptions() !!}
        </div>

        <div class="card-body">
            <div class="fullcalendar-basic"></div>
        </div>
    </div>
    {{--Events Calendar Ends--}}

@endsection
