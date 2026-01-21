@extends('layouts.master')
@section('page_title', 'Manage User Permissions')
@section('content')

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title">Manage Permissions for {{ $user->name }} ({{ strtoupper($user->user_type) }})</h6>
            {!! Qs::getPanelOptions() !!}
        </div>

        <div class="card-body">
            <div class="alert alert-info border-0 alert-dismissible">
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                <span>Permissions marked with <i class="icon-check text-success"></i> are inherited from the user's role. Individual overrides can be assigned below.</span>
            </div>

            <form class="ajax-update" method="post" action="{{ route('users.permissions.update', Qs::hash($user->id)) }}">
                @csrf @method('PUT')

                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th>S/N</th>
                            <th>Permission</th>
                            <th>Inherited?</th>
                            <th>Status (Select to assign)</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($permissions as $p)
                            @php
                                $inherited = $user->user_type_rec && $user->user_type_rec->permissions->contains('id', $p->id);
                                $direct = in_array($p->id, $user_permissions);
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <strong>{{ $p->title }}</strong> <br>
                                    <small class="text-muted">{{ $p->name }}</small>
                                </td>
                                <td>
                                    @if($inherited)
                                        <i class="icon-check text-success" title="Inherited from Role"></i>
                                    @else
                                        <i class="icon-cross2 text-danger" title="Not Inherited"></i>
                                    @endif
                                </td>
                                <td>
                                    <div class="form-check form-check-switchery">
                                        <label class="form-check-label">
                                            <input name="permissions[]" value="{{ $p->id }}" type="checkbox" class="form-check-input-switchery" {{ ($inherited || $direct) ? 'checked' : '' }} data-fouc>
                                        </label>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="text-right mt-3">
                    <button type="submit" class="btn btn-primary">Update Permissions <i class="icon-paperplane ml-2"></i></button>
                    <a href="{{ route('users.index') }}" class="btn btn-light ml-2">Back to Users</a>
                </div>
            </form>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        // Any additional scripts if needed
    </script>
@endsection
