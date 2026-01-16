@extends('layouts.master')
@section('page_title', 'Manage Dorms')
@section('content')

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title">Manage Dorms</h6>
            {!! Qs::getPanelOptions() !!}
        </div>

        <div class="card-body">
            <ul class="nav nav-tabs nav-tabs-highlight">
                <li class="nav-item"><a href="#all-dorms" class="nav-link active" data-toggle="tab">Manage Dorms</a></li>
                <li class="nav-item"><a href="#new-dorm" class="nav-link" data-toggle="tab"><i class="icon-plus2"></i> Create New Dorm</a></li>
            </ul>

            <div class="tab-content">
                    <div class="tab-pane fade show active" id="all-dorms">
                        <table class="table datatable-button-html5-columns">
                            <thead>
                            <tr>
                                <th>S/N</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Gender</th>
                                <th>Capacity</th>
                                <th>Rooms</th>
                                <th>Beds</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($dorms as $d)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $d->name }}</td>
                                    <td>{{ $d->description}}</td>
                                    <td class="text-capitalize">{{ $d->gender ?? 'mixed' }}</td>
                                    <td>{{ $d->capacity ?: '—' }}</td>
                                    <td>{{ $d->room_count ?? $d->rooms->count() }}</td>
                                    <td>{{ $d->bed_count ?? $d->rooms->sum('bed_count') }}</td>
                                    <td class="text-center">
                                        <div class="list-icons">
                                            <div class="dropdown">
                                                <a href="#" class="list-icons-item" data-toggle="dropdown">
                                                    <i class="icon-menu9"></i>
                                                </a>

                                                <div class="dropdown-menu dropdown-menu-left">
                                                    @if(Qs::userIsTeamSA())
                                                    {{--Edit--}}
                                                    <a href="{{ route('dorms.edit', $d->id) }}" class="dropdown-item"><i class="icon-pencil"></i> Edit</a>
                                                   @endif
                                                    <a href="#" class="dropdown-item" data-toggle="collapse" data-target="#dorm-rooms-{{ $d->id }}"><i class="icon-list"></i> Rooms & Beds</a>
                                                        @if(Qs::userIsSuperAdmin())
                                                    {{--Delete--}}
                                                    <a id="{{ $d->id }}" onclick="confirmDelete(this.id)" href="#" class="dropdown-item"><i class="icon-trash"></i> Delete</a>
                                                    <form method="post" id="item-delete-{{ $d->id }}" action="{{ route('dorms.destroy', $d->id) }}" class="hidden">@csrf @method('delete')</form>
                                                        @endif

                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="collapse dorm-extra" id="dorm-rooms-{{ $d->id }}">
                                    <td colspan="8">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h6 class="font-weight-semibold">Rooms</h6>
                                                <div class="table-responsive">
                                                    <table class="table table-sm">
                                                        <thead>
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Name</th>
                                                            <th>Beds</th>
                                                            <th>Gender</th>
                                                            <th></th>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                            @forelse($d->rooms as $room)
                                                                <tr>
                                                                    <td>{{ $loop->iteration }}</td>
                                                                    <td>{{ $room->name }}</td>
                                                                    <td>{{ $room->bed_count }}</td>
                                                                    <td class="text-capitalize">{{ $room->gender }}</td>
                                                                    <td class="text-right text-muted">
                                                                        {{ $room->capacity ?: '—' }} capacity
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td colspan="5">
                                                                        <div class="table-responsive">
                                                                            <table class="table table-bordered table-xs">
                                                                                <thead>
                                                                                <tr>
                                                                                    <th>Bed</th>
                                                                                    <th>Status</th>
                                                                                    <th></th>
                                                                                </tr>
                                                                                </thead>
                                                                                <tbody>
                                                                                @forelse($room->beds as $bed)
                                                                                    <tr>
                                                                                        <td>{{ $bed->label }}</td>
                                                                                        <td class="text-capitalize">{{ $bed->status }}</td>
                                                                                        <td class="text-right">
                                                                                            <span class="badge badge-flat border-{{ $bed->status === 'available' ? 'success' : 'secondary' }} text-{{ $bed->status === 'available' ? 'success' : 'secondary' }} text-capitalize">{{ $bed->status }}</span>
                                                                                        </td>
                                                                                    </tr>
                                                                                @empty
                                                                                    <tr><td colspan="3" class="text-muted">No beds configured.</td></tr>
                                                                                @endforelse
                                                                                </tbody>
                                                                            </table>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                            @empty
                                                                <tr><td colspan="5" class="text-muted">No rooms configured.</td></tr>
                                                            @endforelse
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                            @if(in_array(auth()->user()->user_type, ['hostel_officer','admin','super_admin']))
                                                <div class="col-md-6">
                                                    <h6 class="font-weight-semibold">Quick Actions</h6>
                                                    <form class="ajax-store" method="post" action="{{ route('dorms.rooms.store', $d->id) }}">
                                                        @csrf
                                                        <div class="form-group">
                                                            <label>Room Name <span class="text-danger">*</span></label>
                                                            <input type="text" name="name" class="form-control" required>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Floor</label>
                                                            <input type="number" min="0" name="floor" class="form-control">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Capacity</label>
                                                            <input type="number" min="0" name="capacity" class="form-control">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Gender <span class="text-danger">*</span></label>
                                                            <select name="gender" class="form-control">
                                                                <option value="male">Male</option>
                                                                <option value="female">Female</option>
                                                                <option value="mixed" selected>Mixed</option>
                                                            </select>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Notes</label>
                                                            <textarea name="notes" class="form-control" rows="2"></textarea>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Bed Labels (one per line)</label>
                                                            <textarea name="bed_labels" class="form-control" rows="3" placeholder="A1\nA2\nA3"></textarea>
                                                        </div>
                                                        <div class="text-right">
                                                            <button type="submit" class="btn btn-primary">Save Room</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                <div class="tab-pane fade" id="new-dorm">

                    <div class="row">
                        <div class="col-md-6">
                            <form class="ajax-store" method="post" action="{{ route('dorms.store') }}">
                                @csrf
                                <div class="form-group row">
                                    <label class="col-lg-3 col-form-label font-weight-semibold">Name <span class="text-danger">*</span></label>
                                    <div class="col-lg-9">
                                        <input name="name" value="{{ old('name') }}" required type="text" class="form-control" placeholder="Name of Dormitory">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-lg-3 col-form-label font-weight-semibold">Description</label>
                                    <div class="col-lg-9">
                                        <input name="description" value="{{ old('description') }}"  type="text" class="form-control" placeholder="Description of Dormitory">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-lg-3 col-form-label font-weight-semibold">Gender <span class="text-danger">*</span></label>
                                    <div class="col-lg-9">
                                        <select name="gender" class="form-control" required>
                                            <option value="mixed" selected>Mixed</option>
                                            <option value="male">Male</option>
                                            <option value="female">Female</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-lg-3 col-form-label font-weight-semibold">Capacity</label>
                                    <div class="col-lg-9">
                                        <input name="capacity" value="{{ old('capacity') }}"  type="number" class="form-control" placeholder="Total Capacity">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-lg-3 col-form-label font-weight-semibold">Notes</label>
                                    <div class="col-lg-9">
                                        <textarea name="notes" class="form-control" rows="2" placeholder="Internal notes">{{ old('notes') }}</textarea>
                                    </div>
                                </div>

                                <div class="text-right">
                                    <button id="ajax-btn" type="submit" class="btn btn-primary">Submit form <i class="icon-paperplane ml-2"></i></button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{--Dorm List Ends--}}

@endsection
