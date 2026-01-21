@extends('layouts.master')
@section('page_title', 'Receive Stock')
@section('content')

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title font-weight-semibold">Receive Stock Into Warehouse</h6>
            {!! Qs::getPanelOptions() !!}
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('inventory.stocks.store') }}">
                @csrf
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="item_id">Item <span class="text-danger">*</span></label>
                            <select required data-placeholder="Select Item" class="form-control select" name="item_id" id="item_id">
                                <option value="">-- Select Item --</option>
                                @foreach($items as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }} ({{ $item->code }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="warehouse_id">Warehouse <span class="text-danger">*</span></label>
                            <select required data-placeholder="Select Warehouse" class="form-control select" name="warehouse_id" id="warehouse_id">
                                <option value="">-- Select Warehouse --</option>
                                @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="quantity">Quantity <span class="text-danger">*</span></label>
                            <input required type="number" name="quantity" id="quantity" class="form-control" placeholder="Enter quantity" min="1">
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="batch_number">Batch Number</label>
                            <input type="text" name="batch_number" id="batch_number" class="form-control" placeholder="e.g., BATCH-2026-001">
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="expiry_date">Expiry Date</label>
                            <input type="date" name="expiry_date" id="expiry_date" class="form-control" min="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="unit_price">Unit Price (Optional)</label>
                            <input type="number" step="0.01" name="unit_price" id="unit_price" class="form-control" placeholder="e.g., 150.00" min="0">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="reference">Reference Number</label>
                            <input type="text" name="reference" id="reference" class="form-control" placeholder="e.g., PO-2026-001">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="description">Description/Notes</label>
                            <textarea name="description" id="description" rows="3" class="form-control" placeholder="Enter any additional notes or description"></textarea>
                        </div>
                    </div>
                </div>

                <div class="text-right">
                    <button type="submit" class="btn btn-primary">Receive Stock <i class="icon-paperplane ml-2"></i></button>
                </div>
            </form>
        </div>
    </div>

@endsection
