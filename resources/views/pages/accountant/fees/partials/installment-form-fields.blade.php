<div class="form-row">
    <div class="form-group col-md-3">
        <label class="font-weight-semibold">#</label>
        <input type="number" name="sequence" class="form-control" min="1" value="{{ $sequence ?? old('sequence') }}" required readonly>
    </div>
    <div class="form-group col-md-9">
        <label class="font-weight-semibold">Label</label>
        <input type="text" name="label" class="form-control" value="{{ old('label') }}" required>
    </div>
</div>
<div class="form-row">
    <div class="form-group col-md-4">
        <label class="font-weight-semibold">Percentage</label>
        <input type="number" step="0.01" name="percentage" class="form-control" value="{{ old('percentage') }}">
    </div>
    <div class="form-group col-md-4">
        <label class="font-weight-semibold">Fixed Amount</label>
        <input type="number" step="0.01" name="fixed_amount" class="form-control" value="{{ old('fixed_amount') }}">
    </div>
    <div class="form-group col-md-4">
        <label class="font-weight-semibold">Due Date</label>
        <input type="date" name="due_date" class="form-control" value="{{ old('due_date') }}">
    </div>
</div>

