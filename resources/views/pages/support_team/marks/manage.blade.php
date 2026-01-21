@extends('layouts.master')
@section('page_title', 'Manage Marks')
@section('content')

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title font-weight-bold">Fill The Form To Manage Marks</h6>
            {!! Qs::getPanelOptions() !!}
        </div>

        <div class="card-body">
            @include('pages.support_team.marks.selector')
        </div>
    </div>

    <div class="card">

        <div class="card-header border-bottom">
            <div class="row">
                <div class="col-md-3"><h6 class="card-title"><strong>Subject: </strong> {{ $m->subject->name }}</h6></div>
                <div class="col-md-3"><h6 class="card-title"><strong>Class: </strong> {{ $m->my_class->name.' '.$m->section->name }}</h6></div>
                <div class="col-md-3"><h6 class="card-title"><strong>Exam: </strong> {{ $m->exam->name.' - '.$m->year }}</h6></div>
                <div class="col-md-3 text-right">
                    <a href="{{ route('marks.excel_export', [$m->exam_id, $m->my_class_id, $m->section_id, $m->subject_id]) }}" class="btn btn-success btn-sm"><i class="icon-file-excel mr-2"></i> Export Template</a>
                </div>
            </div>
            <hr>
            <div class="row mt-2">
                <div class="col-md-12">
                    <form method="post" action="{{ route('marks.excel_import', [$m->exam_id, $m->my_class_id, $m->section_id, $m->subject_id]) }}" enctype="multipart/form-data" class="form-inline">
                        @csrf
                        <div class="form-group mr-2">
                            <label for="marks_file" class="mr-2 font-weight-bold">Import From Excel:</label>
                            <input type="file" name="marks_file" id="marks_file" class="form-control-sm" required>
                        </div>
                        <button type="submit" class="btn btn-info btn-sm">Upload & Import <i class="icon-upload ml-1"></i></button>
                    </form>
                </div>
            </div>
        </div>

        <div class="card-body">
            @include('pages.support_team.marks.edit')
            {{--@include('pages.support_team.marks.random')--}}
        </div>
    </div>

    {{--Marks Manage End--}}

@endsection
