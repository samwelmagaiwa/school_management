@extends('layouts.master')
@section('page_title', 'Edit Book')
@section('content')

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title"><i class="icon-pencil mr-2"></i> Edit Book</h6>
            {!! Qs::getPanelOptions() !!}
        </div>

        <div class="card-body">
            <form method="post" action="{{ route('library.books.update', $book->id) }}" class="ajax-update" data-fouc enctype="multipart/form-data">
                @csrf @method('PUT')

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Title <span class="text-danger">*</span></label>
                            <input type="text" name="name" value="{{ $book->name }}" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Author(s) <span class="text-danger">*</span></label>
                            <input type="text" name="author" value="{{ $book->author }}" class="form-control" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>ISBN</label>
                            <input type="text" name="isbn" value="{{ $book->isbn }}" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Category</label>
                            <select name="category" class="form-control select">
                                <option value="">Select category...</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->name }}" {{ $book->category === $cat->name ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Subject</label>
                            <input type="text" name="subject" value="{{ $book->subject }}" class="form-control">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Edition</label>
                            <input type="text" name="edition" value="{{ $book->edition }}" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Publisher</label>
                            <input type="text" name="publisher" value="{{ $book->publisher }}" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Publication Year</label>
                            <input type="number" name="publication_year" value="{{ $book->publication_year }}" class="form-control" min="1800" max="{{ date('Y') + 1 }}">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Language</label>
                            <input type="text" name="language" value="{{ $book->language }}" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Type</label>
                            <input type="text" name="book_type" value="{{ $book->book_type }}" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Shelf / Location</label>
                            <input type="text" name="location" value="{{ $book->location }}" class="form-control">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>URL / Digital Link (optional)</label>
                            <input type="text" name="url" value="{{ $book->url }}" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Reference Only?</label>
                            <select name="is_reference_only" class="form-control select">
                                <option value="0" {{ $book->is_reference_only ? '' : 'selected' }}>No</option>
                                <option value="1" {{ $book->is_reference_only ? 'selected' : '' }}>Yes - Do not allow borrowing</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Cover Image (optional)</label>
                            <div class="d-flex align-items-center">
                                <input type="file" name="cover_image" class="file-input" accept="image/*">
                                @if($book->cover_image_path)
                                    <div class="ml-3 text-center">
                                        <img src="{{ asset($book->cover_image_path) }}" alt="Current cover" style="height: 60px; width: auto;" />
                                        <small class="d-block text-muted">Current cover. Upload a new file to replace.</small>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Short summary or notes about this book">{{ $book->description }}</textarea>
                </div>

                <div class="text-right">
                    <button type="submit" class="btn btn-primary">Update Book <i class="icon-paperplane ml-2"></i></button>
                </div>
            </form>
        </div>
    </div>
@endsection
