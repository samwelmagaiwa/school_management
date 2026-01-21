@extends('layouts.master')
@section('page_title', 'Inventory Categories')
@section('content')

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title">Manage Categories</h6>
            {!! Qs::getPanelOptions() !!}
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <h6>Create New Category</h6>
                    <form method="post" class="ajax-store" action="{{ route('inventory.categories.store') }}">
                        @csrf
                        <div class="form-group">
                            <label>Name: <span class="text-danger">*</span></label>
                            <input type="text" name="name" required class="form-control" placeholder="e.g. Office Supplies">
                        </div>
                        <div class="form-group">
                            <label>Type: <span class="text-danger">*</span></label>
                            <select name="type" required class="form-control select">
                                <option value="general">General</option>
                                <option value="asset">Asset (Eqpt, Furniture)</option>
                                <option value="consumable">Consumable (Stationery, Food)</option>
                            </select>
                        </div>
                        <div class="text-right">
                            <button type="submit" class="btn btn-primary">Create</button>
                        </div>
                    </form>
                </div>
                <div class="col-md-8">
                    <table class="table datatable-button-html5-columns">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Description</th>
                                <th>Date Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($categories as $c)
                                <tr>
                                    <td>{{ $c->name }}</td>
                                    <td>{{ ucfirst($c->type) }}</td>
                                    <td>{{ $c->description }}</td>
                                    <td>{{ $c->created_at->format('d/m/Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

@endsection
