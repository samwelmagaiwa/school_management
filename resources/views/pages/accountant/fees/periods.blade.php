@extends('layouts.master')
@section('page_title', 'Academic Periods / Terms')
@section('content')

@php $today = \Carbon\Carbon::today(); @endphp

<div class="card">
    <div class="card-header header-elements-inline">
        <h6 class="card-title">Academic Periods / Terms</h6>
        {!! Qs::getPanelOptions() !!}
    </div>
    <div class="card-body">
        <p class="mb-3">Define the terms/periods that group billing, invoices and fee structures within each academic year.</p>

        <div class="card border">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0">Existing Periods</h6>
                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modal-create-period">
                    <i class="icon-plus2 mr-1"></i> Create New Period
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-striped table-sm mb-0">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Year</th>
                                <th>Code</th>
                                <th>Dates</th>
                                <th>Due</th>
                                <th>Days Remaining</th>
                                <th>Status</th>
                                <th class="text-right">Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($periods as $period)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $period->name }}</td>
                                    <td>{{ $period->academic_year }}</td>
                                    <td>{{ $period->code }}</td>
                                    <td>
                                        <span class="d-block small text-muted">{{ optional($period->start_date)->format('M d, Y') }} → {{ optional($period->end_date)->format('M d, Y') }}</span>
                                    </td>
                                    <td>{{ optional($period->due_date)->format('M d, Y') }}</td>
                                    @php
                                        $effectiveEnd = $period->due_date ?? $period->end_date;
                                        $daysRemaining = $effectiveEnd ? max(0, $today->diffInDays($effectiveEnd, false)) : null;
                                    @endphp
                                    <td>{{ $daysRemaining !== null ? $daysRemaining : '—' }}</td>
                                    <td>
                                        <span class="badge badge-flat border-{{ $period->is_locked ? 'danger' : 'success' }} text-{{ $period->is_locked ? 'danger' : 'success' }}">
                                            {{ $period->is_locked ? 'Locked' : 'Open' }}
                                        </span>
                                    </td>
                                    <td class="text-right">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle px-2" type="button" data-toggle="dropdown">
                                                &vellip;
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                <button type="button" class="dropdown-item" data-toggle="modal" data-target="#modal-edit-period"
                                                        data-id="{{ $period->id }}"
                                                        data-name="{{ $period->name }}"
                                                        data-code="{{ $period->code }}"
                                                        data-year="{{ $period->academic_year }}"
                                                        data-start="{{ optional($period->start_date)->format('Y-m-d') }}"
                                                        data-end="{{ optional($period->end_date)->format('Y-m-d') }}"
                                                        data-due="{{ optional($period->due_date)->format('Y-m-d') }}"
                                                        data-ordering="{{ $period->ordering }}"
                                                        data-locked="{{ $period->is_locked ? 1 : 0 }}">
                                                    Edit
                                                </button>
                                                <button type="button" class="dropdown-item text-danger js-delete-period"
                                                        data-toggle="modal"
                                                        data-target="#modal-delete-period"
                                                        data-route="{{ route('accounting.periods.destroy', $period) }}"
                                                        data-id="{{ $period->id }}"
                                                        data-name="{{ $period->name }}"
                                                        {{ $period->is_locked ? 'disabled' : '' }}>
                                                    Delete
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">No periods configured yet.</td>
                                </tr>
                            @endforelse
                            </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-create-period" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h6 class="modal-title">Create New Academic Period</h6>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form class="ajax-store" method="post" action="{{ route('accounting.periods.store') }}" data-modal="#modal-create-period">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" placeholder="e.g. Term 1" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Academic Year <span class="text-danger">*</span></label>
                                <input type="text" name="academic_year" class="form-control" placeholder="2025-2026" pattern="^[0-9]{4}-[0-9]{4}$" title="Use the format YYYY-YYYY" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Start Date <span class="text-danger">*</span></label>
                                <input type="date" name="start_date" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>End Date <span class="text-danger">*</span></label>
                                <input type="date" name="end_date" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Due Date <span class="text-danger">*</span></label>
                                <input type="date" name="due_date" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Ordering <span class="text-danger">*</span></label>
                                <input type="number" name="ordering" class="form-control" min="1" value="{{ max(1, $nextOrdering ?? 1) }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Status <span class="text-danger">*</span></label>
                                <select name="is_locked" class="form-control" required>
                                    <option value="0" selected>Open (default)</option>
                                    <option value="1">Locked</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Code</label>
                                <input type="text" class="form-control" value="Auto-generated" disabled>
                                <input type="hidden" name="code" value="">
                                <small class="text-muted d-block">Code is derived from the period name plus a sequence.</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Period</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-edit-period" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h6 class="modal-title">Edit Academic Period</h6>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form class="ajax-update" method="post" action="" data-modal="#modal-edit-period">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Code</label>
                            <input type="text" name="code" class="form-control" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Ordering</label>
                            <input type="number" name="ordering" class="form-control" min="1" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Academic Year</label>
                        <input type="text" name="academic_year" class="form-control" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Start Date</label>
                            <input type="date" name="start_date" class="form-control" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>End Date</label>
                            <input type="date" name="end_date" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Due Date</label>
                        <input type="date" name="due_date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="is_locked" class="form-control">
                            <option value="0">Open</option>
                            <option value="1">Locked</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-secondary">Update Period</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-delete-period" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h6 class="modal-title">Delete this period?</h6>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="mb-2">You are about to remove <strong class="js-period-name">this period</strong>. This action cannot be undone.</p>
                <p class="text-muted small mb-0">Associated fee structures will retain the orphaned reference to this code/year. Consider archiving instead if you need to preserve history.</p>
            </div>
            <form method="post" action="" id="delete-period-form">
                @csrf
                @method('DELETE')
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Yes, delete</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $('#modal-edit-period').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var modal = $(this);
        var id = button.data('id');
        modal.find('form').attr('action', '/accounting/periods/' + id);
        modal.find('input[name="name"]').val(button.data('name'));
        modal.find('input[name="code"]').val(button.data('code'));
        modal.find('input[name="academic_year"]').val(button.data('year'));
        modal.find('input[name="start_date"]').val(button.data('start'));
        modal.find('input[name="end_date"]').val(button.data('end'));
        modal.find('input[name="due_date"]').val(button.data('due'));
        modal.find('input[name="ordering"]').val(button.data('ordering'));
        modal.find('select[name="is_locked"]').val(button.data('locked'));
    });

    $('#modal-delete-period').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var deleteRoute = button.data('route');
        var periodId = button.data('id');
        var periodName = button.data('name') || 'this period';
        var form = document.getElementById('delete-period-form');

        if (form) {
            if (deleteRoute) {
                form.setAttribute('action', deleteRoute);
            } else if (periodId) {
                form.setAttribute('action', '{{ url('accounting/periods') }}/' + periodId);
            } else {
                form.removeAttribute('action');
            }
        }

        $(this).find('.js-period-name').text(periodName);
    });

    // Auto-fill Due Date when End Date is selected
    $('input[name="end_date"]').on('change', function() {
        var endDate = $(this).val();
        var $form = $(this).closest('form');
        var $dueDate = $form.find('input[name="due_date"]');
        
        // Only auto-fill if due date is empty or user hasn't manually edited it differently? 
        // User request: "when i select End Date ... due date will be set automatically"
        // Simplest and most predictable behavior: Always sync, unless we want to be fancy.
        // But user said "if i want i can adjust", implying the sync happens first, then they can change it.
        // So simply setting it is correct.
        if (endDate) {
            $dueDate.val(endDate);
        }
    });
</script>
@endpush
@endsection
