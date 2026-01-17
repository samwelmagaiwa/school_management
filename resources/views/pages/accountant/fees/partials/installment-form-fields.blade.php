<div class="form-row">
    <div class="form-group col-md-3">
        <label class="font-weight-semibold">#</label>
        <input type="number" name="sequence" class="form-control" min="1" value="{{ $sequence ?? old('sequence') }}" required>
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
<div class="form-row">
    <div class="form-group col-md-4">
        <label class="font-weight-semibold">Grace (days)</label>
        <input type="number" name="grace_days" min="0" class="form-control" value="{{ old('grace_days') }}">
    </div>
    <div class="form-group col-md-4">
        <label class="font-weight-semibold">Late Penalty</label>
        <select name="late_penalty_type" class="form-control">
            <option value="none">None</option>
            <option value="fixed">Fixed amount</option>
            <option value="percentage">Percentage</option>
        </select>
    </div>
    <div class="form-group col-md-4">
        <label class="font-weight-semibold">Penalty Value</label>
        <input type="number" step="0.01" name="late_penalty_value" class="form-control" value="{{ old('late_penalty_value') }}">
    </div>
</div>
