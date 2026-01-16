@extends('layouts.master')
@section('page_title', 'Library Books')
@section('content')

    @php($currentUser = Auth::user())
    @php($isLibraryManager = $currentUser && in_array($currentUser->user_type, ['librarian','admin','super_admin']))

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title"><i class="icon-books mr-2"></i> Library Catalog</h6>
            {!! Qs::getPanelOptions() !!}
        </div>

        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-8">
                    <form method="get" action="{{ route('library.books.index') }}" class="form-inline">
                        <div class="input-group w-100 mb-2 mb-md-0">
                            <input type="text" name="q" value="{{ $search ?? '' }}" class="form-control" placeholder="Search by title, author or ISBN...">
                            <span class="input-group-append">
                                <button class="btn btn-primary" type="submit"><i class="icon-search4"></i></button>
                            </span>
                        </div>
                        <div class="ml-0 ml-md-2 mt-2 mt-md-0">
                            <select name="filter" class="form-control mb-2 mb-md-0">
                                <option value="" {{ empty($filter) ? 'selected' : '' }}>All types</option>
                                <option value="borrowable" {{ ($filter ?? '') === 'borrowable' ? 'selected' : '' }}>Borrowable only</option>
                                <option value="reference" {{ ($filter ?? '') === 'reference' ? 'selected' : '' }}>Reference only</option>
                            </select>
                        </div>
                        <div class="ml-0 ml-md-2 mt-2 mt-md-0">
                            <select name="category" class="form-control">
                                <option value="" {{ empty($category) ? 'selected' : '' }}>All categories</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->name }}" {{ ($category ?? '') === $cat->name ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                </div>
                <div class="col-md-4 text-md-right mt-3 mt-md-0">
                    @if($isLibraryManager)
                        <a href="{{ route('library.books.create') }}" class="btn btn-success"><i class="icon-plus-circle2 mr-1"></i> Add Book</a>
                    @else
                        <a href="{{ route('library.requests.my') }}" class="btn btn-outline-primary"><i class="icon-file-text mr-1"></i> My Requests</a>
                    @endif
                </div>
            </div>

            <div class="table-responsive">
                <table class="table datatable-button-html5-columns">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Cover</th>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Type</th>
                        <th>Location</th>
                        <th class="text-center">Availability</th>
                        <th class="text-center">Flags</th>
                        <th class="text-center">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($books as $book)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                @if($book->cover_image_path)
                                    <img src="{{ asset($book->cover_image_path) }}" alt="{{ $book->name }} cover" style="height: 40px; width: auto;" />
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>{{ $book->name }}</td>
                            <td>{{ $book->author }}</td>
                            <td>{{ $book->book_type }}</td>
                            <td>{{ $book->location }}</td>
                            <td class="text-center">
                                <span class="badge badge-success">{{ $book->available_copies_count }} available</span>
                                <span class="badge badge-light">{{ $book->total_copies_count }} total</span>
                            </td>
                            <td class="text-center">
                                @if($book->is_reference_only)
                                    <span class="badge badge-warning">Reference Only</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($isLibraryManager)
                                <div class="list-icons">
                                    <div class="dropdown">
                                        <a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a>
                                        <div class="dropdown-menu dropdown-menu-left">
                                            <a href="{{ route('library.books.show', $book->id) }}" class="dropdown-item"><i class="icon-eye"></i> View / Copies</a>
                                            <a href="{{ route('library.books.edit', $book->id) }}" class="dropdown-item"><i class="icon-pencil"></i> Edit</a>
                                            <a href="#" onclick="event.preventDefault(); confirmDelete('book-{{ $book->id }}')" class="dropdown-item"><i class="icon-trash"></i> Delete</a>
                                            <form id="item-delete-book-{{ $book->id }}" method="post" action="{{ route('library.books.destroy', $book->id) }}" class="d-none">@csrf @method('delete')</form>
                                        </div>
                                    </div>
                                </div>
                                @else
                                    @if($book->is_reference_only)
                                        <span class="badge badge-warning">Reference only - cannot be borrowed</span>
                                    @else
                                        <form method="post" action="{{ route('library.requests.store') }}" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="book_id" value="{{ $book->id }}">
                                            <button type="submit" class="btn btn-sm btn-primary" {{ $book->available_copies_count ? '' : 'disabled' }}>
                                                Request to Borrow
                                            </button>
                                        </form>
                                    @endif
                                    @if(!$book->available_copies_count)
                                        <span class="badge badge-secondary">Queueing</span>
                                    @endif
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
