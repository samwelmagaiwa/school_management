@extends('layouts.master')
@section('page_title', 'Book Categories')
@section('content')

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title"><i class="icon-price-tags2 mr-2"></i> Manage Book Categories</h6>
            {!! Qs::getPanelOptions() !!}
        </div>

        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <form method="post" action="{{ route('library.categories.store') }}" class="ajax-store" data-fouc>
                        @csrf
                        <div class="form-group mb-2">
                            <label class="font-weight-semibold">Add New Category</label>
                            <div class="input-group">
                                <input type="text" name="name" class="form-control" placeholder="e.g. Textbook, Reference, Fiction" required>
                                <span class="input-group-append">
                                    <button type="submit" class="btn btn-primary">Add</button>
                                </span>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table datatable-button-html5-columns">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th class="text-center">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($categories as $category)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $category->name }}</td>
                            <td class="text-center">
                                <button type="button"
                                        class="btn btn-sm btn-outline-primary"
                                        onclick="openEditCategoryModal({{ $category->id }}, '{{ addslashes($category->name) }}')">
                                    Edit
                                </button>
                                <form method="post" action="{{ route('library.categories.destroy', $category->id) }}" class="d-inline" id="delete-category-{{ $category->id }}">
                                    @csrf @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-danger" onclick="confirmDeleteCategory({{ $category->id }})">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Edit Category Modal -->
    <div id="editCategoryModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="editCategoryLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h6 class="modal-title" id="editCategoryLabel">Edit Category</h6>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="post" id="editCategoryForm" class="ajax-update" data-fouc>
                    @csrf @method('PUT')
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="edit_category_name">Name</label>
                            <input type="text" name="name" id="edit_category_name" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @parent
    <script>
        function openEditCategoryModal(id, name) {
            var form = document.getElementById('editCategoryForm');
            form.action = '{{ url('library/categories') }}/' + id;
            document.getElementById('edit_category_name').value = name;
            $('#editCategoryModal').modal('show');
        }

        function confirmDeleteCategory(id) {
            swal({
                title: "Delete this category?",
                text: "This action cannot be undone. You cannot delete a category that is still used by books.",
                icon: "warning",
                buttons: true,
                dangerMode: true
            }).then(function(willDelete) {
                if (willDelete) {
                    document.getElementById('delete-category-' + id).submit();
                }
            });
        }
    </script>
@endsection
