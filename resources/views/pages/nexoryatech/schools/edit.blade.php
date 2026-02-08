@extends('layouts.master')
@section('page_title', 'Edit School')
@section('content')

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title font-weight-semibold">Edit School: {{ $school->name }}</h6>
            {!! Qs::getPanelOptions() !!}
        </div>

        <form method="POST" action="{{ route('nexoryatech.schools.update', $school->id) }}">
            @csrf @method('PUT')
            
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-semibold">School Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" value="{{ old('name', $school->name) }}" required class="form-control">
                            @error('name')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-semibold">School Code <span class="text-danger">*</span></label>
                            <input type="text" name="school_code" value="{{ old('school_code', $school->school_code) }}" required class="form-control text-uppercase" maxlength="50">
                            <span class="form-text text-muted">Uppercase letters, numbers, and underscores only.</span>
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
                                <option value="active" {{ old('status', $school->status) == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $school->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <fieldset class="mt-3">
                    <legend class="font-weight-semibold"><i class="icon-cogs mr-2"></i> School Settings</legend>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-semibold">School Motto</label>
                                <input type="text" name="motto" value="{{ old('motto', $school->settings['motto'] ?? '') }}" class="form-control" placeholder="e.g. Excellence in Education">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-semibold">Theme Color</label>
                                <input type="text" name="theme_color" value="{{ old('theme_color', $school->settings['theme_color'] ?? '') }}" class="form-control" placeholder="e.g. #3498db">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-semibold">Contact Phone</label>
                                <input type="text" name="phone" value="{{ old('phone', $school->settings['phone'] ?? '') }}" class="form-control" placeholder="e.g. +1234567890">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-semibold">Contact Email</label>
                                <input type="email" name="email" value="{{ old('email', $school->settings['email'] ?? '') }}" class="form-control" placeholder="e.g. info@school.com">
                            </div>
                        </div>
                    </div>
                </fieldset>
                
                <div class="alert alert-warning border-0 mt-3">
                    <i class="icon-warning2 mr-2"></i>
                    <strong>Note:</strong> Changing the school code will affect login for all users. Students, teachers, and admins will need to use the new school code to log in.
                </div>
            </div>

            <div class="card-footer text-right">
                <a href="{{ route('nexoryatech.schools.index') }}" class="btn btn-light">
                    <i class="icon-cross2 mr-1"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="icon-checkmark2 mr-1"></i> Update School
                </button>
            </div>
        </form>
    </div>

@endsection
