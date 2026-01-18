@extends('layouts.master')
@section('page_title', 'Fee Structures')
@section('content')

<style>
    /* Custom extra-wide modals */
    .modal-xl {
        max-width: 95vw !important;
    }
    @media (min-width: 1200px) {
        .modal-xl {
            width: 95%;
        }
    }
    /* Compact Table Styling */
    #structures-table th, #structures-table td {
        padding: 0.75rem 0.5rem !important;
        vertical-align: middle;
    }
    .btn-xs-custom {
        padding: 0.2rem 0.4rem;
        font-size: 0.8rem;
    }
</style>

<div class="card">
    <div class="card-header header-elements-inline">
        <h6 class="card-title">Fee Structures</h6>
        <div class="header-elements">
            {!! Qs::getPanelOptions() !!}
        </div>
    </div>
    <div class="card-body">
        <p class="mb-3">Configure term-based fee structures and class/section specific amounts.</p>

        @if(Qs::userIsTeamSA())
            <div id="create-structure-btn" class="d-none">
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal-create-structure">
                    <i class="icon-file-plus mr-2"></i> Create Structure
                </button>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table datatable-button-html5-columns" id="structures-table">
            <thead>
            <tr>
                <th style="width: 30px;">#</th>
                <th class="text-nowrap">Structure Name</th>
                <th class="text-nowrap">Academic Year</th>
                <th class="text-nowrap">Period</th>
                <th class="text-nowrap">Due Date</th>
                <th class="text-nowrap text-center">Status</th>
                <th class="text-nowrap text-center">Installments</th>
                <th class="text-nowrap text-center">Billing</th>
                <th class="text-nowrap text-center">Actions</th>
            </tr>
            </thead>
            <tbody>
            @foreach($structures as $structure)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $structure->name }}</td>
                    <td>{{ $structure->academic_year }}</td>
                    <td>{{ optional($structure->academicPeriod)->name }}</td>
                    <td>{{ optional($structure->due_date)->format('Y-m-d') }}</td>
                    <td>
                        <span class="badge badge-flat border-{{ $structure->status === 'published' ? 'success' : ($structure->status === 'archived' ? 'secondary' : 'primary') }} text-{{ $structure->status === 'published' ? 'success' : ($structure->status === 'archived' ? 'secondary' : 'primary') }} text-nowrap">
                            {{ ucfirst($structure->status) }}
                        </span>
                    </td>
                    <td class="text-center">
                        @if(Qs::userIsTeamSA())
                            <a href="{{ route('accounting.installments.index', $structure->id) }}" class="btn btn-sm btn-outline-primary mb-1">
                                Installments
                            </a>
                        @else
                            <span class="text-muted">Restricted</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if(Qs::userIsTeamSA())
                            <form method="post" action="{{ route('accounting.fee-structures.billing.generate', $structure->id) }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-success" onclick="return confirm('Generate billing for this structure for all eligible students?');">
                                    Generate Billing
                                </button>
                            </form>
                        @else
                            <span class="text-muted">Restricted</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if(Qs::userIsTeamSA())
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle px-1 py-0" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="line-height: 1;">
                                    &vellip;
                                </button>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <button type="button" class="dropdown-item" data-toggle="modal" data-target="#modal-edit-structure-{{ $structure->id }}">
                                        Edit Structure
                                    </button>
                                    <form method="post" action="{{ route('accounting.fee-structures.destroy', $structure->id) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Are you sure you want to delete this structure?');">
                                            Delete Structure
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @else
                            <span class="text-muted">Restricted</span>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
            </table>
        </div>
    </div>
</div>

@if(Qs::userIsTeamSA())
    <div class="modal fade" id="modal-create-structure" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <form method="post" action="{{ route('accounting.fee-structures.store') }}" class="ajax-store">
                    @csrf
                    <div class="modal-header bg-primary text-white">
                        <h6 class="modal-title">Create Fee Structure</h6>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" placeholder="e.g. Primary Class 5 - 2026/2027" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Academic Year <span class="text-danger">*</span></label>
                                    <input type="text" name="academic_year" class="form-control" placeholder="e.g. 2026-2027" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Academic Period/Terms</label>
                                    <select name="academic_period_id" class="form-control">
                                        <option value="">None</option>
                                        @foreach($periods as $period)
                                            <option value="{{ $period->id }}">{{ $period->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Due Date</label>
                                    <input type="date" name="due_date" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Status <span class="text-danger">*</span></label>
                                    <select name="status" class="form-control" required>
                                        <option value="draft">Draft</option>
                                        <option value="published" selected>Published</option>
                                        <option value="archived">Archived</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group h-100">
                                    <label>Description</label>
                                    <textarea name="description" rows="3" class="form-control h-100"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Structure</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- EDIT STRUCTURE WIZARD (Multi-Step: Basic Info → Terms → Installments → Items) --}}
    @foreach($structures as $structure)
        <div class="modal fade" id="modal-edit-structure-{{ $structure->id }}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h6 class="modal-title">Manage Fee Structure: {{ $structure->name }}</h6>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        {{-- Navigation Tabs --}}
                        <ul class="nav nav-tabs nav-tabs-highlight mb-3" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-toggle="tab" href="#basic-info-{{ $structure->id }}">
                                    <i class="icon-file-text2 mr-1"></i> Basic Info
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#terms-{{ $structure->id }}">
                                    <i class="icon-calendar3 mr-1"></i> Terms
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#installments-{{ $structure->id }}">
                                    <i class="icon-cash3 mr-1"></i> Installments
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#items-{{ $structure->id }}">
                                    <i class="icon-list2 mr-1"></i> Items
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content">
                            {{-- STEP 1: BASIC INFO --}}
                            <div class="tab-pane fade show active" id="basic-info-{{ $structure->id }}">
                                <form method="post" action="{{ route('accounting.fee-structures.update', $structure->id) }}" class="ajax-update">
                                    @csrf
                                    @method('PUT')
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Name <span class="text-danger">*</span></label>
                                                <input type="text" name="name" class="form-control" value="{{ $structure->name }}" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Academic Year <span class="text-danger">*</span></label>
                                                <input type="text" name="academic_year" class="form-control" value="{{ $structure->academic_year }}" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Due Date</label>
                                                <input type="date" name="due_date" class="form-control" value="{{ $structure->due_date ? $structure->due_date->format('Y-m-d') : '' }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Status <span class="text-danger">*</span></label>
                                                <select name="status" class="form-control" required>
                                                    <option value="draft" {{ $structure->status === 'draft' ? 'selected' : '' }}>Draft</option>
                                                    <option value="published" {{ $structure->status === 'published' ? 'selected' : '' }}>Published</option>
                                                    <option value="archived" {{ $structure->status === 'archived' ? 'selected' : '' }}>Archived</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Academic Period (Optional)</label>
                                                <select name="academic_period_id" class="form-control">
                                                    <option value="">None</option>
                                                    @foreach($periods as $period)
                                                        <option value="{{ $period->id }}" {{ (int) $structure->academic_period_id === (int) $period->id ? 'selected' : '' }}>{{ $period->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>Description</label>
                                                <textarea name="description" rows="2" class="form-control">{{ $structure->description }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="icon-checkmark3 mr-1"></i> Save Basic Info
                                    </button>
                                </form>
                            </div>

                            {{-- STEP 2: DEFINE TERMS --}}
                            <div class="tab-pane fade" id="terms-{{ $structure->id }}">
                                <form method="post" action="{{ route('accounting.fee-structures.terms.store', $structure->id) }}" class="ajax-store" id="form-terms-{{ $structure->id }}">
                                    @csrf
                                    <div class="alert alert-info border-0">
                                        <i class="icon-info22 mr-2"></i>
                                        Define the terms for this academic year (e.g., Term 1, Term 2). Each term has a total amount and can be paid in installments or as a lump sum.
                                    </div>

                                    <div class="form-group">
                                        <label>Number of Terms</label>
                                        @php $termCount = $structure->terms->count(); @endphp
                                        <select class="form-control num-terms-selector" id="num-terms-{{ $structure->id }}" onchange="generateTermFields{{ $structure->id }}(this.value)">
                                            @for($i = 1; $i <= 6; $i++)
                                                <option value="{{ $i }}" {{ ($termCount == $i || ($termCount == 0 && $i == 2)) ? 'selected' : '' }}>{{ $i }} Term{{ $i > 1 ? 's' : '' }}</option>
                                            @endfor
                                        </select>
                                    </div>

                                    <div id="terms-container-{{ $structure->id }}">
                                        @foreach($structure->terms as $index => $term)
                                            <div class="card mb-2">
                                                <div class="card-body p-3">
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <div class="form-group mb-2">
                                                                <label class="mb-1">Term {{ $index + 1 }} Name</label>
                                                                <input type="text" name="terms[{{ $index }}][name]" class="form-control form-control-sm" value="{{ $term->name }}" required>
                                                                <input type="hidden" name="terms[{{ $index }}][sequence]" value="{{ $term->sequence }}">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group mb-2">
                                                                <label class="mb-1">Total Amount</label>
                                                                <input type="number" name="terms[{{ $index }}][total_amount]" class="form-control form-control-sm term-amount" data-structure-id="{{ $structure->id }}" step="0.01" min="0" value="{{ $term->total_amount }}" required>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group mb-2">
                                                                <label class="mb-1">Enable Installments?</label>
                                                                <select name="terms[{{ $index }}][installments_enabled]" class="form-control form-control-sm">
                                                                    <option value="1" {{ $term->installments_enabled ? 'selected' : '' }}>Yes</option>
                                                                    <option value="0" {{ !$term->installments_enabled ? 'selected' : '' }}>No (Full Payment)</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                                        <h6>Total Year Amount: <span id="total-year-{{ $structure->id }}" class="text-primary font-weight-bold">0</span></h6>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="icon-floppy-disk mr-1"></i> Save Terms
                                        </button>
                                    </div>
                                </form>
                            </div>

                            {{-- STEP 3: CONFIGURE INSTALLMENTS --}}
                            <div class="tab-pane fade" id="installments-{{ $structure->id }}">
                                @if($structure->terms->count() > 0)
                                    <div class="accordion" id="accordion-installments-{{ $structure->id }}">
                                        @foreach($structure->terms as $term)
                                            <div class="card">
                                                <div class="card-header">
                                                    <h6 class="card-title">
                                                        <a data-toggle="collapse" href="#collapse-term-{{ $term->id }}">
                                                            {{ $term->name }} (Total: {{ number_format($term->total_amount, 2) }})
                                                            <span class="badge badge-{{ $term->installments_enabled ? 'success' : 'secondary' }} ml-2">
                                                                {{ $term->installments_enabled ? 'Installments Enabled' : 'Full Payment Only' }}
                                                            </span>
                                                        </a>
                                                    </h6>
                                                </div>
                                                <div id="collapse-term-{{ $term->id }}" class="collapse {{ $loop->first ? 'show' : '' }}" data-parent="#accordion-installments-{{ $structure->id }}">
                                                    <div class="card-body">
                                                        <form method="post" action="{{ route('accounting.terms.installments.store', $term->id) }}" class="ajax-store installment-config-form">
                                                            @csrf
                                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                                <label class="font-weight-semibold mb-0 text-primary">Installment Breakdown</label>
                                                                <button type="button" class="btn btn-outline-primary btn-sm add-installment-row" data-term-id="{{ $term->id }}" data-term-amount="{{ $term->total_amount }}">
                                                                    <i class="icon-plus2 mr-1"></i> Add Installment
                                                                </button>
                                                            </div>
                                                            <div class="installments-fields-{{ $term->id }}">
                                                                @foreach($term->installments as $index => $inst)
                                                                    <div class="installment-row mb-2 border p-2 rounded bg-white shadow-sm">
                                                                        <div class="row align-items-end">
                                                                            <div class="col-md-4">
                                                                                <label class="mb-1 small font-weight-semibold">Label</label>
                                                                                <input type="text" name="installments[{{ $index }}][label]" class="form-control form-control-sm" value="{{ $inst->label }}" required>
                                                                                <input type="hidden" name="installments[{{ $index }}][sequence]" value="{{ $inst->sequence }}">
                                                                            </div>
                                                                            <div class="col-md-3">
                                                                                <label class="mb-1 small font-weight-semibold">Amount</label>
                                                                                <input type="number" name="installments[{{ $index }}][fixed_amount]" class="form-control form-control-sm installment-amount" data-term-id="{{ $term->id }}" data-term-amount="{{ $term->total_amount }}" step="0.01" value="{{ $inst->fixed_amount }}" required>
                                                                            </div>
                                                                            <div class="col-md-3">
                                                                                <label class="mb-1 small font-weight-semibold">Due Date</label>
                                                                                <input type="date" name="installments[{{ $index }}][due_date]" class="form-control form-control-sm" value="{{ $inst->due_date ? $inst->due_date->format('Y-m-d') : '' }}" required>
                                                                            </div>
                                                                            <div class="col-md-2 text-right">
                                                                                <button type="button" class="btn btn-outline-danger btn-sm border-0 remove-installment-row"><i class="icon-trash"></i></button>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                            <div class="alert alert-light border mt-2 p-2 installment-validation-{{ $term->id }}">
                                                                <small>
                                                                    <strong>Validation:</strong> 
                                                                    Total: <span class="installment-total-{{ $term->id }}">0</span> / {{ number_format($term->total_amount, 2) }}
                                                                    <span class="badge badge-success validation-badge-{{ $term->id }}" style="display:none;">✓ Balanced</span>
                                                                    <span class="badge badge-danger validation-error-{{ $term->id }}" style="display:none;">⚠ Mismatch</span>
                                                                </small>
                                                            </div>
                                                            <button type="submit" class="btn btn-primary btn-sm mt-2">Save Installments</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="alert alert-warning border-0">
                                        <i class="icon-warning22 mr-2"></i>
                                        Please define terms first in the "Terms" tab before configuring installments.
                                    </div>
                                @endif
                            </div>

                            {{-- STEP 4: ADD ITEMS --}}
                            <div class="tab-pane fade" id="items-{{ $structure->id }}">
                                <div class="alert alert-info border-0">
                                    <i class="icon-info22 mr-2"></i>
                                    Assign fee items (Transport, Meals, etc.) to each installment. The total of items must equal the installment amount.
                                </div>
                                
                                @if($structure->terms->count() > 0)
                                    <div class="accordion" id="accordion-items-{{ $structure->id }}">
                                        @foreach($structure->terms as $term)
                                            <div class="card mb-2">
                                                <div class="card-header bg-light py-2">
                                                    <h6 class="card-title">
                                                        <a data-toggle="collapse" class="text-dark" href="#collapse-items-term-{{ $term->id }}">
                                                            <i class="icon-calendar3 mr-2"></i>{{ $term->name }}
                                                        </a>
                                                    </h6>
                                                </div>
                                                <div id="collapse-items-term-{{ $term->id }}" class="collapse {{ $loop->first ? 'show' : '' }}" data-parent="#accordion-items-{{ $structure->id }}">
                                                    <div class="card-body p-2">
                                                        @if($term->installments->count() > 0)
                                                            @foreach($term->installments as $inst)
                                                                <div class="card border-left-{{ $inst->isBalanced() ? 'success' : 'danger' }} border-left-3 mb-3 shadow-none bg-light">
                                                                    <div class="card-header d-flex justify-content-between align-items-center py-2">
                                                                        <h6 class="font-weight-semibold mb-0">{{ $inst->label }} ({{ number_format($inst->fixed_amount, 2) }})</h6>
                                                                        <span class="badge badge-{{ $inst->isBalanced() ? 'success' : 'danger' }} item-balance-badge-{{ $inst->id }}">
                                                                            @if($inst->isBalanced())
                                                                                <i class="icon-checkmark4 mr-1"></i> Saved & Balanced
                                                                            @else
                                                                                Not Saved / Mismatch
                                                                            @endif
                                                                        </span>
                                                                    </div>
                                                                    <div class="card-body p-2">
                                                                        <form method="post" action="{{ route('accounting.installments.items.store', $inst->id) }}" class="ajax-store">
                                                                            @csrf
                                                                            <table class="table table-sm table-borderless">
                                                                                <thead>
                                                                                    <tr>
                                                                                        <th style="width: 50%">Fee Item</th>
                                                                                        <th style="width: 40%">Amount</th>
                                                                                        <th style="width: 10%"></th>
                                                                                    </tr>
                                                                                </thead>
                                                                                <tbody class="item-rows-{{ $inst->id }}">
                                                                                    @foreach($inst->items as $itemIndex => $item)
                                                                                        <tr>
                                                                                            <td>
                                                                                                <select name="items[{{ $itemIndex }}][fee_item_id]" class="form-control form-control-sm select-fee-item" data-inst-id="{{ $inst->id }}" required>
                                                                                                    <option value="">Select Category</option>
                                                                                                    @foreach($fee_categories as $fc)
                                                                                                        <option value="" data-name="{{ $fc->name }}" {{ ($item->name ?? '') == $fc->name ? 'selected' : '' }}>{{ $fc->name }}</option>
                                                                                                    @endforeach
                                                                                                </select>
                                                                                                <input type="hidden" name="items[{{ $itemIndex }}][name]" value="{{ $item->name }}">
                                                                                            </td>
                                                                                            <td>
                                                                                                <input type="number" name="items[{{ $itemIndex }}][amount]" class="form-control form-control-sm item-amount" data-inst-id="{{ $inst->id }}" step="0.01" value="{{ $item->amount }}" required>
                                                                                            </td>
                                                                                            <td>
                                                                                                <button type="button" class="btn btn-outline-danger btn-sm border-0 remove-item-row"><i class="icon-trash"></i></button>
                                                                                            </td>
                                                                                        </tr>
                                                                                    @endforeach
                                                                                </tbody>
                                                                                <tfoot>
                                                                                    <tr>
                                                                                        <td colspan="3">
                                                                                            <button type="button" class="btn btn-light btn-sm add-item-row" data-inst-id="{{ $inst->id }}">
                                                                                                <i class="icon-plus2 mr-1"></i> Add Row
                                                                                            </button>
                                                                                            <div class="float-right text-right">
                                                                                                <span class="text-muted small">Total: </span>
                                                                                                <span class="font-weight-bold item-total-{{ $inst->id }}">{{ number_format($inst->total_items, 2) }}</span>
                                                                                                <div class="small text-danger item-mismatch-msg-{{ $inst->id }}" style="{{ $inst->isBalanced() ? 'display:none' : '' }}">
                                                                                                    Mismatch: {{ number_format($inst->fixed_amount - $inst->total_items, 2) }}
                                                                                                </div>
                                                                                            </div>
                                                                                        </td>
                                                                                    </tr>
                                                                                </tfoot>
                                                                            </table>
                                                                            <div class="text-right mt-2">
                                                                                <button type="submit" class="btn btn-primary btn-sm">Save Items</button>
                                                                            </div>
                                                                        </form>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        @else
                                                            <p class="text-muted text-center p-3">No installments defined for this term.</p>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-muted text-center p-3">Please define terms and installments first.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endif

@section('scripts')
    @parent
    @if(Qs::userIsTeamSA())
        <script>
            $(function () {
                // Existing button attachment logic
                var $btnWrapper = $('#create-structure-btn');
                if (!$btnWrapper.length) return;

                var inserted = false;
                var attachButton = function () {
                    if (inserted) return true;
                    var $wrapper = $('#structures-table').closest('.dataTables_wrapper');
                    if (!$wrapper.length) return false;
                    var $buttons = $wrapper.find('.dt-buttons');
                    if ($buttons.length) {
                        $btnWrapper.removeClass('d-none').addClass('ml-2');
                        $buttons.append($btnWrapper);
                        inserted = true;
                        return true;
                    }
                    var $length = $wrapper.find('.dataTables_length');
                    if ($length.length) {
                        $btnWrapper.removeClass('d-none').addClass('ml-3');
                        $length.after($btnWrapper);
                        inserted = true;
                        return true;
                    }
                    return false;
                };

                $('#structures-table').on('init.dt', function () { attachButton(); });
                if (!attachButton()) {
                    var attempts = 0;
                    var interval = setInterval(function () {
                        attempts++;
                        if (attachButton() || attempts > 50) clearInterval(interval);
                    }, 200);
                }
            });

            // GLOBAL WIZARD JAVASCRIPT
            function calculateTotalYear(structureId) {
                var total = 0;
                var $container = $('#terms-container-' + structureId);
                $container.find('.term-amount').each(function() {
                    total += parseFloat($(this).val()) || 0;
                });
                $('#total-year-' + structureId).text(total.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
            }

            // Generate term fields (Now Global)
            function generateTermFields(structureId, numTerms) {
                var container = $('#terms-container-' + structureId);
                container.empty();
                for (var i = 1; i <= numTerms; i++) {
                    var termHtml = `
                        <div class="card mb-2">
                            <div class="card-body p-3">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group mb-2">
                                            <label class="mb-1">Term ${i} Name</label>
                                            <input type="text" name="terms[${i-1}][name]" class="form-control form-control-sm" placeholder="Term ${i}" value="Term ${i}" required>
                                            <input type="hidden" name="terms[${i-1}][sequence]" value="${i}">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group mb-2">
                                            <label class="mb-1">Total Amount</label>
                                            <input type="number" name="terms[${i-1}][total_amount]" class="form-control form-control-sm term-amount" data-structure-id="${structureId}" step="0.01" min="0" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group mb-2">
                                            <label class="mb-1">Enable Installments?</label>
                                            <select name="terms[${i-1}][installments_enabled]" class="form-control form-control-sm">
                                                <option value="1" selected>Yes</option>
                                                <option value="0">No (Full Payment)</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    container.append(termHtml);
                }
                calculateTotalYear(structureId);
            }

            // Real-time calculation listener
            $(document).on('input', '.term-amount', function() {
                var structureId = $(this).data('structure-id');
                calculateTotalYear(structureId);
            });

            // Tab recalculation listener
            $(document).on('shown.bs.tab', 'a[data-toggle="tab"]', function (e) {
                var target = $(e.target).attr("href");
                if (target.indexOf('#terms-') !== -1) {
                    var structureId = target.split('-').pop();
                    calculateTotalYear(structureId);
                }
            });

             // Handle automatic tab progression after successful AJAX save
            $(document).on('ajax:success', 'form.ajax-update, form.ajax-store', function(e, data) {
                var $form = $(this);
                var $modal = $form.closest('.modal');
                var modalId = $modal.attr('id');
                if (!modalId) return;

                var targetModalId = null;
                var targetTabId = null;
                var targetAccordionId = null;

                if (modalId === 'modal-create-structure') {
                   // From Create -> Go to Edit Modal (Terms Tab)
                   targetModalId = 'modal-edit-structure-' + data.id;
                   targetTabId = '#terms-' + data.id;
                } else if (modalId.indexOf('modal-edit-structure-') !== -1) {
                    var $navTabs = $modal.find('.nav-tabs');
                    var $activeLink = $navTabs.find('a.active');
                    var currentTabId = $activeLink.attr('href');

                    // SPECIAL LOGIC: Installments Smart Progression
                    if (currentTabId && currentTabId.indexOf('#installments-') !== -1) {
                        // Get the term ID of the form we just saved
                        var justSavedTermId = $form.find('.add-installment-row').data('term-id');

                        // Scan for other unbalanced installments (Robust check that works even if accordion is closed)
                        var $nextPendingForm = $modal.find('.installment-config-form').filter(function() {
                            var btn = $(this).find('.add-installment-row');
                            var tId = btn.data('term-id');
                            
                            // 1. SKIP the form we just successfully saved (it might be cleared in the DOM by global handler)
                            if (justSavedTermId && tId == justSavedTermId) return false;

                            var tAmt = parseFloat(btn.data('term-amount'));
                            var currentSum = 0;
                            $(this).find('.installment-amount').each(function() {
                                currentSum += parseFloat($(this).val()) || 0;
                            });
                            // If sum doesn't match total, or there are NO installments at all, it's pending
                            return Math.abs(currentSum - tAmt) > 0.01 || $(this).find('.installment-row').length === 0;
                        }).first();

                        if ($nextPendingForm.length) {
                            // Stay on installments, but target this specific accordion
                            targetModalId = modalId;
                            targetTabId = currentTabId;
                            targetAccordionId = $nextPendingForm.closest('.collapse').attr('id');
                        }
                    }

                    // If no specific target set yet, go to next tab
                    if (!targetTabId) {
                        var $nextTabLink = $activeLink.parent().next('li').find('a');
                        if ($nextTabLink.length) {
                            targetModalId = modalId;
                            targetTabId = $nextTabLink.attr('href');
                        }
                    }
                }

                if (targetModalId && targetTabId) {
                    localStorage.setItem('reopen_wizard', JSON.stringify({
                        modalId: targetModalId,
                        tabId: targetTabId,
                        accordionId: targetAccordionId
                    }));
                    setTimeout(function() { location.reload(); }, 1000); 
                }
            });

            // Handle Re-open and field initialization
            $(document).ready(function() {
                // Initialize ALL existing containers
                $('[id^="terms-container-"]').each(function() {
                    var structureId = $(this).attr('id').split('-').pop();
                    if ($(this).text().trim() === '' && $(this).children().length === 0) {
                        generateTermFields(structureId, 2);
                    } else {
                        calculateTotalYear(structureId);
                    }
                });

                // Re-open handle
                var wizardState = localStorage.getItem('reopen_wizard');
                if (wizardState) {
                    wizardState = JSON.parse(wizardState);
                    var $modal = $('#' + wizardState.modalId);
                    if ($modal.length) {
                        $modal.modal('show');
                        $modal.on('shown.bs.modal', function() {
                             var $tabLink = $modal.find('a[href="' + wizardState.tabId + '"]');
                             if ($tabLink.length) $tabLink.tab('show');
                             
                             // Trigger initial data generation if tabs are empty
                             if (wizardState.tabId.indexOf('terms-') !== -1) {
                                 var sId = wizardState.tabId.split('-').pop();
                                 if ($('#terms-container-' + sId).is(':empty')) generateTermFields(sId, 2);
                             }
                             if (wizardState.tabId.indexOf('installments-') !== -1) {
                                 var sId = wizardState.tabId.split('-').pop();
                                 $('#installments-' + sId).find('.add-installment-row').each(function() {
                                     var tId = $(this).data('term-id');
                                     var $f = $('.installments-fields-' + tId);
                                     if ($f.children().length === 0 && $f.text().trim() === '') $(this).trigger('click');
                                     calculateInstallmentTotal(tId, $(this).data('term-amount'));
                                 });
                                 
                                 // Expand target accordion if specified
                                 if (wizardState.accordionId) {
                                     $('#' + wizardState.accordionId).collapse('show');
                                 }
                             }
                             localStorage.removeItem('reopen_wizard');
                        });
                    } else { localStorage.removeItem('reopen_wizard'); }
                }
                
                // Also trigger generation whenever installments tab is shown manually (only if empty)
                $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                    var target = $(e.target).attr("href");
                    if (target.indexOf('#installments-') !== -1) {
                        $(target).find('.add-installment-row').each(function() {
                             var termId = $(this).data('term-id');
                             var $f = $('.installments-fields-' + termId);
                             if ($f.children().length === 0 && $f.text().trim() === '') {
                                 $(this).trigger('click');
                             }
                             calculateInstallmentTotal(termId, $(this).data('term-amount'));
                        });
                    }
                });
            });
            // Installment Row Management
            $(document).on('click', '.add-installment-row', function() {
                var termId = $(this).data('term-id');
                var termAmount = parseFloat($(this).data('term-amount'));
                var container = $('.installments-fields-' + termId);
                var index = container.find('.installment-row').length;
                
                var html = `
                    <div class="installment-row mb-2 border p-2 rounded bg-white shadow-sm">
                        <div class="row align-items-end">
                            <div class="col-md-4">
                                <label class="mb-1 small font-weight-semibold">Label</label>
                                <input type="text" name="installments[${index}][label]" class="form-control form-control-sm" value="Installment ${index + 1}" required>
                                <input type="hidden" name="installments[${index}][sequence]" value="${index + 1}">
                            </div>
                            <div class="col-md-3">
                                <label class="mb-1 small font-weight-semibold">Amount</label>
                                <input type="number" name="installments[${index}][fixed_amount]" class="form-control form-control-sm installment-amount" data-term-id="${termId}" data-term-amount="${termAmount}" step="0.01" value="0.00" required>
                            </div>
                            <div class="col-md-3">
                                <label class="mb-1 small font-weight-semibold">Due Date</label>
                                <input type="date" name="installments[${index}][due_date]" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-md-2 text-right">
                                <button type="button" class="btn btn-outline-danger btn-sm border-0 remove-installment-row"><i class="icon-trash"></i></button>
                            </div>
                        </div>
                    </div>`;
                container.append(html);
                calculateInstallmentTotal(termId, termAmount);
            });

            $(document).on('click', '.remove-installment-row', function() {
                var container = $(this).closest('[class^="installments-fields-"]');
                var termId = container.attr('class').split('-').pop();
                var termAmount = parseFloat(container.closest('form').find('.add-installment-row').data('term-amount'));
                $(this).closest('.installment-row').remove();
                
                // Re-index
                container.find('.installment-row').each(function(i) {
                    $(this).find('input[name*="[label]"]').attr('name', `installments[${i}][label]`);
                    $(this).find('input[name*="[sequence]"]').attr('name', `installments[${i}][sequence]`).val(i + 1);
                    $(this).find('input[name*="[fixed_amount]"]').attr('name', `installments[${i}][fixed_amount]`);
                    $(this).find('input[name*="[due_date]"]').attr('name', `installments[${i}][due_date]`);
                });
                calculateInstallmentTotal(termId, termAmount);
            });

            $(document).on('input', '.installment-amount', function() {
                var termId = $(this).data('term-id');
                var termAmount = parseFloat($(this).data('term-amount'));
                calculateInstallmentTotal(termId, termAmount);
            });

            function calculateInstallmentTotal(termId, termAmount) {
                var total = 0;
                $(`.installment-amount[data-term-id="${termId}"]`).each(function() {
                    total += parseFloat($(this).val()) || 0;
                });
                
                $(`.installment-total-${termId}`).text(total.toLocaleString('en-US', {minimumFractionDigits: 2}));
                
                if (Math.abs(total - termAmount) < 0.01) {
                    $(`.validation-badge-${termId}`).show();
                    $(`.validation-error-${termId}`).hide();
                } else {
                    $(`.validation-badge-${termId}`).hide();
                    $(`.validation-error-${termId}`).show();
                }
            }

            // Initialize validation on load/tab switch
            $(document).on('shown.bs.tab', 'a[data-toggle="tab"]', function (e) {
                var target = $(e.target).attr("href");
                if (target.indexOf('#installments-') !== -1) {
                    $(target).find('.add-installment-row').each(function() {
                        calculateInstallmentTotal($(this).data('term-id'), $(this).data('term-amount'));
                    });
                }
            });

            // Item management logic (already global)
            $(document).on('click', '.add-item-row', function() {
                var instId = $(this).data('inst-id');
                var tbody = $('.item-rows-' + instId);
                var index = tbody.find('tr').length;
                var rowHtml = `<tr>
                    <td><select name="items[${index}][fee_item_id]" class="form-control form-control-sm select-fee-item" data-inst-id="${instId}" required><option value="">Select Category</option>@foreach($fee_categories as $fc)<option value="" data-name="{{ $fc->name }}">{{ $fc->name }}</option>@endforeach</select><input type="hidden" name="items[${index}][name]" value=""></td>
                    <td><input type="number" name="items[${index}][amount]" class="form-control form-control-sm item-amount" data-inst-id="${instId}" step="0.01" value="0.00" required></td>
                    <td><button type="button" class="btn btn-outline-danger btn-sm border-0 remove-item-row"><i class="icon-trash"></i></button></td>
                </tr>`;
                tbody.append(rowHtml);
                updateCategoryOptions(instId);
            });
            $(document).on('click', '.remove-item-row', function() {
                var tbody = $(this).closest('tbody');
                var instId = tbody.attr('class').split('-').pop();
                $(this).closest('tr').remove();
                tbody.find('tr').each(function(i) {
                    $(this).find('select').attr('name', `items[${i}][fee_item_id]`);
                    $(this).find('input[type="hidden"]').attr('name', `items[${i}][name]`);
                    $(this).find('input[type="number"]').attr('name', `items[${i}][amount]`);
                });
                calculateItemTotal(instId);
                updateCategoryOptions(instId);
            });
            $(document).on('change', '.select-fee-item', function() {
                var name = $(this).find(':selected').data('name');
                $(this).closest('td').find('input[type="hidden"]').val(name || '');
                updateCategoryOptions($(this).data('inst-id'));
            });
            $(document).on('input', '.item-amount', function() {
                calculateItemTotal($(this).data('inst-id'));
            });
            function calculateItemTotal(instId) {
                var total = 0; $(`.item-amount[data-inst-id="${instId}"]`).each(function() { total += parseFloat($(this).val()) || 0; });
                var headerText = $(`.item-total-${instId}`).closest('.card-body').prev('.card-header').find('h6').text();
                var instAmount = parseFloat(headerText.match(/\(([^)]+)\)/)[1].replace(/,/g, ''));
                $(`.item-total-${instId}`).text(total.toFixed(2));
                var diff = instAmount - total;
                if (Math.abs(diff) < 0.01) {
                    $(`.item-balance-badge-${instId}`).removeClass('badge-danger').addClass('badge-success').html('<i class="icon-checkmark4 mr-1"></i> Saved & Balanced');
                    $(`.item-mismatch-msg-${instId}`).hide();
                    $(`.item-balance-badge-${instId}`).closest('.card').removeClass('border-left-danger').addClass('border-left-success');
                } else {
                    $(`.item-balance-badge-${instId}`).removeClass('badge-success').addClass('badge-danger').text('Not Saved / Mismatch');
                    $(`.item-mismatch-msg-${instId}`).show().text('Mismatch: ' + diff.toFixed(2));
                    $(`.item-balance-badge-${instId}`).closest('.card').removeClass('border-left-success').addClass('border-left-danger');
                }
            }

            function updateCategoryOptions(instId) {
                var selectedNames = [];
                var $selects = $(`.select-fee-item[data-inst-id="${instId}"]`);
                
                // Collect all current selections
                $selects.each(function() {
                    var val = $(this).find(':selected').data('name');
                    if (val) selectedNames.push(val);
                });

                // Update each select
                $selects.each(function() {
                    var currentVal = $(this).find(':selected').data('name');
                    $(this).find('option').each(function() {
                        var optName = $(this).data('name');
                        if (!optName) return; // Skip placeholder
                        
                        if (selectedNames.includes(optName) && optName !== currentVal) {
                            $(this).hide().prop('disabled', true);
                        } else {
                            $(this).show().prop('disabled', false);
                        }
                    });
                });
            }

            // Also initialize on tab switch
            $(document).on('shown.bs.tab', 'a[data-toggle="tab"]', function (e) {
                var target = $(e.target).attr("href");
                if (target.indexOf('#items-') !== -1) {
                    $(target).find('.select-fee-item').each(function() {
                        updateCategoryOptions($(this).data('inst-id'));
                    });
                }
            });
        </script>
    @endif
@endsection

@endsection
```
