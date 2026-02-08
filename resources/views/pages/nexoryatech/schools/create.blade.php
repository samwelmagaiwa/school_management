@extends('layouts.master')
@section('page_title', 'Create New School')
@section('content')

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title font-weight-semibold">Register New School</h6>
            {!! Qs::getPanelOptions() !!}
        </div>

        <form method="POST" action="{{ route('nexoryatech.schools.store') }}">
            @csrf
            
            <div class="card-body">
                <fieldset>
                    <legend class="font-weight-semibold"><i class="icon-office mr-2"></i> School Information</legend>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-semibold">School Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" value="{{ old('name') }}" required class="form-control" placeholder="e.g. St. John's Secondary School">
                                @error('name')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-semibold">School Code <span class="text-danger">*</span></label>
                                <input type="text" name="school_code" value="{{ old('school_code') }}" required class="form-control text-uppercase" placeholder="e.g. STJOHNS" maxlength="50">
                                <span class="form-text text-muted">Uppercase letters, numbers, and underscores only. Used for login.</span>
                                @error('school_code')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-semibold">Status <span class="text-danger">*</span></label>
                                <select name="status" required class="form-control select">
                                    <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @error('status')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </fieldset>
                
                <fieldset class="mt-4">
                    <legend class="font-weight-semibold"><i class="icon-user mr-2"></i> First SuperAdmin Account</legend>
                    
                    <div class="alert alert-info border-0">
                        <i class="icon-info22 mr-2"></i>
                        This account will have full administrative access to manage the school.
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-semibold">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="admin_name" value="{{ old('admin_name') }}" required class="form-control" placeholder="e.g. John Doe">
                                @error('admin_name')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-semibold">Email Address <span class="text-danger">*</span></label>
                                <input type="email" name="admin_email" value="{{ old('admin_email') }}" required class="form-control" placeholder="e.g. admin@school.com">
                                @error('admin_email')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-semibold">Username <span class="text-danger">*</span></label>
                                <input type="text" name="admin_username" value="{{ old('admin_username') }}" required class="form-control" placeholder="e.g. johndoe">
                                @error('admin_username')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-semibold">Password (Optional)</label>
                                <input type="password" name="admin_password" class="form-control" placeholder="Leave blank to auto-generate">
                                <span class="form-text text-muted">If left blank, a random password will be generated.</span>
                                @error('admin_password')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </fieldset>
            </div>

            <div class="card-footer text-right">
                <a href="{{ route('nexoryatech.schools.index') }}" class="btn btn-light">
                    <i class="icon-cross2 mr-1"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="icon-checkmark2 mr-1"></i> Create School
                </button>
            </div>
        </form>
    </div>

@endsection
