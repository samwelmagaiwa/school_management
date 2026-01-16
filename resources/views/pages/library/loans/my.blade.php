@extends('layouts.master')
@section('page_title', 'My Loans')
@section('content')

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title"><i class="icon-books mr-2"></i> My Borrowed Books</h6>
            {!! Qs::getPanelOptions() !!}
        </div>

        <div class="card-body">
            <div class="alert alert-info">
                Please visit the library counter to return books or address fines. Requests are processed by librarians only.
            </div>
            <div class="table-responsive">
                <table class="table datatable-button-html5-columns">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Book</th>
                        <th>Copy</th>
                        <th>Borrowed / Due</th>
                        <th>Status</th>
                        <th>Fine</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($loans as $loan)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ optional($loan->copy->book)->name }}</td>
                            <td>{{ optional($loan->copy)->copy_code }}</td>
                            <td>
                                <div>{{ $loan->borrowed_at ? \Illuminate\Support\Carbon::parse($loan->borrowed_at)->format('Y-m-d H:i') : '' }}</div>
                                <small class="text-muted">Due {{ $loan->due_at ? \Illuminate\Support\Carbon::parse($loan->due_at)->format('Y-m-d') : '—' }}</small>
                            </td>
                            <td>
                                @if($loan->returned_at)
                                    <span class="badge badge-success">Returned</span>
                                @elseif($loan->is_overdue)
                                    <span class="badge badge-danger">Overdue ({{ $loan->days_overdue }} days)</span>
                                @else
                                    <span class="badge badge-primary">Active</span>
                                @endif
                            </td>
                            <td>{{ number_format($loan->fine_amount, 2) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
