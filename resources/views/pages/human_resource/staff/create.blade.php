@extends('layouts.master')
@section('page_title', 'Add Staff Member')
@section('content')

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title">Fill Staff Details</h6>
            {!! Qs::getPanelOptions() !!}
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('hr.staff.store') }}" enctype="multipart/form-data">
                @csrf
                
                <fieldset>
                    <legend class="font-weight-semibold text-uppercase font-size-sm">Personal Details</legend>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Full Name: <span class="text-danger">*</span></label>
                                <input type="text" name="name" value="{{ old('name') }}" class="form-control" required placeholder="John Doe">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Email Address: <span class="text-danger">*</span></label>
                                <input type="email" name="email" value="{{ old('email') }}" class="form-control" required placeholder="john@example.com">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Phone Number:</label>
                                <input type="text" name="phone" value="{{ old('phone') }}" class="form-control" placeholder="+255 700 000 000">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Gender: <span class="text-danger">*</span></label>
                                <select class="select form-control" name="gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Date of Birth:</label>
                                <input type="date" name="dob" value="{{ old('dob') }}" class="form-control">
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
                                <input type="text" name="address" value="{{ old('address') }}" class="form-control" placeholder="Residential Address">
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
                                        <option value="{{ $d->id }}">{{ $d->name }}</option>
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
                                        <option value="{{ $d->id }}">{{ $d->name }}</option>
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
                                    <option value="Full Time">Full Time</option>
                                    <option value="Part Time">Part Time</option>
                                    <option value="Contract">Contract</option>
                                    <option value="Intern">Intern</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Date of Hire: <span class="text-danger">*</span></label>
                                <input type="date" name="date_of_hire" value="{{ old('date_of_hire') }}" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Basic Salary:</label>
                                <input type="number" name="basic_salary" value="{{ old('basic_salary') }}" class="form-control" step="0.01">
                            </div>
                        </div>
                    </div>
                </fieldset>

                <div class="text-right mt-3">
                    <button type="submit" class="btn btn-primary">Create Staff Member</button>
                </div>
            </form>
        </div>
    </div>

@endsection
