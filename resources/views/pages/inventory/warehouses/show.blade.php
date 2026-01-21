@extends('layouts.master')
@section('page_title', 'Warehouse Details')
@section('content')

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title">{{ $warehouse->name }} - Stock Management</h6>
            {!! Qs::getPanelOptions() !!}
        </div>

        <div class="card-body">
            <h6>Location: {{ $warehouse->location }} | Keeper: {{ $warehouse->keeper->name ?? 'None' }}</h6>
            
            <ul class="nav nav-tabs nav-tabs-highlight">
                <li class="nav-item"><a href="#stock" class="nav-link active" data-toggle="tab">Current Stock (Consumables)</a></li>
                <li class="nav-item"><a href="#assets" class="nav-link" data-toggle="tab">Fixed Assets</a></li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="stock">
                    <table class="table datatable-button-html5-columns">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Quantity</th>
                                <th>Batch</th>
                                <th>Expiry</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($warehouse->stocks as $s)
                                <tr>
                                    <td>{{ $s->item->name }} ({{ $s->item->code }})</td>
                                    <td>{{ $s->quantity }} {{ $s->item->unit->abbreviation ?? '' }}</td>
                                    <td>{{ $s->batch_number ?? '-' }}</td>
                                    <td>{{ $s->expiry_date ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="tab-pane fade" id="assets">
                    <table class="table datatable-button-html5-columns">
                         <thead>
                            <tr>
                                <th>Asset Tag</th>
                                <th>Item</th>
                                <th>Serial</th>
                                <th>Condition</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                         <tbody>
                            @foreach($warehouse->assets as $a)
                                <tr>
                                    <td>{{ $a->unique_tag }}</td>
                                    <td>{{ $a->item->name }}</td>
                                    <td>{{ $a->serial_number }}</td>
                                    <td>{{ $a->condition }}</td>
                                    <td>{{ $a->status }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

@endsection
