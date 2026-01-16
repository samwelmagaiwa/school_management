@extends('layouts.master')
@section('page_title', 'Departments')
@section('content')

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title">Departments</h6>
            {!! Qs::getPanelOptions() !!}
        </div>

        <div class="card-body">
            <form method="post" action="{{ route('departments.store') }}" class="mb-4">
                @csrf
                <div class="form-row align-items-end">
                    <div class="col-md-4 mb-2">
                        <label class="font-weight-semibold">Name</label>
                        <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="font-weight-semibold">Head of Department (Teacher)</label>
                        <select name="head_id" class="form-control select-search">
                            <option value="">None</option>
                            @foreach($teachers as $t)
                                <option value="{{ $t->id }}" {{ old('head_id') == $t->id ? 'selected' : '' }}>
                                    {{ $t->name }} ({{ $t->username }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-2">
                        <button type="submit" class="btn btn-success mt-3">Add Department</button>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Head of Department</th>
                        <th>Classes</th>
                        <th>Subjects</th>
                        <th class="text-center">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($departments as $dept)
                        <tr>
                            <td>{{ $dept->name }}</td>
                            <td>{{ optional($dept->head)->name ?: '—' }}</td>
                            <td>{{ $dept->classes->count() }}</td>
                            <td>{{ $dept->subjects->count() }}</td>
                            <td class="text-center">
                                <div class="d-inline-flex">
                                    <form method="post" action="{{ route('departments.update', $dept->id) }}" class="mr-2">
                                        @csrf
                                        @method('PUT')
                                        <div class="form-row align-items-center">
                                            <div class="col-auto">
                                                <input type="text" name="name" value="{{ $dept->name }}" class="form-control form-control-sm" required>
                                            </div>
                                            <div class="col-auto">
                                                <select name="head_id" class="form-control form-control-sm">
                                                    <option value="">None</option>
                                                    @foreach($teachers as $t)
                                                        <option value="{{ $t->id }}" {{ $dept->head_id == $t->id ? 'selected' : '' }}>
                                                            {{ $t->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-auto">
                                                <button type="submit" class="btn btn-primary btn-sm">Update</button>
                                            </div>
                                        </div>
                                    </form>

                                    <form method="post" action="{{ route('departments.destroy', $dept->id) }}" onsubmit="return confirm('Delete this department?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">No departments defined yet.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection
