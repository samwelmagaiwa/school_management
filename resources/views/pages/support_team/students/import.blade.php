@extends('layouts.master')
@section('page_title', 'Import Students')
@section('content')

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title">Bulk Student Import</h6>
            {!! Qs::getPanelOptions() !!}
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="alert alert-info border-0 alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                        <span>Follow these steps to import students in bulk:</span>
                        <ol>
                            <li>Download the CSV template using the button below.</li>
                            <li>Open it in Excel or Google Sheets.</li>
                            <li>Fill in the student details (Name, Gender, Class_ID, etc.).</li>
                            <li>Save as a CSV file and upload it here.</li>
                        </ol>
                    </div>
                </div>
            </div>

            @if(session('import_errors'))
                <div class="row">
                    <div class="col-md-12">
                        <div class="alert alert-danger border-0 alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                            <h6 class="font-weight-semibold">Some rows failed to import:</h6>
                            <ul>
                                @foreach(session('import_errors') as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <div class="row">
                <div class="col-md-6">
                    <form method="post" enctype="multipart/form-data" action="{{ route('students.import.store') }}">
                        @csrf
                        <div class="form-group row">
                            <label class="col-lg-3 col-form-label font-weight-semibold">CSV File <span class="text-danger">*</span></label>
                            <div class="col-lg-9">
                                <input name="csv_file" required type="file" class="form-input-styled" data-fouc>
                                <span class="form-text text-muted">Accepted format: CSV only.</span>
                            </div>
                        </div>

                        <div class="text-right">
                             <a href="{{ route('students.import.template') }}" class="btn btn-secondary float-left"><i class="icon-download mr-2"></i> Download Template</a>
                             <button type="submit" class="btn btn-primary">Upload & Import <i class="icon-upload ml-2"></i></button>
                        </div>
                    </form>
                </div>

                <div class="col-md-6">
                    <div class="card bg-light border-left-info border-left-3">
                        <div class="card-header">
                            <h6 class="card-title font-weight-semibold">Quick Guide: Class IDs</h6>
                        </div>
                        <div class="card-body">
                           <p>Use the following IDs for the <strong>Class_ID</strong> column in your CSV:</p>
                           <table class="table table-sm table-bordered">
                               <thead>
                                   <tr>
                                       <th>ID</th>
                                       <th>Class Name</th>
                                   </tr>
                               </thead>
                               <tbody>
                                   @foreach(App\Models\MyClass::orderBy('name')->get() as $c)
                                       <tr>
                                           <td>{{ $c->id }}</td>
                                           <td>{{ $c->name }}</td>
                                       </tr>
                                   @endforeach
                               </tbody>
                           </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
