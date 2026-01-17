@extends('layouts.master')
@section('page_title', 'Manage Payments')
@section('content')

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title font-weight-bold">Manage Payment Records for {{ $sr->user->name}} </h6>
            {!! Qs::getPanelOptions() !!}
        </div>

        <div class="card-body">
                <ul class="nav nav-tabs nav-tabs-highlight">
                    <li class="nav-item"><a href="#all-uc" class="nav-link active" data-toggle="tab">Incomplete Payments</a></li>
                    <li class="nav-item"><a href="#all-cl" class="nav-link" data-toggle="tab">Completed Payments</a></li>
                    <li class="nav-item"><a href="#installments" class="nav-link" data-toggle="tab">Installment Schedule</a></li>
                </ul>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="all-uc">
                <table class="table datatable-button-html5-columns table-responsive">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Pay_Ref</th>
                        <th>Amount ({{ Qs::currencyUnit() }})</th>
                        <th>Paid ({{ Qs::currencyUnit() }})</th>
                        <th>Balance ({{ Qs::currencyUnit() }})</th>
                        <th>Pay Now</th>
                        <th>Receipt_No</th>
                        <th>Year</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($uncleared as $uc)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $uc->payment->title }}</td>
                            <td>{{ $uc->payment->ref_no }}</td>

                            {{--Amount--}}
                            <td class="font-weight-bold" id="amt-{{ Qs::hash($uc->id) }}" data-amount="{{ $uc->payment->amount }}">{{ Qs::formatCurrency($uc->payment->amount) }}</td>

                            {{--Amount Paid--}}
                            <td id="amt_paid-{{ Qs::hash($uc->id) }}" data-amount="{{ $uc->amt_paid ?: 0 }}" class="text-blue font-weight-bold">{{ Qs::formatCurrency($uc->amt_paid ?: 0) }}</td>

                            {{--Balance--}}
                            <td id="bal-{{ Qs::hash($uc->id) }}" class="text-danger font-weight-bold">{{ Qs::formatCurrency($uc->balance ?: $uc->payment->amount) }}</td>

                            {{--Pay Now Form--}}
                            <td>
                                <form id="{{ Qs::hash($uc->id) }}" method="post" class="ajax-pay" action="{{ route('payments.pay_now', Qs::hash($uc->id)) }}">
                                    @csrf
                             <div class="row">
                                 <div class="col-md-7">
                                     <input min="1" max="{{ $uc->balance ?: $uc->payment->amount }}" id="val-{{ Qs::hash($uc->id) }}" class="form-control" required placeholder="Pay Now" title="Pay Now" name="amt_paid" type="number">
                                 </div>
                                 <div class="col-md-5">
                                     <button data-text="Pay" class="btn btn-danger" type="submit">Pay <i class="icon-paperplane ml-2"></i></button>
                                 </div>
                             </div>
                                </form>
                            </td>
                            {{--Receipt No--}}
                            <td>{{ $uc->ref_no }}</td>

                            <td>{{ $uc->year }}</td>

                            {{--Action--}}
                            <td class="text-center">
                                <div class="list-icons">
                                    <div class="dropdown">
                                        <a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i>
                                        </a>

                                        <div class="dropdown-menu dropdown-menu-left">

                                            {{--Reset Payment--}}
                                            <a id="{{ Qs::hash($uc->id) }}" onclick="confirmReset(this.id)" href="#" class="dropdown-item"><i class="icon-reset"></i> Reset Payment</a>
                                            <form method="post" id="item-reset-{{ Qs::hash($uc->id) }}" action="{{ route('payments.reset_record', Qs::hash($uc->id)) }}" class="hidden">@csrf @method('delete')</form>

                                            {{--Receipt--}}
                                                <a target="_blank" href="{{ route('payments.receipts', Qs::hash($uc->id)) }}" class="dropdown-item"><i class="icon-printer"></i> Print Receipt</a>
                                            {{--PDF Receipt--}}
                            {{--                    <a  href="{{ route('payments.pdf_receipts', Qs::hash($uc->id)) }}" class="dropdown-item download-receipt"><i class="icon-download"></i> Download Receipt</a>--}}

                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="tab-pane fade" id="all-cl">
                <table class="table datatable-button-html5-columns table-responsive">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Pay_Ref</th>
                        <th>Amount ({{ Qs::currencyUnit() }})</th>
                        <th>Receipt_No</th>
                        <th>Year</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($cleared as $cl)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $cl->payment->title }}</td>
                            <td>{{ $cl->payment->ref_no }}</td>

                            {{--Amount--}}
                            <td class="font-weight-bold">{{ Qs::formatCurrency($cl->payment->amount) }}</td>
                            {{--Receipt No--}}
                            <td>{{ $cl->ref_no }}</td>

                            <td>{{ $cl->year }}</td>

                            {{--Action--}}
                            <td class="text-center">
                                <div class="list-icons">
                                    <div class="dropdown">
                                        <a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i>
                                        </a>

                                        <div class="dropdown-menu dropdown-menu-left">

                                            {{--Reset Payment--}}
                                            <a id="{{ Qs::hash($cl->id) }}" onclick="confirmReset(this.id)" href="#" class="dropdown-item"><i class="icon-reset"></i> Reset Payment</a>
                                            <form method="post" id="item-reset-{{ Qs::hash($cl->id) }}" action="{{ route('payments.reset_record', Qs::hash($cl->id)) }}" class="hidden">@csrf @method('delete')</form>

                                            {{--Receipt--}}
                                            <a target="_blank" href="{{ route('payments.receipts', Qs::hash($cl->id)) }}" class="dropdown-item"><i class="icon-printer"></i> Print Receipt</a>

                                            {{--PDF Receipt--}}
                                            {{--                    <a  href="{{ route('payments.pdf_receipts', Qs::hash($uc->id)) }}" class="dropdown-item download-receipt"><i class="icon-download"></i> Download Receipt</a>--}}

                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

            </div>

            <div class="tab-pane fade" id="installments">
                <div class="d-flex justify-content-between mb-2">
                    <h6 class="mb-0">Installment Schedule</h6>
                    <div class="form-inline">
                        <label for="installment-status-filter" class="mr-2 mb-0">Status</label>
                        <select id="installment-status-filter" class="form-control form-control-sm mr-2">
                            <option value="">All</option>
                            <option value="pending">Pending</option>
                            <option value="partial">Partial</option>
                            <option value="paid">Paid</option>
                            <option value="overdue">Overdue</option>
                        </select>
                        <button type="button" id="btn-export-installments" class="btn btn-sm btn-outline-secondary">
                            Export CSV
                        </button>
                    </div>
                </div>
                <table class="table datatable-button-html5-columns table-responsive" id="tbl-student-installments">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Invoice #</th>
                        <th>Installment</th>
                        <th>Amount ({{ Qs::currencyUnit() }})</th>
                        <th>Paid ({{ Qs::currencyUnit() }})</th>
                        <th>Balance ({{ Qs::currencyUnit() }})</th>
                        <th>Due Date</th>
                        <th>Status</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($installments as $inst)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                @if($inst->invoice)
                                    <a href="{{ route('accounting.invoices.show', $inst->invoice_id) }}" target="_blank">
                                        {{ $inst->invoice->invoice_number }}
                                    </a>
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ optional($inst->installmentDefinition)->label ?? 'Installment' }}</td>
                            <td>{{ Qs::formatCurrency($inst->amount) }}</td>
                            <td>{{ Qs::formatCurrency($inst->amount_paid) }}</td>
                            <td>{{ Qs::formatCurrency($inst->amount - $inst->amount_paid) }}</td>
                            <td>{{ optional($inst->due_date)->format('Y-m-d') ?? '—' }}</td>
                            <td>
                                <span class="badge badge-{{ $inst->status === 'paid' ? 'success' : ($inst->status === 'overdue' ? 'danger' : 'warning') }}">
                                    {{ ucfirst($inst->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-muted">No installment schedule available for this student.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        </div>
    </div>

    {{--Payments Invoice List Ends--}}

@endsection

@push('scripts')
<script>
    $(function () {
        var dataTableInstance = null;
        if ($.fn.DataTable && $.fn.DataTable.isDataTable('#tbl-student-installments')) {
            dataTableInstance = $('#tbl-student-installments').DataTable();
        }

        $('#installment-status-filter').on('change', function () {
            var status = $(this).val();

            if (dataTableInstance) {
                // Assume Status is the last column
                var colIndex = $('#tbl-student-installments thead th').length - 1;
                if (status) {
                    dataTableInstance.column(colIndex).search(status, true, false).draw();
                } else {
                    dataTableInstance.column(colIndex).search('').draw();
                }
            } else {
                // Fallback simple DOM filter
                $('#tbl-student-installments tbody tr').each(function () {
                    var text = $(this).find('td:last').text().toLowerCase();
                    if (!status || text.indexOf(status) !== -1) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            }
        });

        $('#btn-export-installments').on('click', function () {
            var rows = [];
            $('#tbl-student-installments tr').each(function () {
                var cells = [];
                $(this).find('th,td').each(function () {
                    var text = $(this).text().trim().replace(/"/g, '""');
                    cells.push('"' + text + '"');
                });
                if (cells.length) {
                    rows.push(cells.join(','));
                }
            });

            if (!rows.length) {
                return;
            }

            var csvContent = rows.join('\n');
            var blob = new Blob([csvContent], {type: 'text/csv;charset=utf-8;'});
            var link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'student_installments_{{ $sr->user_id }}.csv';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });
    });
</script>
@endpush
