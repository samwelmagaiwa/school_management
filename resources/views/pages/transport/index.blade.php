@extends('layouts.master')
@section('page_title', 'Transport Management')
@section('content')

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title">Manage Vehicles & Trips</h6>
            {!! Qs::getPanelOptions() !!}
        </div>

        <div class="card-body">
            <ul class="nav nav-tabs nav-tabs-highlight">
                <li class="nav-item"><a href="#vehicles" class="nav-link active" data-toggle="tab">Vehicles</a></li>
                <li class="nav-item"><a href="#trips" class="nav-link" data-toggle="tab">Record Trip</a></li>
                <li class="nav-item"><a href="#fuel" class="nav-link" data-toggle="tab">Log Fuel</a></li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="vehicles">
                    <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#new-vehicle">New Vehicle</button>
                    <table class="table datatable-button-html5-columns">
                        <thead>
                            <tr>
                                <th>Plate</th>
                                <th>Type</th>
                                <th>Make/Model</th>
                                <th>Driver</th>
                                <th>Mileage</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($vehicles as $v)
                                <tr>
                                    <td>{{ $v->plate_number }}</td>
                                    <td>{{ $v->type }}</td>
                                    <td>{{ $v->make }} {{ $v->model }}</td>
                                    <td>{{ $v->driver->name ?? 'Unassigned' }}</td>
                                    <td>{{ $v->current_mileage }} km</td>
                                    <td>{{ $v->status }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="tab-pane fade" id="trips">
                     <form method="post" class="ajax-store" action="{{ route('transport.trips.store') }}">
                        @csrf
                        <div class="row">
                             <div class="col-md-6">
                                <label>Vehicle:</label>
                                <select name="vehicle_id" required class="form-control select">
                                    <option value="">Select</option>
                                    @foreach($vehicles as $v)
                                    <option value="{{ $v->id }}">{{ $v->plate_number }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label>Destination:</label>
                                <input type="text" name="destination" required class="form-control" placeholder="e.g. City Center">
                            </div>
                        </div>
                        <div class="form-group mt-2">
                             <label>Purpose:</label>
                             <input type="text" name="purpose" required class="form-control" placeholder="e.g. Student Excursion">
                        </div>
                        <div class="row">
                             <div class="col-md-6">
                                <label>Start Odometer:</label>
                                <input type="number" step="0.1" name="start_odometer" required class="form-control" placeholder="e.g. 15000">
                             </div>
                             <div class="col-md-6">
                                <label>Date:</label>
                                <input type="text" name="departure_time" required class="form-control date-pick" value="{{ date('m/d/Y') }}">
                             </div>
                        </div>
                        <button type="submit" class="btn btn-primary mt-3">Book Trip</button>
                     </form>
                </div>

                <div class="tab-pane fade" id="fuel">
                    <form method="post" class="ajax-store" action="{{ route('transport.fuel.store') }}">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <label>Vehicle:</label>
                                <select name="vehicle_id" required class="form-control">
                                    <option value="">-- Select Vehicle --</option>
                                    @foreach($vehicles as $v)
                                        <option value="{{ $v->id }}">{{ $v->plate_number }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label>Date:</label>
                                <input type="date" name="date" required class="form-control" value="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-4">
                                <label>Fuel Quantity (Liters):</label>
                                <input type="number" step="0.01" name="liters" required class="form-control" placeholder="e.g. 40">
                            </div>
                            <div class="col-md-4">
                                <label>Cost Per Liter:</label>
                                <input type="number" step="0.01" name="cost_per_liter" required class="form-control" placeholder="e.g. 2500">
                            </div>
                            <div class="col-md-4">
                                <label>Odometer Reading:</label>
                                <input type="number" name="odometer_reading" required class="form-control" placeholder="e.g. 15000">
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <label>Invoice Number:</label>
                                <input type="text" name="invoice_number" class="form-control" placeholder="Optional">
                            </div>
                            <div class="col-md-6">
                                <label>Notes:</label>
                                <textarea name="notes" class="form-control" placeholder="Optional notes"></textarea>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary mt-3">Log Fuel</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- New Vehicle Modal --}}
    <div id="new-vehicle" class="modal fade" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header"><h5>Register Vehicle</h5></div>
                <form method="post" class="ajax-store" action="{{ route('transport.vehicles.store') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group"><label>Plate Number</label><input type="text" name="plate_number" required class="form-control" placeholder="ABC 123"></div>
                        <div class="form-group"><label>Type</label><select name="type" class="form-control"><option>Bus</option><option>Van</option><option>Car</option></select></div>
                         <div class="form-group"><label>Make</label><input type="text" name="make" class="form-control" placeholder="Toyota"></div>
                         <div class="form-group"><label>Model</label><input type="text" name="model" class="form-control" placeholder="Hiace"></div>
                    </div>
                     <div class="modal-footer"><button type="submit" class="btn btn-primary">Save</button></div>
                </form>
            </div>
        </div>
    </div>

@endsection
