@extends('layouts.master')
@section('page_title', 'Warehouses')
@section('content')

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title">Manage Warehouses</h6>
            {!! Qs::getPanelOptions() !!}
        </div>

        <div class="card-body">
             <ul class="nav nav-tabs nav-tabs-highlight">
                <li class="nav-item"><a href="#list" class="nav-link active" data-toggle="tab">List Warehouses</a></li>
                <li class="nav-item"><a href="#new" class="nav-link" data-toggle="tab">Create New</a></li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="list">
                     <table class="table datatable-button-html5-columns">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Location</th>
                                <th>Keeper</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($warehouses as $w)
                                <tr>
                                    <td>{{ $w->name }}</td>
                                    <td>{{ $w->location }}</td>
                                    <td>{{ $w->keeper->name ?? '-' }}</td>
                                    <td><span class="badge badge-success">Active</span></td>
                                    <td>
                                        <div class="dropdown">
                                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">Action</a>
                                            <div class="dropdown-menu">
                                                <a href="{{ route('inventory.warehouses.show', $w->id) }}" class="dropdown-item">View Stock</a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="tab-pane fade" id="new">
                    <form method="post" class="ajax-store" action="{{ route('inventory.warehouses.store') }}">
                        @csrf
                        <div class="form-group">
                            <label>Name:</label>
                            <input type="text" name="name" required class="form-control" placeholder="e.g. Main Store">
                        </div>
                        <div class="form-group">
                            <label>Location:</label>
                            <input type="text" name="location" class="form-control" placeholder="e.g. Block A, Room 101">
                        </div>
                        <button type="submit" class="btn btn-primary">Create</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
