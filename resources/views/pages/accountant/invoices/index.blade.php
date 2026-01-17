@extends('layouts.master')
@section('page_title', 'Invoices & Billing')
@section('content')

<div class="card">
    <div class="card-header header-elements-inline">
        <h6 class="card-title">Invoices & Billing</h6>
        <div class="header-elements">
            <div class="list-icons">
                @if($canCreateInvoices)
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal-create-invoice">
                        <i class="icon-file-plus mr-2"></i> Create Invoice
                    </button>
                @endif
            </div>
            {!! Qs::getPanelOptions() !!}
        </div>
    </div>
    <div class="card-body">
        <div class="alert alert-info alert-styled-left">
            <span class="font-weight-semibold">Integrity Controls:</span>
            Locked academic periods cannot accept new invoices, waivers or edits. Use reversals for corrections.
        </div>

        <div class="table-responsive">
            <table class="table datatable-button-html5-columns">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Invoice #</th>
                    <th>Student</th>
                    <th>Class</th>
                    <th>Period</th>
                    <th>Due Date</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Total</th>
                    <th>Balance</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach($invoices as $invoice)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $invoice->invoice_number }}</td>
                        <td>{{ optional($invoice->student)->name }}</td>
                        <td>{{ optional(optional($invoice->studentRecord)->my_class)->name }}</td>
                        <td>
                            {{ optional($invoice->period)->name }}
                            @if(optional($invoice->period)->is_locked)
                                <span class="badge badge-danger ml-1">Locked</span>
                            @endif
                        </td>
                        <td>{{ optional($invoice->due_date)->format('Y-m-d') ?? '—' }}</td>
                        <td>
                            <span class="badge badge-secondary">Parent</span>
                            @if($invoice->childInvoices->count())
                                <span class="badge badge-info">{{ $invoice->childInvoices->count() }} installments</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-flat border-{{ $invoice->status === 'paid' ? 'success' : ($invoice->status === 'partially_paid' ? 'warning' : 'primary') }} text-{{ $invoice->status === 'paid' ? 'success' : ($invoice->status === 'partially_paid' ? 'warning' : 'primary') }}">
                                {{ ucfirst(str_replace('_', ' ', $invoice->status)) }}
                            </span>
                        </td>
                        <td>{{ Qs::formatCurrency($invoice->total_amount) }}</td>
                        <td>{{ Qs::formatCurrency($invoice->balance_due) }}</td>
                        <td>
                            <a href="{{ route('accounting.students.account', optional($invoice->studentRecord)->id) }}" class="btn btn-link p-0 mr-2"><i class="icon-user"></i> View Account</a>
                            @can('approveWaiver', $invoice)
                                <button class="btn btn-link text-warning p-0" data-toggle="modal" data-target="#waiver-{{ $invoice->id }}">
                                    <i class="icon-scissors"></i> Approve Waiver
                                </button>
                            @endcan
                        </td>
                    </tr>

                    @foreach($invoice->childInvoices as $child)
                        <tr class="bg-light">
                            <td></td>
                            <td class="pl-4">↳ {{ $child->invoice_number }}</td>
                            <td>{{ optional($child->student)->name }}</td>
                            <td>{{ optional(optional($child->studentRecord)->my_class)->name }}</td>
                            <td>{{ optional($child->period)->name }}</td>
                            <td>{{ optional($child->due_date)->format('Y-m-d') ?? '—' }}</td>
                            <td><span class="badge badge-primary">{{ $child->installment_label ?? ('Installment '.$child->installment_sequence) }}</span></td>
                            <td>
                                <span class="badge badge-flat border-{{ $child->status === 'paid' ? 'success' : ($child->status === 'partially_paid' ? 'warning' : 'primary') }} text-{{ $child->status === 'paid' ? 'success' : ($child->status === 'partially_paid' ? 'warning' : 'primary') }}">
                                    {{ ucfirst(str_replace('_', ' ', $child->status)) }}
                                </span>
                            </td>
                            <td>{{ Qs::formatCurrency($child->total_amount) }}</td>
                            <td>{{ Qs::formatCurrency($child->balance_due) }}</td>
                            <td><span class="text-muted">Installment</span></td>
                        </tr>
                    @endforeach

                    @can('approveWaiver', $invoice)
                        <div class="modal fade" id="waiver-{{ $invoice->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <form method="post" action="{{ route('accounting.invoices.waiver', $invoice) }}">
                                        @csrf
                                        <div class="modal-header bg-warning-400">
                                            <h6 class="modal-title">Approve Waiver - {{ $invoice->invoice_number }}</h6>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <p class="text-muted">Use waivers for authorised adjustments only. Every action is audit-logged with your ID and timestamp.</p>
                                            <div class="form-group">
                                                <label>Waiver Amount</label>
                                                <input type="number" class="form-control" step="0.01" min="0" max="{{ $invoice->balance_due }}" name="amount" required>
                                            </div>
                                            <div class="form-group">
                                                <label>Reason / Notes</label>
                                                <textarea class="form-control" name="notes" rows="3" required></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-warning">Approve Waiver</button>
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
            {{ $invoices->links() }}
        </div>
    </div>
</div>

@if($canCreateInvoices)
    <div class="modal fade" id="modal-create-invoice" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <form method="post" action="{{ route('accounting.invoices.store') }}" id="form-create-invoice">
                    @csrf
                    <div class="modal-header bg-primary text-white">
                        <h6 class="modal-title">Create Invoice</h6>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>Student</label>
                                <select name="student_id" class="form-control select-search" required>
                                    <option value="">Select student</option>
                                    @foreach($students as $student)
                                        <option value="{{ $student->id }}">{{ $student->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label>Fee Structure (optional)</label>
                                <select name="fee_structure_id" class="form-control select-search">
                                    <option value="">Standalone invoice</option>
                                    @foreach($feeStructures as $structure)
                                        <option value="{{ $structure->id }}">{{ $structure->name }} ({{ $structure->academic_year }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label>Academic Period</label>
                                <select name="academic_period_id" class="form-control" required>
                                    <option value="">Select period</option>
                                    @foreach($periods as $period)
                                        <option value="{{ $period->id }}" {{ $period->is_locked ? 'disabled' : '' }}>
                                            {{ $period->name }} {{ $period->is_locked ? '(Locked)' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>Student Record ID (optional)</label>
                                <input type="number" name="student_record_id" class="form-control" placeholder="Link to student record">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Due Date</label>
                                <input type="date" class="form-control" name="due_date">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Notes</label>
                                <input type="text" class="form-control" name="notes">
                            </div>
                        </div>

                        <hr>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0">Invoice Items</h6>
                            <button type="button" class="btn btn-light btn-sm" id="btn-add-invoice-item"><i class="icon-plus2 mr-1"></i> Add Item</button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm" id="invoice-items-table">
                                <thead>
                                <tr>
                                    <th>Description</th>
                                    <th style="width: 18%">Fee Item</th>
                                    <th style="width: 10%">Qty</th>
                                    <th style="width: 15%">Unit Amount</th>
                                    <th style="width: 10%">Optional?</th>
                                    <th style="width: 5%"></th>
                                </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                        <small class="text-muted">Every invoice requires at least one line item. Amounts are validated server-side with audit logs for traceability.</small>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Invoice</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
@endsection

@push('scripts')
<script>
    (function () {
        var itemIndex = 0;
        function addInvoiceItemRow() {
            var tableBody = $('#invoice-items-table tbody');
            var row = $('<tr>');
            row.append('<td><input type="text" name="items[' + itemIndex + '][description]" class="form-control" required></td>');
            var feeSelect = $('<select class="form-control select-search" name="items[' + itemIndex + '][fee_item_id]"><option value="">Custom</option>@foreach($feeItems as $item)<option value="{{ $item->id }}">{{ $item->name }} @if($item->category) ({{ $item->category->name }}) @endif</option>@endforeach</select>');
            row.append($('<td>').append(feeSelect));
            row.append('<td><input type="number" name="items[' + itemIndex + '][quantity]" class="form-control" min="1" value="1" required></td>');
            row.append('<td><input type="number" name="items[' + itemIndex + '][unit_amount]" class="form-control" step="0.01" min="0" required></td>');
            row.append('<td class="text-center"><input type="checkbox" name="items[' + itemIndex + '][is_optional]" value="1"></td>');
            row.append('<td class="text-center"><button type="button" class="btn btn-link text-danger p-0 btn-remove-item"><i class="icon-trash"></i></button></td>');
            tableBody.append(row);
            feeSelect.select2({ width: '100%' });
            itemIndex++;
        }

        $('#btn-add-invoice-item').on('click', addInvoiceItemRow);
        $('#invoice-items-table').on('click', '.btn-remove-item', function () {
            $(this).closest('tr').remove();
        });

        // Initialize with one item row by default
        addInvoiceItemRow();
    })();
</script>
@endpush
