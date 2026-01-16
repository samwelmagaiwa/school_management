@extends('layouts.master')
@section('page_title', 'Book Details')
@section('content')

    @php($currentUser = Auth::user())
    @php($isLibraryManager = $currentUser && in_array($currentUser->user_type, ['librarian','admin','super_admin']))

    <div class="row">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title"><i class="icon-book mr-2"></i> Book Details</h6>
                </div>
                <div class="card-body">
                    @if(!$isLibraryManager)
                        @if($book->is_reference_only)
                            <div class="alert alert-warning mb-3">
                                This book is <strong>reference only</strong> and cannot be borrowed.
                            </div>
                        @else
                            <form method="post" action="{{ route('library.requests.store') }}" class="mb-3">
                                @csrf
                                <input type="hidden" name="book_id" value="{{ $book->id }}">
                                <button type="submit" class="btn btn-primary btn-block" {{ $book->available_copies_count ? '' : 'disabled' }}>
                                    Request to Borrow
                                </button>
                            </form>
                        @endif
                        <a href="{{ route('library.requests.my') }}" class="btn btn-outline-primary btn-block mb-3">View My Requests</a>
                        @if(!$book->available_copies_count)
                            <div class="alert alert-warning mb-3">All copies are currently issued. Your request will be queued.</div>
                        @endif
                    @else
                        {{-- Physical borrowing for librarian/admin/super admin --}}
                        @if($book->is_reference_only)
                            <div class="alert alert-warning mb-3">
                                This book is <strong>reference only</strong>. Physical loans are disabled; users can consult it on-site only.
                            </div>
                        @else
                            <div class="alert alert-info mb-3">
                                You can borrow this book for a user from the Copies section on the right.
                            </div>
                        @endif
                    @endif

                    @if($book->cover_image_path)
                        <div class="text-center mb-3">
                            <img src="{{ asset($book->cover_image_path) }}" alt="{{ $book->name }} cover" class="img-fluid" style="max-height: 220px;">
                        </div>
                    @endif

                    <dl class="mb-0">
                        <dt>Title</dt>
                        <dd>{{ $book->name }}</dd>

                        <dt>Author(s)</dt>
                        <dd>{{ $book->author ?: '—' }}</dd>

                        <dt>ISBN</dt>
                        <dd>{{ $book->isbn ?: '—' }}</dd>

                        <dt>Category</dt>
                        <dd>{{ $book->category ?: '—' }}</dd>

                        <dt>Subject</dt>
                        <dd>{{ $book->subject ?: '—' }}</dd>

                        <dt>Edition</dt>
                        <dd>{{ $book->edition ?: '—' }}</dd>

                        <dt>Publisher</dt>
                        <dd>{{ $book->publisher ?: '—' }}</dd>

                        <dt>Publication Year</dt>
                        <dd>{{ $book->publication_year ?: '—' }}</dd>

                        <dt>Language</dt>
                        <dd>{{ $book->language ?: '—' }}</dd>

                        <dt>Type</dt>
                        <dd>{{ $book->book_type ?: '—' }}</dd>

                        <dt>Location</dt>
                        <dd>{{ $book->location ?: '—' }}</dd>

                        <dt>Reference Only</dt>
                        <dd>
                            @if($book->is_reference_only)
                                <span class="badge badge-warning">Yes</span>
                            @else
                                <span class="badge badge-success">No</span>
                            @endif
                        </dd>

                        <dt>URL / Digital Link</dt>
                        <dd>
                            @if($book->url)
                                <a href="{{ $book->url }}" target="_blank">{{ $book->url }}</a>
                            @else
                                —
                            @endif
                        </dd>

                        <dt>Description</dt>
                        <dd>{{ $book->description ?: '—' }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            @if($isLibraryManager)
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title">Copies</h6>
                    {!! Qs::getPanelOptions() !!}
                </div>

                <div class="card-body">
                    <form method="post" action="{{ route('library.books.copies.store', $book->id) }}" class="form-inline mb-3">
                        @csrf
                        <div class="form-group mr-2 mb-2">
                            <label class="mr-2">Add Copies</label>
                            <input type="number" min="1"  name="quantity" value="1" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary mb-2"><i class="icon-plus22 mr-1"></i> Add</button>
                    </form>

                    <div class="table-responsive">
                        <table class="table datatable-button-html5-columns">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Copy Code</th>
                                <th>Status</th>
                                <th>Notes</th>
                                <th class="text-center">Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($book->copies as $copy)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $copy->copy_code }}</td>
                                    <td>
                                        @php($status = $copy->status)
                                        <span class="badge badge-{{ $status === 'available' ? 'success' : ($status === 'borrowed' ? 'primary' : 'danger') }}">{{ ucfirst($status) }}</span>
                                    </td>
                                    <td>{{ $copy->notes }}</td>
                                    <td class="text-center">
                                        <div class="list-icons">
                                            <div class="dropdown">
                                                <a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a>
                                                <div class="dropdown-menu dropdown-menu-left">
                                                    <form method="post" action="{{ route('library.copies.status.update', $copy->id) }}" class="px-3 py-2">
                                                        @csrf @method('PUT')
                                                        <div class="form-group mb-2">
                                                            <label class="font-weight-semibold">Status</label>
                                                            <select name="status" class="form-control">
                                                                <option value="available" {{ $copy->status == 'available' ? 'selected' : '' }}>Available</option>
                                                                <option value="damaged" {{ $copy->status == 'damaged' ? 'selected' : '' }}>Damaged</option>
                                                                <option value="lost" {{ $copy->status == 'lost' ? 'selected' : '' }}>Lost</option>
                                                            </select>
                                                        </div>
                                                        <div class="form-group mb-2">
                                                            <label class="font-weight-semibold">Notes</label>
                                                            <textarea name="notes" class="form-control" rows="2">{{ $copy->notes }}</textarea>
                                                        </div>
                                                        <button type="submit" class="btn btn-primary btn-block">Update</button>
                                                    </form>
                                                    <div class="dropdown-divider"></div>
                                                    <a href="#" onclick="event.preventDefault(); confirmDelete('copy-{{ $copy->id }}')" class="dropdown-item"><i class="icon-trash"></i> Delete Copy</a>
                                                    <form id="item-delete-copy-{{ $copy->id }}" method="post" action="{{ route('library.copies.destroy', $copy->id) }}" class="d-none">@csrf @method('DELETE')</form>
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
            @endif
        </div>
    </div>

    @if($isLibraryManager)
        {{-- Borrow for User modal --}}
        <div id="borrowModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="borrowModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h6 class="modal-title" id="borrowModalLabel">Borrow "{{ $book->name }}" For User</h6>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form method="post" action="{{ route('library.loans.store') }}">
                        @csrf
                        <div class="modal-body">
                            <input type="hidden" name="book_id" value="{{ $book->id }}">
                            <input type="hidden" name="book_copy_id" id="borrow_copy_id" value="">

                            <div class="form-group">
                                <label for="borrower_id">Borrower</label>
                                <select class="select-search form-control" id="borrower_id" name="borrower_id" data-placeholder="Search by name..." required>
                                    <option value=""></option>
                                    @foreach($borrowers as $u)
                                        <option value="{{ $u->id }}">{{ $u->name }} ({{ ucfirst($u->user_type) }})</option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">Start typing to search for a student or teacher by name.</small>
                            </div>

                            <div class="form-group">
                                <label for="borrow_copy_select">Assign Copy (optional)</label>
                                <select class="form-control" id="borrow_copy_select" onchange="document.getElementById('borrow_copy_id').value = this.value;">
                                    <option value="">Auto-select first available copy</option>
                                    @foreach($book->copies->where('status', 'available') as $copy)
                                        <option value="{{ $copy->id }}">{{ $copy->copy_code }}</option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">If left empty, the system will choose the first available copy.</small>
                            </div>

                            @php($defaultDue = \Illuminate\Support\Carbon::now()
                                ->addDays(config('library.loan_period_days', 14))
                                ->format('Y-m-d\TH:i'))
                            <div class="form-group">
                                <label for="due_at">Return Date &amp; Time</label>
                                <input type="datetime-local" name="due_at" id="due_at" class="form-control" value="{{ $defaultDue }}" required>
                                <small class="form-text text-muted">Select the exact date and time this book should be returned. Fines start counting after this moment.</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Create Loan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection

@section('scripts')
    @parent
    <script>
        // Ensure the Borrower select behaves as a searchable Select2 inside the Bootstrap modal
        $(function () {
            $('#borrowModal').on('shown.bs.modal', function () {
                var $modal = $(this);
                var $select = $modal.find('#borrower_id');

                // If Select2 was already initialized by global scripts, destroy and reinitialize
                if ($select.data('select2')) {
                    $select.select2('destroy');
                }

                $select.select2({
                    dropdownParent: $modal,
                    width: '100%'
                });
            });
        });
    </script>
@endsection
