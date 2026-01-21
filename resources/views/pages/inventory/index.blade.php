@extends('layouts.master')
@section('page_title', 'Inventory Items')
@section('content')

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title">Manage Items</h6>
            {!! Qs::getPanelOptions() !!}
        </div>

        <div class="card-body">
            <ul class="nav nav-tabs nav-tabs-highlight">
                <li class="nav-item"><a href="#all-items" class="nav-link active" data-toggle="tab">Manage Items</a></li>
                <li class="nav-item"><a href="#new-item" class="nav-link" data-toggle="tab"><i class="icon-plus2"></i> Create New Item</a></li>
            </ul>

            <div class="tab-content">
                    <div class="tab-pane fade show active" id="all-items">
                        <table class="table datatable-button-html5-columns">
                            <thead>
                            <tr>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Type</th>
                                <th>Unit</th>
                                <th>Reorder Level</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($items as $i)
                                <tr>
                                    <td>{{ $i->code }}</td>
                                    <td>{{ $i->name }}</td>
                                    <td>{{ $i->category->name }}</td>
                                    <td>
                                        <span class="badge badge-{{ $i->is_asset ? 'info' : 'success' }}">
                                            {{ $i->is_asset ? 'Asset' : 'Consumable' }}
                                        </span>
                                    </td>
                                    <td>{{ $i->unit->abbreviation ?? '-' }}</td>
                                    <td>{{ $i->reorder_level }}</td>
                                    <td class="text-center">
                                        <div class="list-icons">
                                            <div class="dropdown">
                                                <a href="#" class="list-icons-item" data-toggle="dropdown">
                                                    <i class="icon-menu9"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-left">
                                                    <a href="#" class="dropdown-item" data-toggle="modal" data-target="#edit-item-{{ $i->id }}"><i class="icon-pencil"></i> Edit</a>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="tab-pane fade" id="new-item">
                        <form method="post" class="ajax-store" action="{{ route('inventory.items.store') }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Item Name: <span class="text-danger">*</span></label>
                                        <input type="text" name="name" required class="form-control" placeholder="e.g. White Board Marker">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Category: <span class="text-danger">*</span></label>
                                        <select required name="category_id" class="form-control select">
                                            <option value="">Select Category</option>
                                            @foreach($categories as $c)
                                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Unit: </label>
                                        <select name="unit_id" class="form-control select">
                                            <option value="">Select Unit</option>
                                            @foreach($units as $u)
                                                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->abbreviation }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Is Fixed Asset?</label>
                                        <select name="is_asset" class="form-control select">
                                            <option value="0">No (Consumable)</option>
                                            <option value="1">Yes (Fixed Asset)</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Reorder Level: </label>
                                        <input type="number" name="reorder_level" class="form-control" value="10">
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <button type="submit" class="btn btn-primary">Create Item <i class="icon-paperplane ml-2"></i></button>
                            </div>
                        </form>
                    </div>
            </div>
        </div>
    </div>

    {{-- Edit Modals --}}
    @foreach($items as $i)
    <div id="edit-item-{{ $i->id }}" class="modal fade" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Item</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form method="post" class="ajax-update" action="{{ route('inventory.items.update', $i->id) }}">
                    @csrf @method('PUT')
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Name:</label>
                            <input type="text" name="name" value="{{ $i->name }}" required class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Category:</label>
                            <select required name="category_id" class="form-control select-search fa-select">
                                @foreach($categories as $c)
                                    <option {{ $i->category_id == $c->id ? 'selected' : '' }} value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Reorder Level:</label>
                            <input type="number" name="reorder_level" value="{{ $i->reorder_level }}" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endforeach

@endsection
