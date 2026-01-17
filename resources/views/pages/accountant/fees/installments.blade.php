@extends('layouts.master')
@php use App\Helpers\Qs; @endphp
@section('page_title', 'Installment Plan - ' . $structure->name)
@section('content')

@php
    $nextSequence = ($installments->max('sequence') ?? 0) + 1;
@endphp

@if(! $plan)
    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title">Create Installment Plan for {{ $structure->name }}</h6>
            {!! Qs::getPanelOptions() !!}
        </div>
        <div class="card-body">
            <p class="text-muted">There is no active installment plan for this structure. Create one to enable automated billing schedules.</p>
            <form method="post" action="{{ route('accounting.installments.plan.store', $structure->id) }}" class="form-inline">
                @csrf
                <div class="form-group mr-2 mb-2">
                    <label class="sr-only">Plan Name</label>
                    <input type="text" name="name" class="form-control" value="Default Installment Plan" required>
                </div>
                <button type="submit" class="btn btn-primary">Create Plan <i class="icon-checkmark3 ml-2"></i></button>
            </form>
        </div>
    </div>
@else
    <div class="card mb-3">
        <div class="card-header header-elements-inline">
            <h6 class="card-title">Installment Plan Overview</h6>
            <div class="header-elements">
                <form method="post" action="{{ route('accounting.installments.plan.store', $structure->id) }}" class="form-inline">
                    @csrf
                    <input type="text" name="name" value="{{ $plan->name }}" class="form-control mr-2" required>
                    <button type="submit" class="btn btn-outline-primary">Save Plan Name</button>
                </form>
                <button type="button" class="btn btn-primary ml-3" data-toggle="modal" data-target="#modal-add-installment">
                    <i class="icon-plus2 mr-1"></i> Add Installment
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-2">
                        <span class="text-muted d-block">Structure</span>
                        <strong>{{ $structure->name }}</strong>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-2">
                        <span class="text-muted d-block">Academic Period</span>
                        <strong>{{ optional($structure->academicPeriod)->name ?? '—' }}</strong>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-2">
                        <span class="text-muted d-block">Default Due Date</span>
                        <strong>{{ optional($structure->due_date)->format('Y-m-d') ?? '—' }}</strong>
                    </div>
                </div>
            </div>
            <p class="text-muted mb-0">Installment rules created here drive automated billing. Accountants can only collect against the generated schedule; edits happen here only.</p>
        </div>
    </div>

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title">Installment Definitions</h6>
            {!! Qs::getPanelOptions() !!}
        </div>
        <div class="card-body">
            <table class="table datatable-button-html5-columns" id="installments-table">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Label</th>
                    <th>%</th>
                    <th>Fixed Amount</th>
                    <th>Due Date</th>
                    <th>Grace (days)</th>
                    <th>Penalty</th>
                    <th class="text-center">Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse($installments as $row)
                    <tr>
                        <td>{{ $row->sequence }}</td>
                        <td>{{ $row->label }}</td>
                        <td>{{ $row->percentage !== null ? number_format($row->percentage, 2) . '%' : '—' }}</td>
                        <td>{{ $row->fixed_amount !== null ? Qs::formatCurrency($row->fixed_amount) : '—' }}</td>
                        <td>{{ optional($row->due_date)->format('Y-m-d') ?? '—' }}</td>
                        <td>{{ $row->grace_days ?? '—' }}</td>
                        <td>
                            @if($row->late_penalty_type === 'fixed')
                                Fixed {{ Qs::formatCurrency($row->late_penalty_value) }}
                            @elseif($row->late_penalty_type === 'percentage')
                                {{ number_format($row->late_penalty_value, 2) }}%
                            @else
                                —
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="btn-group">
                                <button type="button"
                                        class="btn btn-sm btn-outline-primary"
                                        data-toggle="modal"
                                        data-target="#modal-edit-installment"
                                        data-id="{{ $row->id }}"
                                        data-sequence="{{ $row->sequence }}"
                                        data-label="{{ $row->label }}"
                                        data-percentage="{{ $row->percentage }}"
                                        data-fixed_amount="{{ $row->fixed_amount }}"
                                        data-due_date="{{ optional($row->due_date)->format('Y-m-d') }}"
                                        data-grace_days="{{ $row->grace_days }}"
                                        data-penalty_type="{{ $row->late_penalty_type }}"
                                        data-penalty_value="{{ $row->late_penalty_value }}">
                                    <i class="icon-pencil"></i>
                                </button>
                                <form method="post" action="{{ route('accounting.installments.rows.destroy', $row->id) }}" onsubmit="return confirm('Remove this installment definition?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger"><i class="icon-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-muted">No installments defined yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Installment Modal -->
    <div class="modal fade" id="modal-add-installment" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="post" action="{{ route('accounting.installments.rows.store', [$structure->id, $plan->id]) }}">
                    @csrf
                    <div class="modal-header bg-primary text-white">
                        <h6 class="modal-title">Add Installment</h6>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        @include('pages.accountant.fees.partials.installment-form-fields', ['sequence' => $nextSequence])
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Installment Modal -->
    <div class="modal fade" id="modal-edit-installment" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="post" action="" id="edit-installment-form">
                    @csrf
                    @method('PUT')
                    <div class="modal-header bg-primary text-white">
                        <h6 class="modal-title">Edit Installment</h6>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        @include('pages.accountant.fees.partials.installment-form-fields', ['sequence' => null])
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

@push('scripts')
<script>
    $('#modal-edit-installment').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var modal = $(this);
        var form = modal.find('form#edit-installment-form');
        var id = button.data('id');
        form.attr('action', '/accounting/installments/' + id);

        form.find('[name="sequence"]').val(button.data('sequence'));
        form.find('[name="label"]').val(button.data('label'));
        form.find('[name="percentage"]').val(button.data('percentage'));
        form.find('[name="fixed_amount"]').val(button.data('fixed_amount'));
        form.find('[name="due_date"]').val(button.data('due_date'));
        form.find('[name="grace_days"]').val(button.data('grace_days'));
        form.find('[name="late_penalty_type"]').val(button.data('penalty_type'));
        form.find('[name="late_penalty_value"]').val(button.data('penalty_value'));
    });

    $('#modal-add-installment').on('show.bs.modal', function () {
        var maxSequence = 0;
        $('#installments-table tbody tr').each(function () {
            var value = parseInt($(this).find('td:first').text(), 10);
            if (!isNaN(value)) {
                maxSequence = Math.max(maxSequence, value);
            }
        });

        var modal = $(this);
        modal.find('input[name="sequence"]').val(maxSequence + 1);
        modal.find('input[name="label"], input[name="percentage"], input[name="fixed_amount"], input[name="due_date"], input[name="grace_days"], input[name="late_penalty_value"]').val('');
        modal.find('select[name="late_penalty_type"]').val('none');
    });
</script>
@endpush

@endsection
