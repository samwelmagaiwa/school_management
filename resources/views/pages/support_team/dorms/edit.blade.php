@extends('layouts.master')
@section('page_title', 'Edit Dorm - '.$dorm->name)
@section('content')

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title">Edit Dorm</h6>
            {!! Qs::getPanelOptions() !!}
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <form class="ajax-update" data-reload="#page-header" method="post" action="{{ route('dorms.update', $dorm->id) }}">
                        @csrf @method('PUT')
                        <div class="form-group row">
                            <label class="col-lg-3 col-form-label font-weight-semibold">Name <span class="text-danger">*</span></label>
                            <div class="col-lg-9">
                                <input name="name" value="{{ $dorm->name }}" required type="text" class="form-control" placeholder="Name of Dormitory">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-lg-3 col-form-label font-weight-semibold">Description</label>
                            <div class="col-lg-9">
                                <input name="description" value="{{ $dorm->description }}"  type="text" class="form-control" placeholder="Description of Dormitory">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-lg-3 col-form-label font-weight-semibold">Gender <span class="text-danger">*</span></label>
                            <div class="col-lg-9">
                                <select name="gender" class="form-control" required>
                                    <option value="mixed" {{ $dorm->gender === 'mixed' ? 'selected' : '' }}>Mixed</option>
                                    <option value="male" {{ $dorm->gender === 'male' ? 'selected' : '' }}>Male</option>
                                    <option value="female" {{ $dorm->gender === 'female' ? 'selected' : '' }}>Female</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-lg-3 col-form-label font-weight-semibold">Capacity</label>
                            <div class="col-lg-9">
                                <input name="capacity" value="{{ $dorm->capacity }}"  type="number" class="form-control" placeholder="Total Capacity">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-lg-3 col-form-label font-weight-semibold">Notes</label>
                            <div class="col-lg-9">
                                <textarea name="notes" class="form-control" rows="2" placeholder="Internal notes">{{ $dorm->notes }}</textarea>
                            </div>
                        </div>

                        <div class="text-right">
                            <button type="submit" class="btn btn-primary">Submit form <i class="icon-paperplane ml-2"></i></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{--Class Edit Ends--}}

@endsection
