@extends('layouts.master')
@section('page_title', 'Edit Staff Member')
@section('content')

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title">Edit Staff Details - {{ $staff->name }}</h6>
            {!! Qs::getPanelOptions() !!}
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('hr.staff.update', $staff->id) }}" enctype="multipart/form-data">
                @csrf @method('PUT')
                
                <fieldset>
                    <legend class="font-weight-semibold text-uppercase font-size-sm">Personal Details</legend>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Full Name: <span class="text-danger">*</span></label>
                                <input type="text" name="name" value="{{ $staff->name }}" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Email Address: <span class="text-danger">*</span></label>
                                <input type="email" name="email" value="{{ $staff->email }}" class="form-control" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Phone Number:</label>
                                <input type="text" name="phone" value="{{ $staff->phone }}" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Gender: <span class="text-danger">*</span></label>
                                <select class="select form-control" name="gender" required>
                                    <option value="Male" {{ $staff->gender == 'Male' ? 'selected' : '' }}>Male</option>
                                    <option value="Female" {{ $staff->gender == 'Female' ? 'selected' : '' }}>Female</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Date of Birth:</label>
                                <input type="date" name="dob" value="{{ $staff->dob }}" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Passport Photo:</label>
                                <input type="file" name="photo" class="form-input-styled">
                            </div>
                        </div>
                    </div>
                        <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Address:</label>
                                <input type="text" name="address" value="{{ $staff->address }}" class="form-control">
                            </div>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="mt-3">
                    <legend class="font-weight-semibold text-uppercase font-size-sm">Employment Details</legend>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Department: <span class="text-danger">*</span></label>
                                <select class="select form-control" name="department_id" required>
                                    <option value="">Select Department</option>
                                    @foreach($departments as $d)
                                        <option value="{{ $d->id }}" {{ ($staff->staff_record && $staff->staff_record->department_id == $d->id) ? 'selected' : '' }}>{{ $d->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Designation: <span class="text-danger">*</span></label>
                                <select class="select form-control" name="designation_id" required>
                                    <option value="">Select Designation</option>
                                    @foreach($designations as $d)
                                        <option value="{{ $d->id }}" {{ ($staff->staff_record && $staff->staff_record->designation_id == $d->id) ? 'selected' : '' }}>{{ $d->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Employment Type: <span class="text-danger">*</span></label>
                                <select class="select form-control" name="employment_type" required>
                                    <option value="">Select Type</option>
                                    @foreach(['Full Time', 'Part Time', 'Contract', 'Intern'] as $type)
                                            <option value="{{ $type }}" {{ ($staff->staff_record && $staff->staff_record->employment_type == $type) ? 'selected' : '' }}>{{ $type }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Date of Hire: <span class="text-danger">*</span></label>
                                <input type="date" name="date_of_hire" value="{{ $staff->staff_record->date_of_hire ?? '' }}" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Basic Salary:</label>
                                <input type="number" name="basic_salary" value="{{ $staff->staff_record->basic_salary ?? '' }}" class="form-control" step="0.01">
                            </div>
                        </div>
                    </div>
                </fieldset>

                <div class="text-right mt-3">
                    <button type="submit" class="btn btn-primary">Update Staff</button>
                </div>
            </form>
        </div>
    </div>

@endsection
