@extends('layouts.master')
@section('page_title', 'Borrow Requests')
@section('content')

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title"><i class="icon-file-check mr-2"></i> Borrow Requests</h6>
            {!! Qs::getPanelOptions() !!}
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table datatable-button-html5-columns">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Book</th>
                        <th>Borrower</th>
                        <th>Status</th>
                        <th>Requested At</th>
                        <th>Decision</th>
                        <th class="text-center">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($requests as $request)
                        @php
                            $availableCopies = optional($request->book)->copies?->where('status', 'available')->map(fn($copy) => ['id' => $copy->id, 'label' => $copy->copy_code])->values()->all() ?? [];
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ optional($request->book)->name }}</td>
                            <td>{{ optional($request->requester)->name }}</td>
                            <td>
                                <span class="badge badge-{{ $request->status === 'pending' ? 'warning' : ($request->status === 'approved' ? 'success' : ($request->status === 'cancelled' ? 'secondary' : 'danger')) }} text-capitalize">{{ $request->status }}</span>
                            </td>
                            <td>{{ $request->created_at?->format('Y-m-d H:i') }}</td>
                            <td>
                                @if($request->approved_at)
                                    <small>Approved {{ $request->approved_at->diffForHumans() }}</small>
                                @elseif($request->rejected_at)
                                    <small>Rejected {{ $request->rejected_at->diffForHumans() }}</small>
                                @elseif($request->cancelled_at)
                                    <small>Cancelled {{ $request->cancelled_at->diffForHumans() }}</small>
                                @else
                                    <small>Pending</small>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($request->status === 'pending')
                                    <div class="btn-group">
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-success js-approve-request"
                                            data-toggle="modal"
                                            data-target="#approveModal"
                                            data-id="{{ $request->id }}"
                                            data-book="{{ optional($request->book)->name }}"
                                            data-borrower="{{ optional($request->requester)->name }}"
                                            data-copies='@json($availableCopies)'
                                        >Approve</button>
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-danger js-reject-request"
                                            data-toggle="modal"
                                            data-target="#rejectModal"
                                            data-id="{{ $request->id }}"
                                            data-book="{{ optional($request->book)->name }}"
                                            data-borrower="{{ optional($request->requester)->name }}"
                                        >Reject</button>
                                    </div>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="modal fade" id="approveModal" tabindex="-1" role="dialog" aria-labelledby="approveModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h6 class="modal-title" id="approveModalLabel">Approve Request</h6>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="post" id="approveForm" class="ajax-form">
                    @csrf
                    <div class="modal-body">
                        <p class="mb-3 text-muted" id="approveModalInfo"></p>
                        <div class="form-group">
                            <label>Assign Copy</label>
                            <select name="book_copy_id" class="form-control" id="approveCopySelect"></select>
                            <small class="form-text text-muted">Only available copies are listed. Leave empty to auto-assign the first available copy.</small>
                        </div>
                        <div class="form-group">
                            <label>Borrower (optional)</label>
                            <input type="number" class="form-control" name="borrower_id" placeholder="User ID" />
                            <small class="form-text text-muted">Leave blank to approve for the requester.</small>
                        </div>
                        @php($defaultDue = \Illuminate\Support\Carbon::now()->addDays(config('library.loan_period_days', 14))->format('Y-m-d\TH:i'))
                        <div class="form-group">
                            <label for="approve_due_at">Return Date &amp; Time</label>
                            <input type="datetime-local" name="due_at" id="approve_due_at" class="form-control" value="{{ $defaultDue }}">
                            <small class="form-text text-muted">Select the exact date and time this book should be returned. Fines start counting after this moment.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Approve</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-labelledby="rejectModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h6 class="modal-title" id="rejectModalLabel">Reject Request</h6>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="post" id="rejectForm">
                    @csrf
                    <div class="modal-body">
                        <p class="mb-3 text-muted" id="rejectModalInfo"></p>
                        <div class="form-group">
                            <label>Reason</label>
                            <textarea name="reason" class="form-control" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Reject</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @parent
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var approveForm = document.getElementById('approveForm');
            var approveInfo = document.getElementById('approveModalInfo');
            var approveSelect = document.getElementById('approveCopySelect');
            var rejectForm = document.getElementById('rejectForm');
            var rejectInfo = document.getElementById('rejectModalInfo');

            $('.js-approve-request').on('click', function () {
                var button = $(this);
                var id = button.data('id');
                var borrower = button.data('borrower');
                var book = button.data('book');
                var copies = button.data('copies') || [];

                approveForm.action = '{{ url('library/requests') }}/' + id + '/approve';
                approveInfo.textContent = borrower + ' → ' + book;
                approveSelect.innerHTML = '<option value="">Auto-select available copy</option>';
                copies.forEach(function (copy) {
                    var option = document.createElement('option');
                    option.value = copy.id;
                    option.textContent = copy.label;
                    approveSelect.appendChild(option);
                });
            });

            $('.js-reject-request').on('click', function () {
                var button = $(this);
                var id = button.data('id');
                var borrower = button.data('borrower');
                var book = button.data('book');

                rejectForm.action = '{{ url('library/requests') }}/' + id + '/reject';
                rejectInfo.textContent = borrower + ' → ' + book;
            });
        });
    </script>
@endsection
