@extends('layouts.master')
@section('page_title', 'Fee Structures')
@section('content')

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

        <table class="table datatable-button-html5-columns" id="structures-table">
            <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Academic Year</th>
                <th>Period</th>
                <th>Due Date</th>
                <th>Status</th>
                <th class="text-center">Installments</th>
                <th class="text-center">Billing</th>
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
                        <span class="badge badge-flat border-{{ $structure->status === 'published' ? 'success' : ($structure->status === 'archived' ? 'secondary' : 'primary') }} text-{{ $structure->status === 'published' ? 'success' : ($structure->status === 'archived' ? 'secondary' : 'primary') }}">
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
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

@if(Qs::userIsTeamSA())
    <div class="modal fade" id="modal-create-structure" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="post" action="{{ route('accounting.fee-structures.store') }}">
                    @csrf
                    <div class="modal-header bg-primary text-white">
                        <h6 class="modal-title">Create Fee Structure</h6>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Academic Year</label>
                            <input type="text" name="academic_year" class="form-control" placeholder="e.g. 2025-2026" required>
                        </div>
                        <div class="form-group">
                            <label>Academic Period</label>
                            <select name="academic_period_id" class="form-control">
                                <option value="">None</option>
                                @foreach($periods as $period)
                                    <option value="{{ $period->id }}">{{ $period->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Due Date</label>
                            <input type="date" name="due_date" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" class="form-control" required>
                                <option value="draft">Draft</option>
                                <option value="published" selected>Published</option>
                                <option value="archived">Archived</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" rows="3" class="form-control"></textarea>
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
@endif

@section('scripts')
    @parent
    @if(Qs::userIsTeamSA())
        <script>
            $(function () {
                var $btnWrapper = $('#create-structure-btn');
                if (!$btnWrapper.length) {
                    return;
                }

                var inserted = false;
                var attachButton = function () {
                    if (inserted) {
                        return true;
                    }

                    var $wrapper = $('#structures-table').closest('.dataTables_wrapper');
                    if (! $wrapper.length) {
                        return false;
                    }

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

                $('#structures-table').on('init.dt', function () {
                    attachButton();
                });

                if (!attachButton()) {
                    var attempts = 0;
                    var interval = setInterval(function () {
                        attempts++;
                        if (attachButton() || attempts > 50) {
                            clearInterval(interval);
                        }
                    }, 200);
                }
            });
        </script>
    @endif
@endsection

@endsection
