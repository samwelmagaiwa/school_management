@extends('layouts.master')
@section('page_title', 'Apply for Leave')
@section('content')

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title">New Leave Application</h6>
            {!! Qs::getPanelOptions() !!}
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('hr.leaves.store') }}" enctype="multipart/form-data">
                @csrf
                
                <div class="form-group row">
                    <label class="col-lg-3 col-form-label font-weight-semibold">Leave Type <span class="text-danger">*</span></label>
                    <div class="col-lg-9">
                        <select name="leave_type_id" class="form-control select" required>
                            <option value="">Select Type</option>
                            @foreach($types as $t)
                                <option value="{{ $t->id }}">{{ $t->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-lg-3 col-form-label font-weight-semibold">Start Date <span class="text-danger">*</span></label>
                    <div class="col-lg-9">
                        <input name="start_date" type="date" class="form-control" required>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-lg-3 col-form-label font-weight-semibold">End Date <span class="text-danger">*</span></label>
                    <div class="col-lg-9">
                        <input name="end_date" type="date" class="form-control" required>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-lg-3 col-form-label font-weight-semibold">Reason <span class="text-danger">*</span></label>
                    <div class="col-lg-9">
                        <textarea name="reason" class="form-control" rows="3" required placeholder="Reason for leave..."></textarea>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-lg-3 col-form-label font-weight-semibold">Attachment</label>
                    <div class="col-lg-9">
                        <input name="attachment" type="file" class="form-input-styled">
                        <span class="form-text text-muted">Document supporting your leave (PDF/Image)</span>
                    </div>
                </div>

                <div class="text-right">
                    <button type="submit" class="btn btn-primary">Submit Request</button>
                </div>
            </form>
        </div>
    </div>

@endsection
