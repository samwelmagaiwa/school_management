@extends('layouts.master')
@section('page_title', 'Loan #'.$loan->id)
@section('content')

    <div class="row">
        <div class="col-xl-4">
            <div class="card mb-3">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title"><i class="icon-ticket mr-2"></i> Summary</h6>
                </div>
                <div class="card-body">
                    <dl class="mb-0">
                        <dt>Book</dt>
                        <dd>{{ optional($loan->copy->book)->name }} <small class="text-muted">{{ optional($loan->copy)->copy_code }}</small></dd>

                        <dt>Borrower</dt>
                        <dd>{{ optional($loan->user)->name }}</dd>

                        <dt>Processed By</dt>
                        <dd>{{ optional($loan->processedBy)->name }}</dd>

                        <dt>Status</dt>
                        <dd>
                            <span class="badge badge-{{ $loan->status === 'returned' ? 'success' : ($loan->status === 'reversed' ? 'secondary' : 'primary') }} text-capitalize">{{ $loan->status }}</span>
                            @if($loan->has_override)
                                <span class="badge badge-warning">Override</span>
                            @endif
                        </dd>

                        <dt>Borrowed</dt>
                        <dd>{{ $loan->borrowed_at ? \Illuminate\Support\Carbon::parse($loan->borrowed_at)->format('Y-m-d H:i') : '' }}</dd>

                        <dt>Due Date</dt>
                        <dd>{{ $loan->due_at ? \Illuminate\Support\Carbon::parse($loan->due_at)->format('Y-m-d H:i') : '—' }}</dd>

                        <dt>Returned</dt>
                        <dd>{{ $loan->returned_at ? \Illuminate\Support\Carbon::parse($loan->returned_at)->format('Y-m-d H:i') : 'Not yet' }}</dd>

                        <dt>Fine</dt>
                        <dd>{{ number_format($loan->fine_amount, 2) }}</dd>

                        @if($loan->override_notes)
                            <dt>Override Notes</dt>
                            <dd>{{ $loan->override_notes }}</dd>
                        @endif
                    </dl>
                </div>
            </div>

            @if($loan->request)
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title"><i class="icon-file-text2 mr-2"></i> Linked Request</h6>
                    </div>
                    <div class="card-body">
                        <dl class="mb-0">
                            <dt>Requester</dt>
                            <dd>{{ optional($loan->request->requester)->name }}</dd>
                            <dt>Status</dt>
                            <dd><span class="badge badge-info text-capitalize">{{ $loan->request->status }}</span></dd>
                            @if($loan->request->rejection_reason)
                                <dt>Notes</dt>
                                <dd>{{ $loan->request->rejection_reason }}</dd>
                            @endif
                        </dl>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-xl-8">
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title"><i class="icon-clipboard6 mr-2"></i> Audit Trail</h6>
                    <a href="{{ route('library.loans.index') }}" class="btn btn-sm btn-light">Back to Loans</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Event</th>
                                <th>Actor</th>
                                <th>When</th>
                                <th>Details</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($loan->events as $event)
                                @php
                                    $meta = $event->meta ?? [];
                                    $actorName = optional($event->actor)->name ?: ('User #'.$event->performed_by);
                                    $details = '';

                                    switch ($event->event_type) {
                                        case 'loan_created':
                                            $details = 'Loan created by '.$actorName;
                                            break;

                                        case 'returned':
                                            $fine = $meta['fine'] ?? null;
                                            $days = $meta['days_overdue'] ?? null;
                                            $parts = ['Returned by '.$actorName];
                                            if (! is_null($fine)) {
                                                $parts[] = 'fine: '.number_format((float) $fine, 2);
                                            }
                                            if (! is_null($days)) {
                                                $parts[] = 'days overdue: '.$days;
                                            }
                                            $details = implode(' | ', $parts);
                                            break;

                                        case 'fine_waived':
                                            $reason = $meta['reason'] ?? null;
                                            $details = 'Fine waived by '.$actorName.($reason ? ' (reason: '.$reason.')' : '');
                                            break;

                                        case 'force_closed':
                                            $reason = $meta['reason'] ?? null;
                                            $details = 'Loan force-closed by '.$actorName.($reason ? ' (reason: '.$reason.')' : '');
                                            break;

                                        case 'loan_reversed':
                                            $reason = $meta['reason'] ?? null;
                                            $details = 'Loan reversed by '.$actorName.($reason ? ' (reason: '.$reason.')' : '');
                                            break;

                                        case 'request_approved':
                                            $requestId = $meta['request_id'] ?? null;
                                            $details = 'Borrow request approved by '.$actorName.($requestId ? ' (request #'.$requestId.')' : '');
                                            break;

                                        default:
                                            $details = json_encode($meta, JSON_UNESCAPED_UNICODE);
                                    }
                                @endphp
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td><span class="text-capitalize">{{ str_replace('_', ' ', $event->event_type) }}</span></td>
                                    <td>{{ optional($event->actor)->name }}</td>
                                    <td>{{ $event->created_at->format('Y-m-d H:i') }}</td>
                                    <td>{{ $details ?: '—' }}</td>
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
