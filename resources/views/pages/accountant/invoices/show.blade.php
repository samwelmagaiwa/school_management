@extends('layouts.master')
@php use App\Helpers\Qs; @endphp
@section('page_title', 'Student Account')
@section('content')

@php
    $totalInstallments = $installments->sum('amount');
    $totalPaid = $installments->sum('amount_paid');
    $totalBalance = $totalInstallments - $totalPaid;
    $openInstallments = $installments->whereIn('status', ['pending', 'partial', 'overdue']);
@endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h5 class="mb-1">{{ $student->user?->name }}</h5>
        <p class="text-muted mb-0">
            Adm No: {{ $student->adm_no }} &middot;
            Class: {{ optional($student->my_class)->name ?? 'N/A' }}
        </p>
    </div>
    <div>
        <a href="{{ route('accounting.invoices.index') }}" class="btn btn-light"><i class="icon-arrow-left13 mr-2"></i>Back to Invoices</a>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card card-body bg-primary text-white">
            <h6 class="mb-1">Total Scheduled</h6>
            <h4 class="mb-0">{{ Qs::formatCurrency($totalInstallments) }}</h4>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-body bg-success text-white">
            <h6 class="mb-1">Total Paid</h6>
            <h4 class="mb-0">{{ Qs::formatCurrency($totalPaid) }}</h4>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-body bg-warning text-white">
            <h6 class="mb-1">Balance</h6>
            <h4 class="mb-0">{{ Qs::formatCurrency($totalBalance) }}</h4>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header header-elements-inline">
        <h6 class="card-title">Installment Schedule</h6>
        {!! Qs::getPanelOptions() !!}
    </div>
    <div class="card-body">
        <div class="alert alert-info">Installment definitions are read-only for accountants. Please contact Admin/Super Admin for plan changes.</div>
        <table class="table table-sm">
            <thead>
            <tr>
                <th>Installment</th>
                <th>Amount</th>
                <th>Due Date</th>
                <th>Paid</th>
                <th>Balance</th>
                <th>Status</th>
            </tr>
            </thead>
            <tbody>
            @forelse($installments as $installment)
                @php
                    $balance = $installment->amount - $installment->amount_paid;
                    $label = $installment->installmentDefinition?->label ?? ('Installment '.$loop->iteration);
                @endphp
                <tr>
                    <td>{{ $label }}</td>
                    <td>{{ Qs::formatCurrency($installment->amount) }}</td>
                    <td>{{ optional($installment->due_date)->format('Y-m-d') ?? '—' }}</td>
                    <td>{{ Qs::formatCurrency($installment->amount_paid) }}</td>
                    <td>{{ Qs::formatCurrency($balance) }}</td>
                    <td>
                        <span class="badge badge-{{ $installment->status === 'paid' ? 'success' : ($installment->status === 'partial' ? 'warning' : ($installment->status === 'overdue' ? 'danger' : 'primary')) }}">
                            {{ ucfirst($installment->status) }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-muted">No installments generated for this student.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="row">
    <div class="col-md-5">
        <div class="card">
            <div class="card-header header-elements-inline">
                <h6 class="card-title">Record Payment</h6>
                {!! Qs::getPanelOptions() !!}
            </div>
            <div class="card-body">
                <form method="post" action="{{ route('accounting.students.account.payments', $student->id) }}">
                    @csrf
                    <div class="form-group">
                        <label>Amount</label>
                        <input type="number" name="amount" step="0.01" min="0.01" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Payment Method</label>
                        <select name="method" class="form-control" required>
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="mobile_money">Mobile Money</option>
                            <option value="cheque">Cheque</option>
                        </select>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Reference</label>
                            <input type="text" name="reference" class="form-control" placeholder="optional">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Received At</label>
                            <input type="datetime-local" name="received_at" class="form-control">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Allocation Strategy</label>
                        <select name="allocation_strategy" class="form-control">
                            <option value="oldest">Oldest Unpaid Installment</option>
                            <option value="specific">Specific Installment</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Installment (if specific)</label>
                        <select name="student_installment_id" class="form-control select-search">
                            <option value="">Select installment</option>
                            @foreach($openInstallments as $installment)
                                <option value="{{ $installment->id }}">
                                    {{ ($installment->installmentDefinition?->label ?? 'Installment '.$loop->iteration) }} - Due {{ optional($installment->due_date)->format('Y-m-d') ?? '—' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Notes</label>
                        <textarea name="notes" rows="3" class="form-control"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Record Payment</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card">
            <div class="card-header header-elements-inline">
                <h6 class="card-title">Payment History</h6>
                {!! Qs::getPanelOptions() !!}
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <thead>
                    <tr>
                        <th>Receipt</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Received</th>
                        <th>Status</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($payments as $payment)
                        <tr>
                            <td>{{ $payment->receipt_number }}</td>
                            <td>{{ Qs::formatCurrency($payment->amount) }}</td>
                            <td>{{ strtoupper($payment->method) }}</td>
                            <td>{{ optional($payment->received_at)->format('Y-m-d H:i') }}</td>
                            <td>{{ ucfirst($payment->status) }}</td>
                        </tr>
                        @if($payment->allocations->count())
                            <tr class="text-muted">
                                <td colspan="5">
                                    <strong>Allocations:</strong>
                                    <ul class="mb-0">
                                        @foreach($payment->allocations as $allocation)
                                            <li>
                                                {{ optional($allocation->invoice)->invoice_number }} &mdash; {{ Qs::formatCurrency($allocation->amount_applied) }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr><td colspan="5" class="text-muted">No payments recorded.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

