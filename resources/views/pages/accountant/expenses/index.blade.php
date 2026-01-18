@extends('layouts.master')
@section('page_title', 'Expenses & Vendors')
@section('content')

<div class="card">
    <div class="card-header header-elements-inline">
        <h6 class="card-title">Expenses & Vendors</h6>
        {!! Qs::getPanelOptions() !!}
    </div>
    <div class="card-body">
        <ul class="nav nav-tabs nav-tabs-highlight">
            <li class="nav-item"><a href="#expenses-list" class="nav-link active" data-toggle="tab">Expenses</a></li>
            <li class="nav-item"><a href="#vendors-list" class="nav-link" data-toggle="tab">Manage Vendors</a></li>
        </ul>

        <div class="tab-content">
            {{-- EXPENSES TAB --}}
            <div class="tab-pane fade show active" id="expenses-list">
                <div class="d-flex justify-content-end align-items-center mb-3">
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal-create-expense">
                        <i class="icon-plus2 mr-1"></i> Record Expense
                    </button>
                </div>

                <table class="table datatable-button-html5-columns">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Vendor</th>
                        <th>Category</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Reference</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($expenses as $expense)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $expense->title }}</td>
                            <td>{{ optional($expense->vendor)->name }}</td>
                            <td>{{ $expense->category }}</td>
                            <td>{{ Qs::formatCurrency($expense->amount) }}</td>
                            <td>{{ optional($expense->expense_date)->format('Y-m-d') }}</td>
                            <td>{{ $expense->reference ?? '-' }}</td>
                            <td>
                                <span class="badge badge-{{ $expense->status == 'approved' || $expense->status == 'paid' ? 'success' : ($expense->status == 'cancelled' ? 'danger' : 'warning') }}">
                                    {{ ucfirst($expense->status) }}
                                </span>
                            </td>
                            <td class="text-right">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle px-2" type="button" data-toggle="dropdown">
                                        &vellip;
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right">
                                        <button type="button" class="dropdown-item" data-toggle="modal" data-target="#modal-edit-expense"
                                                data-route="{{ route('accounting.expenses.update', $expense->id) }}"
                                                data-title="{{ $expense->title }}"
                                                data-amount="{{ $expense->amount }}"
                                                data-date="{{ optional($expense->expense_date)->format('Y-m-d') }}"
                                                data-category="{{ $expense->category }}"
                                                data-vendor="{{ $expense->vendor_id }}"
                                                data-reference="{{ $expense->reference }}"
                                                data-note="{{ $expense->note }}"
                                                data-status="{{ $expense->status }}">
                                            Edit
                                        </button>
                                        <button type="button" class="dropdown-item text-danger" onclick="confirmDelete('{{ route('accounting.expenses.destroy', $expense->id) }}')">
                                            Delete
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            {{-- VENDORS TAB --}}
            <div class="tab-pane fade" id="vendors-list">
                <div class="row">
                    <div class="col-md-4">
                        <div class="card border-primary">
                            <div class="card-header bg-primary text-white header-elements-inline">
                                <h6 class="card-title">Add New Vendor</h6>
                            </div>
                            <div class="card-body">
                                <form class="ajax-store" method="post" action="{{ route('accounting.vendors.store') }}">
                                    @csrf
                                    <div class="form-group">
                                        <label>Vendor Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Contact Person</label>
                                        <input type="text" name="contact_person" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label>Phone</label>
                                        <input type="text" name="phone" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label>Email</label>
                                        <input type="email" name="email" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label>Address</label>
                                        <textarea name="address" class="form-control" rows="3"></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-block">Add Vendor</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8">
                         <table class="table datatable-button-html5-columns">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Contact</th>
                                <th>Phone</th>
                                <th class="text-right">Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($vendors as $vendor)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $vendor->name }}</td>
                                    <td>{{ $vendor->contact_person ?? '-' }}</td>
                                    <td>{{ $vendor->phone ?? '-' }}</td>
                                    <td class="text-right">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle px-2" type="button" data-toggle="dropdown">
                                                &vellip;
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                <button type="button" class="dropdown-item" data-toggle="modal" data-target="#modal-edit-vendor"
                                                        data-route="{{ route('accounting.vendors.update', $vendor->id) }}"
                                                        data-name="{{ $vendor->name }}"
                                                        data-contact="{{ $vendor->contact_person }}"
                                                        data-phone="{{ $vendor->phone }}"
                                                        data-email="{{ $vendor->email }}"
                                                        data-address="{{ $vendor->address }}">
                                                    Edit
                                                </button>
                                                <button type="button" class="dropdown-item text-danger" onclick="confirmDelete('{{ route('accounting.vendors.destroy', $vendor->id) }}')">
                                                    Delete
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- CREATE EXPENSE MODAL --}}
<div class="modal fade" id="modal-create-expense" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h6 class="modal-title">Record New Expense</h6>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form class="ajax-store" method="post" action="{{ route('accounting.expenses.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control" required placeholder="e.g. Office Supplies">
                            </div>
                        </div>
                        <div class="col-md-6">
                             <div class="form-group">
                                <label>Category <span class="text-danger">*</span></label>
                                <select name="category" class="form-control select" required>
                                    <option value="General">General</option>
                                    <option value="Maintenance">Maintenance</option>
                                    <option value="Utilities">Utilities</option>
                                    <option value="Salaries">Salaries</option>
                                    <option value="Transport">Transport</option>
                                    <option value="Events">Events</option>
                                    <option value="Others">Others</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Amount <span class="text-danger">*</span></label>
                                <input type="number" name="amount" class="form-control" required step="0.01" min="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Date <span class="text-danger">*</span></label>
                                <input type="date" name="expense_date" class="form-control" required value="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Vendor</label>
                                <select name="vendor_id" class="form-control select-search">
                                    <option value="">Select Vendor (Optional)</option>
                                    @foreach($vendors as $v)
                                        <option value="{{ $v->id }}">{{ $v->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Reference</label>
                                <input type="text" name="reference" class="form-control" placeholder="Receipt No.">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Description / Note</label>
                        <textarea name="note" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control select">
                            <option value="pending">Pending Approval</option>
                            <option value="approved" selected>Approved</option>
                            <option value="paid">Paid</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Submit Expense</button>
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- EDIT EXPENSE MODAL --}}
<div class="modal fade" id="modal-edit-expense" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
             <div class="modal-header bg-primary text-white">
                <h6 class="modal-title">Edit Expense</h6>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form class="ajax-update" method="post" action="">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                             <div class="form-group">
                                <label>Category <span class="text-danger">*</span></label>
                                <select name="category" class="form-control select" required>
                                    <option value="General">General</option>
                                    <option value="Maintenance">Maintenance</option>
                                    <option value="Utilities">Utilities</option>
                                    <option value="Salaries">Salaries</option>
                                    <option value="Transport">Transport</option>
                                    <option value="Events">Events</option>
                                    <option value="Others">Others</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Amount <span class="text-danger">*</span></label>
                                <input type="number" name="amount" class="form-control" required step="0.01" min="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Date <span class="text-danger">*</span></label>
                                <input type="date" name="expense_date" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Vendor</label>
                                <select name="vendor_id" class="form-control select-search">
                                    <option value="">Select Vendor (Optional)</option>
                                    @foreach($vendors as $v)
                                        <option value="{{ $v->id }}">{{ $v->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Reference</label>
                                <input type="text" name="reference" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Description / Note</label>
                        <textarea name="note" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control select">
                            <option value="pending">Pending Approval</option>
                            <option value="approved">Approved</option>
                            <option value="paid">Paid</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Update Expense</button>
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- EDIT VENDOR MODAL --}}
<div class="modal fade" id="modal-edit-vendor" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
             <div class="modal-header bg-primary text-white">
                <h6 class="modal-title">Edit Vendor</h6>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form class="ajax-update" method="post" action="">
                 @csrf @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label>Vendor Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Contact Person</label>
                        <input type="text" name="contact_person" class="form-control">
                    </div>
                     <div class="form-group">
                        <label>Phone</label>
                        <input type="text" name="phone" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Address</label>
                        <textarea name="address" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Update Vendor</button>
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Delete Form --}}
<form id="delete-form" method="POST" style="display: none;">
    @csrf @method('DELETE')
</form>

@endsection

@section('scripts')
<script>
    $('#modal-edit-expense').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var modal = $(this);
        
        modal.find('form').attr('action', button.data('route'));
        modal.find('input[name="title"]').val(button.data('title'));
        modal.find('input[name="amount"]').val(button.data('amount'));
        modal.find('input[name="expense_date"]').val(button.data('date'));
        modal.find('select[name="category"]').val(button.data('category')).trigger('change');
        modal.find('select[name="vendor_id"]').val(button.data('vendor')).trigger('change');
        modal.find('input[name="reference"]').val(button.data('reference'));
        modal.find('textarea[name="note"]').val(button.data('note'));
        modal.find('select[name="status"]').val(button.data('status')).trigger('change');
    });

    $('#modal-edit-vendor').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var modal = $(this);
        
        modal.find('form').attr('action', button.data('route'));
        modal.find('input[name="name"]').val(button.data('name'));
        modal.find('input[name="contact_person"]').val(button.data('contact'));
        modal.find('input[name="phone"]').val(button.data('phone'));
        modal.find('input[name="email"]').val(button.data('email'));
        modal.find('textarea[name="address"]').val(button.data('address'));
    });

    function confirmDelete(route) {
        if(confirm('Are you sure you want to delete this specific record?')) {
            var form = document.getElementById('delete-form');
            form.action = route;
            form.submit();
        }
    }
</script>
@endsection
