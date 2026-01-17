@extends('layouts.master')
@php use Illuminate\Support\Str; @endphp
@section('page_title', 'Financial Reports')
@section('content')

<form method="get" action="{{ route('accounting.reports.index') }}" class="card mb-3">
    <div class="card-header header-elements-inline">
        <h6 class="card-title">Drill-down Filters</h6>
        {!! Qs::getPanelOptions() !!}
    </div>
    <div class="card-body">
        <div class="form-row">
            <div class="col-md-3">
                <label class="font-weight-semibold">Class</label>
                <select name="class_id" class="form-control select-search" data-placeholder="All Classes">
                    <option value="">All</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ (int)$classFilter === (int)$class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="font-weight-semibold">Student (name)</label>
                <input type="text" name="student_search" value="{{ $studentSearch }}" class="form-control" placeholder="Search student">
            </div>
            <div class="col-md-2">
                <label class="font-weight-semibold">Cashbook From</label>
                <input type="date" name="cashbook_start" value="{{ $cashbookStart }}" class="form-control">
            </div>
            <div class="col-md-2">
                <label class="font-weight-semibold">Cashbook To</label>
                <input type="date" name="cashbook_end" value="{{ $cashbookEnd }}" class="form-control">
            </div>
            <div class="col-md-2">
                <label class="d-block font-weight-semibold">&nbsp;</label>
                <button class="btn btn-primary btn-block" type="submit">Apply Filters <i class="icon-filter3 ml-2"></i></button>
            </div>
        </div>
    </div>
</form>

<div class="row">
    <div class="col-md-3">
        <div class="card card-body bg-primary text-white mb-3">
            <h6 class="mb-1">Total Invoiced</h6>
            <h4 class="mb-0">{{ Qs::formatCurrency($feeSummary['total_invoiced']) }}</h4>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-body bg-success text-white mb-3">
            <h6 class="mb-1">Total Collected</h6>
            <h4 class="mb-0">{{ Qs::formatCurrency($feeSummary['total_paid']) }}</h4>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-body bg-warning text-white mb-3">
            <h6 class="mb-1">Outstanding</h6>
            <h4 class="mb-0">{{ Qs::formatCurrency($feeSummary['outstanding']) }}</h4>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-body bg-info text-white mb-3">
            <h6 class="mb-1">Collection Rate</h6>
            <h4 class="mb-0">{{ $feeSummary['collection_rate'] }}%</h4>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-7">
        <div class="card border-left-primary">
            <div class="card-header bg-transparent">
                <h6 class="card-title">Financial Period Controls</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                        <tr>
                            <th>Name</th>
                            <th>Start</th>
                            <th>End</th>
                            <th>Status</th>
                            <th class="text-right">Controls</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($periods as $period)
                            <tr>
                                <td>{{ $period->name }}</td>
                                <td>{{ optional($period->start_date)->format('Y-m-d') }}</td>
                                <td>{{ optional($period->end_date)->format('Y-m-d') }}</td>
                                <td>
                                    <span class="badge badge-{{ $period->is_locked ? 'danger' : 'success' }}">{{ $period->is_locked ? 'Locked' : 'Open' }}</span>
                                </td>
                                <td class="text-right">
                                    @if($period->is_locked)
                                        @can('unlockPeriod', $period)
                                            <form class="d-inline-block" method="post" action="{{ route('accounting.periods.unlock', $period) }}">
                                                @csrf
                                                <input type="hidden" name="reason" value="Manual unlock from dashboard">
                                                <button type="submit" class="btn btn-sm btn-outline-warning">Unlock</button>
                                            </form>
                                        @else
                                            <span class="text-muted">Awaiting approval</span>
                                        @endcan
                                    @else
                                        @can('manageLocks', $period)
                                            <form class="d-inline-block" method="post" action="{{ route('accounting.periods.lock', $period) }}">
                                                @csrf
                                                <input type="hidden" name="reason" value="Period closed via dashboard">
                                                <button type="submit" class="btn btn-sm btn-outline-primary">Lock Period</button>
                                            </form>
                                        @else
                                            <span class="text-muted">No permission</span>
                                        @endcan
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="card border-left-info">
            <div class="card-header bg-transparent">
                <h6 class="card-title">Audit Trail (last 15 events)</h6>
            </div>
            <div class="card-body">
                <ul class="media-list">
                    @forelse($auditLogs as $log)
                        <li class="media">
                            <div class="mr-3">
                                <span class="badge badge-flat border-primary text-primary">{{ $log->event }}</span>
                            </div>
                            <div class="media-body">
                                <div class="font-weight-semibold">{{ optional($log->user)->name ?? 'System' }}</div>
                                <span class="text-muted small">{{ $log->created_at->format('Y-m-d H:i') }} &bullet; {{ Str::limit($log->description, 80) }}</span>
                            </div>
                        </li>
                    @empty
                        <li class="text-muted">No audit activity recorded yet.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>

@php
    $csvUrl = fn($type) => request()->fullUrlWithQuery(array_merge(request()->query(), ['export' => $type]));
@endphp

@component('pages.accountant.reports.partials.section', [
    'title' => 'Fee Collection Summary & Cashbook',
    'tableId' => 'tbl-cash-daily',
    'csv' => $csvUrl('cashbook-daily')
])
    <div class="row">
        <div class="col-md-6">
            <h6>Daily Cashbook</h6>
            <table class="table datatable-button-html5-columns" id="tbl-cash-daily">
                <thead>
                <tr>
                    <th>Date</th>
                    <th>Total</th>
                </tr>
                </thead>
                <tbody>
                @forelse($cashbookDaily as $row)
                    <tr>
                        <td>{{ $row->label }}</td>
                        <td>{{ Qs::formatCurrency($row->total) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="2" class="text-muted">No payments recorded.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="col-md-6">
            <h6>Monthly Cashbook</h6>
            <table class="table datatable-button-html5-columns" id="tbl-cash-monthly">
                <thead>
                <tr>
                    <th>Month</th>
                    <th>Total</th>
                </tr>
                </thead>
                <tbody>
                @forelse($cashbookMonthly as $row)
                    <tr>
                        <td>{{ $row->label }}</td>
                        <td>{{ Qs::formatCurrency($row->total) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="2" class="text-muted">No data available.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endcomponent

@component('pages.accountant.reports.partials.section', [
    'title' => 'Outstanding Balances',
    'tableId' => 'tbl-outstanding-class',
    'csv' => $csvUrl('outstanding-class')
])
    <div class="row">
        <div class="col-md-6">
            <h6>By Class</h6>
            <table class="table datatable-button-html5-columns" id="tbl-outstanding-class">
                <thead>
                <tr>
                    <th>Class</th>
                    <th>Outstanding</th>
                </tr>
                </thead>
                <tbody>
                @forelse($outstandingByClass as $row)
                    <tr>
                        <td>{{ $row['class'] }}</td>
                        <td>{{ Qs::formatCurrency($row['outstanding']) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="2" class="text-muted">No outstanding balances.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="col-md-6">
            <h6>Top Students</h6>
            <table class="table datatable-button-html5-columns" id="tbl-outstanding-student">
                <thead>
                <tr>
                    <th>Student</th>
                    <th>Outstanding</th>
                    <th>Paid</th>
                </tr>
                </thead>
                <tbody>
                @forelse($outstandingByStudent as $row)
                    <tr>
                        <td>{{ $row['student'] }}</td>
                        <td>{{ Qs::formatCurrency($row['outstanding']) }}</td>
                        <td>{{ Qs::formatCurrency($row['paid']) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-muted">No outstanding balances.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endcomponent

@component('pages.accountant.reports.partials.section', [
    'title' => 'Income vs Expenses',
    'tableId' => 'tbl-income',
    'csv' => $csvUrl('income-expenses')
])
    <table class="table datatable-button-html5-columns" id="tbl-income">
        <thead>
        <tr>
            <th>Metric</th>
            <th>Amount</th>
        </tr>
        </thead>
        <tbody>
        <tr><td>Fee Income</td><td>{{ Qs::formatCurrency($incomeVsExpenses['fee_income']) }}</td></tr>
        <tr><td>Other Income</td><td>{{ Qs::formatCurrency($incomeVsExpenses['other_income']) }}</td></tr>
        <tr><td>Total Income</td><td>{{ Qs::formatCurrency($incomeVsExpenses['total_income']) }}</td></tr>
        <tr><td>Expenses</td><td>{{ Qs::formatCurrency($incomeVsExpenses['expenses']) }}</td></tr>
        <tr><td>Net</td><td>{{ Qs::formatCurrency($incomeVsExpenses['net']) }}</td></tr>
        </tbody>
    </table>
@endcomponent

@component('pages.accountant.reports.partials.section', [
    'title' => 'Arrears Aging',
    'tableId' => 'tbl-aging',
    'csv' => $csvUrl('arrears-aging')
])
    <table class="table datatable-button-html5-columns" id="tbl-aging">
        <thead>
        <tr>
            <th>Bucket</th>
            <th>Amount</th>
        </tr>
        </thead>
        <tbody>
        @foreach($arrearsAging as $bucket => $amount)
            <tr>
                <td>{{ $bucket }} days</td>
                <td>{{ Qs::formatCurrency($amount) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endcomponent

@component('pages.accountant.reports.partials.section', [
    'title' => 'Student Statements',
    'tableId' => 'tbl-statements',
    'csv' => $csvUrl('student-statements')
])
    <table class="table datatable-button-html5-columns" id="tbl-statements">
        <thead>
        <tr>
            <th>Student ID</th>
            <th>Student</th>
            <th>Invoiced</th>
            <th>Paid</th>
            <th>Balance</th>
        </tr>
        </thead>
        <tbody>
        @forelse($studentStatements as $row)
            <tr>
                <td>{{ $row->student_id }}</td>
                <td>{{ $row->student_name }}</td>
                <td>{{ Qs::formatCurrency($row->invoiced) }}</td>
                <td>{{ Qs::formatCurrency($row->paid) }}</td>
                <td>{{ Qs::formatCurrency($row->balance) }}</td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-muted">No records.</td></tr>
        @endforelse
        </tbody>
    </table>
@endcomponent

@component('pages.accountant.reports.partials.section', [
    'title' => 'Recent Payments',
    'tableId' => 'tbl-payments',
    'csv' => $csvUrl('payment-history')
])
    <table class="table datatable-button-html5-columns" id="tbl-payments">
        <thead>
        <tr>
            <th>Receipt #</th>
            <th>Student</th>
            <th>Amount</th>
            <th>Method</th>
            <th>Received At</th>
        </tr>
        </thead>
        <tbody>
        @forelse($paymentHistory as $payment)
            <tr>
                <td>{{ $payment->receipt_number }}</td>
                <td>{{ optional($payment->student)->name }}</td>
                <td>{{ Qs::formatCurrency($payment->amount) }}</td>
                <td>{{ strtoupper($payment->method) }}</td>
                <td>{{ optional($payment->received_at)->format('Y-m-d H:i') }}</td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-muted">No recent payments.</td></tr>
        @endforelse
        </tbody>
    </table>
@endcomponent

@component('pages.accountant.reports.partials.section', [
    'title' => 'Balance Forward',
    'tableId' => 'tbl-balance',
    'csv' => $csvUrl('balance-forward')
])
    <table class="table datatable-button-html5-columns" id="tbl-balance">
        <thead>
        <tr>
            <th>Student ID</th>
            <th>Student</th>
            <th>Outstanding</th>
        </tr>
        </thead>
        <tbody>
        @forelse($balanceForward as $row)
            <tr>
                <td>{{ $row->student_id }}</td>
                <td>{{ $row->student_name }}</td>
                <td>{{ Qs::formatCurrency($row->outstanding) }}</td>
            </tr>
        @empty
            <tr><td colspan="3" class="text-muted">No balances.</td></tr>
        @endforelse
        </tbody>
    </table>
@endcomponent

@component('pages.accountant.reports.partials.section', [
    'title' => 'Installments by Student',
    'tableId' => 'tbl-installments-student',
    'csv' => $csvUrl('installments-student')
])
    <table class="table datatable-button-html5-columns" id="tbl-installments-student">
        <thead>
        <tr>
            <th>Student ID</th>
            <th>Student</th>
            <th>Scheduled</th>
            <th>Paid</th>
            <th>Outstanding</th>
            <th>Overdue</th>
        </tr>
        </thead>
        <tbody>
        @forelse($installmentsByStudent as $row)
            <tr>
                <td>{{ $row->student_id }}</td>
                <td>{{ $row->student_name }}</td>
                <td>{{ Qs::formatCurrency($row->scheduled) }}</td>
                <td>{{ Qs::formatCurrency($row->paid) }}</td>
                <td>{{ Qs::formatCurrency($row->balance) }}</td>
                <td>{{ Qs::formatCurrency($row->overdue) }}</td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-muted">No installment data.</td></tr>
        @endforelse
        </tbody>
    </table>
@endcomponent
@endsection

@push('scripts')
<script>
    $(document).on('click', '.js-export-trigger', function (e) {
        e.preventDefault();
        var target = $(this).data('target');
        var action = $(this).data('action');
        var table = $(target).DataTable();
        if (table && table.button('.buttons-' + action).length) {
            table.button('.buttons-' + action).trigger();
        }
    });
</script>
@endpush
