@extends('layouts.master')
@section('page_title', 'My Borrow Requests')
@section('content')

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title"><i class="icon-file-text mr-2"></i> My Borrow Requests</h6>
            {!! Qs::getPanelOptions() !!}
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table datatable-button-html5-columns">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Book</th>
                        <th>Status</th>
                        <th>Requested At</th>
                        <th>Notes</th>
                        <th class="text-center">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($requests as $request)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ optional($request->book)->name }}</td>
                            <td>
                                <span class="badge badge-{{ $request->status === 'pending' ? 'warning' : ($request->status === 'approved' ? 'success' : ($request->status === 'rejected' ? 'danger' : 'secondary')) }} text-capitalize">{{ $request->status }}</span>
                            </td>
                            <td>{{ $request->created_at?->format('Y-m-d H:i') }}</td>
                            <td>{{ $request->rejection_reason ?: '—' }}</td>
                            <td class="text-center">
                                @if($request->status === 'pending')
                                    <form method="post" action="{{ route('library.requests.cancel', $request->id) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Cancel</button>
                                    </form>
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
@endsection
