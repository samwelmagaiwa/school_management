@extends('layouts.master')
@section('page_title', $overdueOnly ? 'Overdue Loans' : 'Active Loans')
@section('content')

    @php($currentUser = Auth::user())
    @php($isAdmin = $currentUser && in_array($currentUser->user_type, ['admin','super_admin']))
    @php($isSuper = $currentUser && $currentUser->user_type === 'super_admin')

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title"><i class="icon-library2 mr-2"></i> {{ $overdueOnly ? 'Overdue Loans' : 'Active Loans' }}</h6>
            {!! Qs::getPanelOptions() !!}
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table datatable-button-html5-columns">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Book</th>
                        <th>Borrower</th>
                        <th>Borrowed / Due</th>
                        <th>Status</th>
                        <th>Fine</th>
                        <th>Processed By</th>
                        <th class="text-center">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($loans as $loan)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <strong>{{ optional($loan->copy->book)->name }}</strong>
                                <div class="text-muted">{{ optional($loan->copy)->copy_code }}</div>
                            </td>
                            <td>{{ optional($loan->user)->name }}</td>
                            <td>
                                <div>{{ $loan->borrowed_at ? \Illuminate\Support\Carbon::parse($loan->borrowed_at)->format('Y-m-d H:i') : '' }}</div>
                                <small class="text-muted">Due {{ $loan->due_at ? \Illuminate\Support\Carbon::parse($loan->due_at)->format('Y-m-d') : '—' }}</small>
                            </td>
                            <td>
                                @if($loan->returned_at)
                                    <span class="badge badge-success">Returned</span>
                                @elseif($loan->is_overdue)
                                    <span class="badge badge-danger">Overdue ({{ $loan->days_overdue }}d)</span>
                                @else
                                    <span class="badge badge-primary">Active</span>
                                @endif
                                @if($loan->has_override)
                                    <span class="badge badge-warning">Override</span>
                                @endif
                            </td>
                            <td>{{ number_format($loan->fine_amount, 2) }}</td>
                            <td>{{ optional($loan->processedBy)->name }}</td>
                            <td class="text-center">
                                <a href="{{ route('library.loans.show', $loan->id) }}" class="btn btn-sm btn-outline-secondary">Details</a>

                                {{-- Mark Returned --}}
                                <form method="post" action="{{ route('library.loans.return', $loan->id) }}" class="d-inline loan-action-form" data-loan-action="return">
                                    @csrf
                                    <select name="mark_status" class="custom-select custom-select-sm w-auto d-inline-block">
                                        <option value="available">Available</option>
                                        <option value="damaged">Damaged</option>
                                        <option value="lost">Lost</option>
                                    </select>
                                    <button type="button" class="btn btn-sm btn-primary" onclick="confirmLoanAction(this, 'return')">Mark Returned</button>
                                </form>

                                {{-- Waive Fine --}}
                                @if($loan->fine_amount > 0 && $isAdmin)
                                    <form method="post" action="{{ route('library.loans.waive', $loan->id) }}" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="reason" value="Fine waived via dashboard" />
                                        <button type="submit" class="btn btn-sm btn-warning">Waive Fine</button>
                                    </form>
                                @endif

                                {{-- Force Close --}}
                                @if($isAdmin)
                                    <form method="post" action="{{ route('library.loans.force_close', $loan->id) }}" class="d-inline loan-action-form" data-loan-action="force_close">
                                        @csrf
                                        <input type="hidden" name="reason" value="Force closed via dashboard" />
                                        <button type="button" class="btn btn-sm btn-info" onclick="confirmLoanAction(this, 'force_close')">Force Close</button>
                                    </form>
                                @endif

                                {{-- Reverse --}}
                                @if($isSuper)
                                    <form method="post" action="{{ route('library.loans.reverse', $loan->id) }}" class="d-inline loan-action-form" data-loan-action="reverse">
                                        @csrf
                                        <input type="hidden" name="reason" value="Reversed via dashboard" />
                                        <button type="button" class="btn btn-sm btn-danger" onclick="confirmLoanAction(this, 'reverse')">Reverse</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
