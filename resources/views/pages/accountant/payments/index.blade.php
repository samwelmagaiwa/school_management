@extends('layouts.master')
@section('page_title', 'Payments & Receipts')
@section('content')

<div class="card">
    <div class="card-header header-elements-inline">
        <h6 class="card-title">Payments & Receipts</h6>
        <div class="header-elements">
            <div class="list-icons">
                @if($canRecordPayments)
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal-record-payment">
                        <i class="icon-cash3 mr-2"></i> Record Payment
                    </button>
                @endif
            </div>
            {!! Qs::getPanelOptions() !!}
        </div>
    </div>
    <div class="card-body">
        <div class="alert alert-warning border-0">
            <span class="font-weight-semibold">No Deletions:</span> Once a receipt is reconciled, use reversal workflows instead of deleting financial history.
        </div>
        <div class="table-responsive">
            <table class="table datatable-button-html5-columns">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Receipt #</th>
                    <th>Student</th>
                    <th>Amount</th>
                    <th>Method</th>
                    <th>Received At</th>
                    <th>Status</th>
                    <th>Recorded By</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach($payments as $payment)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $payment->receipt_number }}</td>
                        <td>{{ optional($payment->student)->name }}</td>
                        <td>{{ Qs::formatCurrency($payment->amount) }}</td>
                        <td>{{ strtoupper($payment->method) }}</td>
                        <td>{{ optional($payment->received_at)->format('Y-m-d H:i') }}</td>
                        <td>
                            <span class="badge badge-{{ $payment->status === 'refunded' ? 'danger' : ($payment->status === 'allocated' ? 'success' : 'primary') }}">{{ ucfirst($payment->status) }}</span>
                        </td>
                        <td>{{ optional($payment->recorder)->name }}</td>
                        <td>
                            @can('reversePayment', $payment)
                                <button class="btn btn-link text-danger p-0" data-toggle="modal" data-target="#reverse-{{ $payment->id }}" {{ $payment->status === 'refunded' ? 'disabled' : '' }}>
                                    <i class="icon-undo"></i> Reverse
                                </button>
                            @else
                                <span class="text-muted">None</span>
                            @endcan
                        </td>
                    </tr>

                    @can('reversePayment', $payment)
                        <div class="modal fade" id="reverse-{{ $payment->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <form method="post" action="{{ route('accounting.payments.reverse', $payment) }}">
                                        @csrf
                                        <div class="modal-header bg-danger-400">
                                            <h6 class="modal-title">Reverse Payment {{ $payment->receipt_number }}</h6>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <p>This marks the receipt as refunded and rolls back allocations. Provide a justification for the audit log.</p>
                                            <div class="form-group">
                                                <label>Reason</label>
                                                <textarea class="form-control" name="reason" rows="3" required></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-danger">Reverse Receipt</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endcan
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $payments->links() }}
        </div>
    </div>
</div>

@if($canRecordPayments)
    <div class="modal fade" id="modal-record-payment" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="post" action="{{ route('accounting.payments.store') }}">
                    @csrf
                    <div class="modal-header bg-primary text-white">
                        <h6 class="modal-title">Record Payment</h6>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Student (optional)</label>
                            <select name="student_id" class="form-control select-search">
                                <option value="">Walk-in / non-student</option>
                                @foreach($students as $student)
                                    <option value="{{ $student->id }}">{{ $student->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Amount</label>
                            <input type="number" class="form-control" name="amount" step="0.01" min="0.01" required>
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
                        <div class="form-group">
                            <label>Apply to Installment (optional)</label>
                            <select name="student_installment_id" class="form-control select-search">
                                <option value="">-- None (leave unallocated) --</option>
                                @foreach($installments as $inst)
                                    <option value="{{ $inst->id }}">
                                        {{ optional($inst->student)->name ?? 'N/A' }} -
                                        {{ optional($inst->invoice)->invoice_number ?? 'Invoice ?' }} -
                                        {{ optional($inst->installmentDefinition)->label ?? 'Installment' }}
                                        (Due: {{ optional($inst->due_date)->format('Y-m-d') ?? 'n/a' }},
                                        Bal: {{ Qs::formatCurrency($inst->amount - $inst->amount_paid) }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">If set, this receipt will be allocated to the selected installment first.</small>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Reference</label>
                                <input type="text" class="form-control" name="reference">
                            </div>
                            <div class="form-group col-md-6">
                                <label>Received At</label>
                                <input type="datetime-local" class="form-control" name="received_at">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Notes</label>
                            <textarea name="notes" class="form-control" rows="3"></textarea>
                        </div>
                        <small class="text-muted">Payments are automatically matched to open periods based on the received date. Locked periods reject new receipts.</small>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Receipt</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
@endsection
