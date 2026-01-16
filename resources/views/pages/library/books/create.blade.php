@extends('layouts.master')
@section('page_title', 'Add Book')
@section('content')

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title"><i class="icon-plus-circle2 mr-2"></i> Add New Book</h6>
            {!! Qs::getPanelOptions() !!}
        </div>

        <div class="card-body">
            <form method="post" action="{{ route('library.books.store') }}" class="ajax-store" data-fouc enctype="multipart/form-data">
                @csrf

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Title <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Author(s) <span class="text-danger">*</span></label>
                            <input type="text" name="author" class="form-control" placeholder="e.g. First Author; Second Author" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>ISBN</label>
                            <input type="text" name="isbn" class="form-control" placeholder="e.g. 978-3-16-148410-0">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Category</label>
                            <select name="category" class="form-control select">
                                <option value="">Select category...</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->name }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Subject</label>
                            <input type="text" name="subject" class="form-control" placeholder="e.g. Mathematics, History">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Edition</label>
                            <input type="text" name="edition" class="form-control" placeholder="e.g. 3rd Edition">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Publisher</label>
                            <input type="text" name="publisher" class="form-control" placeholder="Publisher name">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Publication Year</label>
                            <input type="number" name="publication_year" class="form-control" min="1800" max="{{ date('Y') + 1 }}" placeholder="e.g. 2024">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Language</label>
                            <input type="text" name="language" class="form-control" placeholder="e.g. English">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Type</label>
                            <input type="text" name="book_type" class="form-control" placeholder="e.g. Print, Digital">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Total Copies</label>
                            <input type="number" name="total_copies" class="form-control" min="0" value="0" placeholder="Number of physical copies">
                            <small class="form-text text-muted">Physical copy records will be created automatically.</small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Shelf / Location</label>
                            <input type="text" name="location" class="form-control" placeholder="e.g. Shelf A3, Row 2">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>URL / Digital Link (optional)</label>
                            <input type="text" name="url" class="form-control" placeholder="Online resource or e-book link">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Reference Only?</label>
                            <select name="is_reference_only" class="form-control select">
                                <option value="0" selected>No</option>
                                <option value="1">Yes - Do not allow borrowing</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Cover Image (optional)</label>
                            <input type="file" name="cover_image" class="file-input" accept="image/*">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Short summary or notes about this book"></textarea>
                </div>

                <div class="text-right">
                    <button type="submit" class="btn btn-primary">Save Book <i class="icon-paperplane ml-2"></i></button>
                </div>
            </form>
        </div>
    </div>
@endsection
