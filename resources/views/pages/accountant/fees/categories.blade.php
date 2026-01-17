@extends('layouts.master')
@section('page_title', 'Fee Categories')
@section('content')

<div class="card">
    <div class="card-header header-elements-inline">
        <h6 class="card-title">Fee Categories</h6>
        {!! Qs::getPanelOptions() !!}
    </div>
    <div class="card-body">
        <ul class="nav nav-tabs nav-tabs-highlight">
            <li class="nav-item"><a href="#list" class="nav-link active" data-toggle="tab">Categories</a></li>
            <li class="nav-item"><a href="#create" class="nav-link" data-toggle="tab"><i class="icon-plus2"></i> New Category</a></li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="list">
                <table class="table datatable-button-html5-columns">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Code</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($categories as $category)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $category->name }}</td>
                                <td>{{ $category->code }}</td>
                                <td>{{ $category->description }}</td>
                                <td>
                                    <span class="badge badge-flat border-{{ $category->is_active ? 'success' : 'secondary' }} text-{{ $category->is_active ? 'success' : 'secondary' }}">
                                        {{ $category->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="list-icons">
                                        <div class="dropdown">
                                            <a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a>
                                            <div class="dropdown-menu dropdown-menu-left">
                                                <a href="#" class="dropdown-item" data-toggle="modal" data-target="#editCategoryModal" data-id="{{ $category->id }}" data-name="{{ $category->name }}" data-code="{{ $category->code }}" data-description="{{ $category->description }}" data-active="{{ $category->is_active }}"><i class="icon-pencil"></i> Edit</a>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="tab-pane fade" id="create">
                <div class="row">
                    <div class="col-md-6">
                        <form class="ajax-store" method="post" action="{{ route('accounting.fee-categories.store') }}">
                            @csrf
                            <div class="form-group row">
                                <label class="col-lg-3 col-form-label font-weight-semibold">Name <span class="text-danger">*</span></label>
                                <div class="col-lg-9">
                                    <input type="text" name="name" class="form-control" placeholder="e.g. Tuition" required>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-lg-3 col-form-label font-weight-semibold">Code</label>
                                <div class="col-lg-9">
                                    <input type="text" class="form-control" value="Auto-generated" disabled>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-lg-3 col-form-label font-weight-semibold">Description</label>
                                <div class="col-lg-9">
                                    <textarea name="description" class="form-control" rows="3" placeholder="Optional notes"></textarea>
                                </div>
                            </div>
                            <div class="text-right">
                                <button type="submit" class="btn btn-primary">Save Category <i class="icon-paperplane ml-2"></i></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="editCategoryModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Category</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form class="ajax-update" method="post" action="" data-modal="#editCategoryModal">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="name" class="form-control" placeholder="Category name" required>
                    </div>
                    <div class="form-group">
                        <label>Code</label>
                        <input type="text" class="form-control js-code-display" placeholder="Auto-generated" disabled>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Optional notes"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="is_active" class="form-control">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $('#editCategoryModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var id = button.data('id');
        var modal = $(this);
        modal.find('form').attr('action', '/accounting/fee-categories/' + id);
        modal.find('input[name="name"]').val(button.data('name'));
        modal.find('.js-code-display').val(button.data('code'));
        modal.find('textarea[name="description"]').val(button.data('description'));
        modal.find('select[name="is_active"]').val(button.data('active'));
    });
</script>
@endpush
@endsection
