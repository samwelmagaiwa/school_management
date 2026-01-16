@extends('layouts.master')
@section('page_title', 'Children Loans')
@section('content')

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title"><i class="icon-library2 mr-2"></i> Children Loans</h6>
            {!! Qs::getPanelOptions() !!}
        </div>

        <div class="card-body">
            @if($loans->isEmpty())
                <div class="alert alert-info">None of your children currently have any recorded library loans.</div>
            @endif

            <div class="table-responsive">
                <table class="table datatable-button-html5-columns">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Child</th>
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
                            <td>{{ optional($loan->user)->name }}</td>
                            <td>{{ optional($loan->copy->book)->name }}</td>
                            <td>{{ optional($loan->copy)->copy_code }}</td>
                            <td>
                                <div>{{ $loan->borrowed_at ? \Illuminate\Support\Carbon::parse($loan->borrowed_at)->format('Y-m-d H:i') : '' }}</div>
                                <small class="text-muted">
                                    Due {{ $loan->due_at ? \Illuminate\Support\Carbon::parse($loan->due_at)->format('Y-m-d') : '—' }}
                                </small>
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
                            <td>
                                @if($loan->fine_amount > 0)
                                    <span class="badge badge-danger">{{ number_format($loan->fine_amount, 2) }}</span>
                                @else
                                    <span class="text-muted">{{ number_format($loan->fine_amount, 2) }}</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-3 text-muted small">
                <p>
                    Any overdue loans or outstanding fines shown here will prevent your child from borrowing new books
                    until they are resolved at the library.
                </p>
            </div>
        </div>
    </div>
@endsection
