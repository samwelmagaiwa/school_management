@extends('layouts.master')
@section('page_title', 'Exam Statistics Dashboard')
@section('content')

<div class="card">
    <div class="card-header header-elements-inline">
        <h6 class="card-title font-weight-bold">Exam Statistics - {{ $year }}</h6>
        {!! Qs::getPanelOptions() !!}
    </div>

    <div class="card-body">
        @if($exams->count() > 0)
            <div class="row">
                @foreach($exams as $exam)
                    <div class="col-md-4 mb-3">
                        <div class="card border-left-primary shadow h-100">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            {{ $exam->name }}
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            Term {{ $exam->term }}
                                        </div>
                                        <div class="mt-2">
                                            <small class="text-muted">Academic Year: {{ $exam->year }}</small>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="icon-stats-bars icon-2x text-gray-300"></i>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <a href="{{ route('exam_stats.show', $exam->id) }}" class="btn btn-primary btn-sm btn-block">
                                        <i class="icon-eye"></i> View Statistics
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="alert alert-info">
                <i class="icon-info22"></i> No exams found for the current session ({{ $year }}).
            </div>
        @endif
    </div>
</div>

@endsection
