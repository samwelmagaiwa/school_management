@extends('layouts.master')
@section('page_title', 'Requisitions')
@section('content')

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title">My Requisitions</h6>
            {!! Qs::getPanelOptions() !!}
        </div>

        <div class="card-body">
            <ul class="nav nav-tabs nav-tabs-highlight">
                <li class="nav-item"><a href="#list" class="nav-link active" data-toggle="tab">History</a></li>
                <li class="nav-item"><a href="#new" class="nav-link" data-toggle="tab">New Request</a></li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="list">
                    <table class="table datatable-button-html5-columns">
                        <thead>
                            <tr>
                                <th>Ref</th>
                                <th>Requester</th>
                                <th>Date Needed</th>
                                <th>Status</th>
                                <th>Items</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reqs as $r)
                                <tr>
                                    <td>{{ $r->reference_code }}</td>
                                    <td>{{ $r->requester->name }}</td>
                                    <td>{{ $r->date_needed }}</td>
                                    <td>
                                        <span class="badge badge-{{ $r->status == 'Pending' ? 'warning' : ($r->status == 'Approved' ? 'success' : 'danger') }}">
                                            {{ $r->status }}
                                        </span>
                                    </td>
                                    <td>{{ $r->items->count() }}</td>
                                    <td>
                                        @if($r->status == 'Pending' && (Auth::user()->user_type == 'super_admin' || Auth::user()->user_type == 'storekeeper'))
                                            <form method="post" action="{{ route('inventory.requisitions.approve', $r->id) }}" class="ajax-store">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                            </form>
                                        @elseif($r->status == 'Approved' && (Auth::user()->user_type == 'super_admin' || Auth::user()->user_type == 'storekeeper'))
                                            <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#issueModal{{ $r->id }}">Issue</button>
                                            
                                            <!-- Issue Modal -->
                                            <div class="modal fade" id="issueModal{{ $r->id }}" tabindex="-1" role="dialog">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Issue Stock for {{ $r->reference_code }}</h5>
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <form method="POST" action="{{ route('inventory.requisitions.issue', $r->id) }}">
                                                            @csrf
                                                            <div class="modal-body">
                                                                <div class="form-group">
                                                                    <label>Select Warehouse to Issue From:</label>
                                                                    <select name="warehouse_id" class="form-control" required>
                                                                        <option value="">-- Select Warehouse --</option>
                                                                        @foreach(\App\Models\Inventory\Warehouse::all() as $wh)
                                                                            <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                                <p class="text-muted"><small>Note: Stock will be deducted from the selected warehouse.</small></p>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                                <button type="submit" class="btn btn-primary">Confirm Issue</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @elseif($r->status == 'Issued')
                                            <span class="badge badge-info">Completed</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="tab-pane fade" id="new">
                    <!-- Simple Request Form -->
                    <form method="post" class="ajax-store" action="{{ route('inventory.requisitions.store') }}">
                        @csrf
                        <div class="form-group">
                            <label>Date Needed:</label>
                            <input type="text" name="date_needed" class="form-control date-pick" required>
                        </div>
                        <div class="form-group">
                            <label>Reason:</label>
                            <textarea name="reason" class="form-control" required placeholder="e.g. For computer lab maintenance"></textarea>
                        </div>
                        
                        <h6>Items (Add one Item Example)</h6>
                        <!-- In a real app, use dynamic JS to add rows. Here assume 1 item for MVP -->
                        <div class="row">
                            <div class="col-md-6">
                                <label>Item ID (Enter numeric ID for now):</label>
                                <input type="number" name="items[0][item_id]" class="form-control" required placeholder="e.g. 1">
                            </div>
                            <div class="col-md-6">
                                <label>Quantity:</label>
                                <input type="number" name="items[0][qty]" class="form-control" required placeholder="e.g. 5">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary mt-3">Submit Request</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
