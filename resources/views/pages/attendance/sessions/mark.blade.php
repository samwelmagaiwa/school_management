@extends('layouts.master')

@section('page_title', 'Mark Attendance')

@section('content')
    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title">Mark Attendance</h6>
            <div class="header-elements">
                <a href="{{ route('attendance.sessions.index') }}" class="btn btn-sm btn-light"><i class="icon-arrow-left13 mr-1"></i> Back to Sessions</a>
            </div>
        </div>

        <div class="card-body pb-0">
            <div class="table-responsive session-meta-table mb-3">
                <table class="table table-sm table-bordered table-striped mb-0">
                    <tbody>
                    <tr>
                        <th scope="row">Date</th>
                        <td>{{ \Illuminate\Support\Carbon::parse($session->date)->format('d M, Y') }}</td>
                        <th scope="row">Class</th>
                        <td>{{ $session->my_class->name ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th scope="row">Section</th>
                        <td>{{ $session->section->name ?? '—' }}</td>
                        <th scope="row">Subject</th>
                        <td>{{ $session->subject->name ?? 'Daily Attendance' }}</td>
                    </tr>
                    <tr>
                        <th scope="row">Type</th>
                        <td>{{ ucfirst($session->type) }}</td>
                        <th scope="row">Status</th>
                        <td>
                            @php
                                $statusClasses = [
                                    'open' => 'badge-success',
                                    'submitted' => 'badge-info',
                                    'locked' => 'badge-danger'
                                ];
                            @endphp
                            <span class="badge {{ $statusClasses[$session->status] ?? 'badge-secondary' }} text-uppercase">{{ ucfirst($session->status) }}</span>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <div class="session-submit-card card border-left-danger border-left-3 mb-3">
                <div class="card-body d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                    <div class="mr-md-3 mb-3 mb-md-0">
                        <h6 class="font-weight-semibold text-danger mb-1">Finalize Session</h6>
                        <p class="mb-0 text-muted">Submit attendance for this session? You will not be able to edit it afterwards <span class="font-weight-semibold">(except by admin override)</span>.</p>
                    </div>
                    <form action="{{ route('attendance.sessions.submit', $session->id) }}" method="post" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-danger"
                                onclick="return confirm('Submit attendance for this session? You will not be able to edit it afterwards (except by admin override).');">
                            <i class="icon-locked mr-1"></i> Submit & Lock Session
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="card-body border-top">
            <form action="{{ route('attendance.sessions.records.store', $session->id) }}" method="post">
                @csrf

                <div class="table-responsive">
                    <table class="table table-striped table-bordered attendance-mark-table">
                        <thead>
                        <tr>
                            <th style="width: 3rem;">#</th>
                            <th style="width: 10rem;">Admission No</th>
                            <th>Student Name</th>
                            <th class="text-center" colspan="4">Status</th>
                            <th style="width: 14rem;">Reason (if Absent/Excused)</th>
                            <th style="width: 18rem;">Remarks</th>
                        </tr>
                        <tr class="text-uppercase font-size-sm text-muted">
                            <th></th>
                            <th></th>
                            <th></th>
                            <th class="text-center">Present</th>
                            <th class="text-center">Absent</th>
                            <th class="text-center">Late</th>
                            <th class="text-center">Excused</th>
                            <th class="text-center">Reason</th>
                        </tr>
                        </thead>
                        <tbody>
                        @php $i = 1; @endphp
                        @foreach($records->sortBy(fn($r) => optional($r->student)->name) as $record)
                            @php $student = $record->student; @endphp
                            @if(!$student)
                                @continue
                            @endif
                            <tr>
                                <td>{{ $i++ }}</td>
                                <td>{{ $student->adm_no ?? '—' }}</td>
                                <td class="font-weight-semibold">{{ $student->name }}</td>
                                <td class="text-center">
                                    <input type="hidden" name="records[{{ $loop->index }}][student_id]" value="{{ $student->id }}">
                                    <label class="attendance-radio">
                                        <input type="radio" name="records[{{ $loop->index }}][status]" value="present" {{ $record->status === 'present' ? 'checked' : '' }} required>
                                    </label>
                                </td>
                                <td class="text-center">
                                    <label class="attendance-radio">
                                        <input type="radio" name="records[{{ $loop->index }}][status]" value="absent" {{ $record->status === 'absent' ? 'checked' : '' }}>
                                    </label>
                                </td>
                                <td class="text-center">
                                    <label class="attendance-radio">
                                        <input type="radio" name="records[{{ $loop->index }}][status]" value="late" {{ $record->status === 'late' ? 'checked' : '' }}>
                                    </label>
                                </td>
                                <td class="text-center">
                                    <label class="attendance-radio">
                                        <input type="radio" name="records[{{ $loop->index }}][status]" value="excused" {{ $record->status === 'excused' ? 'checked' : '' }}>
                                    </label>
                                </td>
                                <td>
                                    <select name="records[{{ $loop->index }}][absence_reason]" class="form-control form-control-sm">
                                        <option value="">None</option>
                                        <option value="sick" {{ $record->absence_reason === 'sick' ? 'selected' : '' }}>Sick</option>
                                        <option value="family_emergency" {{ $record->absence_reason === 'family_emergency' ? 'selected' : '' }}>Family Emergency</option>
                                        <option value="school_activity" {{ $record->absence_reason === 'school_activity' ? 'selected' : '' }}>School Activity</option>
                                        <option value="unexcused" {{ $record->absence_reason === 'unexcused' ? 'selected' : '' }}>Unexcused</option>
                                        <option value="other" {{ $record->absence_reason === 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="text" name="records[{{ $loop->index }}][remarks]" class="form-control form-control-sm"
                                           value="{{ old('records.'.$loop->index.'.remarks', $record->remarks) }}" maxlength="500" placeholder="Optional note">
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="attendance-actions mt-3 d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                    <div class="mb-2 mb-md-0">
                        <button type="button" class="btn btn-sm btn-outline-success" id="mark-all-present">
                            <i class="icon-user-check mr-1"></i> Mark All Present
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger ml-md-2" id="mark-all-absent">
                            <i class="icon-user-block mr-1"></i> Mark All Absent
                        </button>
                    </div>
                    <button type="submit" class="btn btn-success"><i class="icon-floppy-disk mr-1"></i> Save Attendance</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .attendance-mark-table th,
        .attendance-mark-table td {
            vertical-align: middle;
        }
        .attendance-radio input[type="radio"] {
            width: 1rem;
            height: 1rem;
        }
        .attendance-actions {
            border-top: 1px dashed rgba(0,0,0,0.08);
            padding-top: 1rem;
        }
        .session-submit-card {
            border-radius: .5rem;
            box-shadow: 0 6px 18px rgba(230, 57, 70, 0.12);
        }
        .border-left-3 {
            border-left-width: 4px !important;
        }
        .session-meta-table th {
            width: 12%;
            text-transform: uppercase;
            font-size: 0.75rem;
            color: rgba(0,0,0,0.62);
            background: rgba(0,0,0,0.02);
        }
        .session-meta-table td {
            font-weight: 600;
        }
        @media (max-width: 767.98px) {
            .session-meta-table th,
            .session-meta-table td {
                display: block;
                width: 100% !important;
                text-align: left;
            }
            .session-meta-table tr {
                margin-bottom: 0.75rem;
                display: block;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        (function () {
            function bulkMark(value) {
                document.querySelectorAll('.attendance-mark-table input[type="radio"][value="' + value + '"]').forEach(function (input) {
                    input.checked = true;
                });
            }

            var btnAllPresent = document.getElementById('mark-all-present');
            var btnAllAbsent  = document.getElementById('mark-all-absent');

            if (btnAllPresent) {
                btnAllPresent.addEventListener('click', function () {
                    bulkMark('present');
                });
            }

            if (btnAllAbsent) {
                btnAllAbsent.addEventListener('click', function () {
                    bulkMark('absent');
                });
            }
        })();
    </script>
@endpush
