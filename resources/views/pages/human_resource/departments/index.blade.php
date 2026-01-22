@extends('layouts.master')
@section('page_title', 'Manage Departments & Designations')
@section('content')

<div class="card">
    <div class="card-header header-elements-inline">
        <h6 class="card-title">Departments & Designations</h6>
        {!! Qs::getPanelOptions() !!}
    </div>

    <div class="card-body">
        <ul class="nav nav-tabs nav-tabs-highlight">
            <li class="nav-item"><a href="#dept" class="nav-link active" data-toggle="tab">Departments</a></li>
            <li class="nav-item"><a href="#desig" class="nav-link" data-toggle="tab">Designations</a></li>
        </ul>

        <div class="tab-content">
            {{-- Departments Tab --}}
            <div class="tab-pane fade show active" id="dept">
                <div class="row">
                    <div class="col-md-6">
                        <form method="POST" action="{{ route('hr.departments.store') }}">
                            @csrf
                            <div class="form-group row">
                                <label class="col-lg-3 col-form-label font-weight-semibold">Name <span class="text-danger">*</span></label>
                                <div class="col-lg-9">
                                    <input name="name" required type="text" class="form-control" placeholder="Department Name">
                                </div>
                            </div>
                            <div class="text-right">
                                <button type="submit" class="btn btn-primary">Submit form <i class="icon-paperplane ml-2"></i></button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="table-responsive mt-3">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($departments as $d)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $d->name }}</td>
                                    <td>
                                        <div class="list-icons">
                                            <div class="dropdown">
                                                <a href="#" class="list-icons-item" data-toggle="dropdown">
                                                    <i class="icon-menu9"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-right">
                                                    {{--Edit/Delete to be implemented if needed--}}
                                                    <a href="#" class="dropdown-item"><i class="icon-pencil"></i> Edit</a>
                                                    <a href="#" class="dropdown-item"><i class="icon-trash"></i> Delete</a>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Designations Tab --}}
            <div class="tab-pane fade" id="desig">
                <div class="row">
                    <div class="col-md-6">
                        <form method="POST" action="{{ route('hr.designations.store') }}">
                            @csrf
                            <div class="form-group row">
                                <label class="col-lg-3 col-form-label font-weight-semibold">Title <span class="text-danger">*</span></label>
                                <div class="col-lg-9">
                                    <input name="title" required type="text" class="form-control" placeholder="Designation Title">
                                </div>
                            </div>
                            <div class="text-right">
                                <button type="submit" class="btn btn-primary">Submit form <i class="icon-paperplane ml-2"></i></button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="table-responsive mt-3">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Title</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($designations as $d)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $d->title }}</td>
                                    <td>
                                        <div class="list-icons">
                                            <div class="dropdown">
                                                <a href="#" class="list-icons-item" data-toggle="dropdown">
                                                    <i class="icon-menu9"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-right">
                                                    <a href="#" class="dropdown-item"><i class="icon-pencil"></i> Edit</a>
                                                    <a href="#" class="dropdown-item"><i class="icon-trash"></i> Delete</a>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
