@extends('layouts.master')
@section('page_title', 'My Account')
@section('content')

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title">My Account</h6>
            {!! Qs::getPanelOptions() !!}
        </div>

        <div class="card-body">
            <ul class="nav nav-tabs nav-tabs-highlight">
                <li class="nav-item"><a href="#change-pass" class="nav-link active" data-toggle="tab">Change Password</a></li>
                <li class="nav-item"><a href="#edit-profile" class="nav-link" data-toggle="tab"><i class="icon-profile"></i> Manage Profile</a></li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="change-pass">
                    <div class="row">
                        <div class="col-md-8">
                            <form method="post" action="{{ route('my_account.change_pass') }}">
                                @csrf @method('put')

                                <div class="form-group row">
                                    <label for="current_password" class="col-lg-3 col-form-label font-weight-semibold">Current Password <span class="text-danger">*</span></label>
                                    <div class="col-lg-9">
                                        <input id="current_password" name="current_password"  required type="password" class="form-control">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="password" class="col-lg-3 col-form-label font-weight-semibold">New Password <span class="text-danger">*</span></label>
                                    <div class="col-lg-9">
                                        <input id="password" name="password"  required type="password" class="form-control">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="password_confirmation" class="col-lg-3 col-form-label font-weight-semibold">Confirm Password <span class="text-danger">*</span></label>
                                    <div class="col-lg-9">
                                        <input id="password_confirmation" name="password_confirmation"  required type="password" class="form-control">
                                    </div>
                                </div>

                                <div class="text-right">
                                    <button type="submit" class="btn btn-danger">Submit form <i class="icon-paperplane ml-2"></i></button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="edit-profile">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="card border-primary mb-3">
                                <div class="card-header bg-primary text-white">Account Snapshot</div>
                                <div class="card-body">
                                    <dl class="mb-0">
                                        <dt>Name</dt>
                                        <dd>{{ $my->name ?? '—' }}</dd>
                                        <dt>Username</dt>
                                        <dd>{{ $my->username ?? 'Not set' }}</dd>
                                        <dt>Email</dt>
                                        <dd>{{ $my->email ?? '—' }}</dd>
                                        <dt>Phone</dt>
                                        <dd>{{ $my->phone ?? '—' }}</dd>
                                        <dt>Telephone</dt>
                                        <dd>{{ $my->phone2 ?? '—' }}</dd>
                                        <dt>Address</dt>
                                        <dd>{{ $my->address ?? '—' }}</dd>
                                        <dt>Ward</dt>
                                        <dd>{{ $my->ward ?? '—' }}</dd>
                                        <dt>Street</dt>
                                        <dd>{{ $my->street ?? '—' }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-8">
                            <form enctype="multipart/form-data" method="post" action="{{ route('my_account.update') }}">
                                @csrf @method('put')

                                <div class="row">
                                    {{-- Column 1 --}}
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="name" class="font-weight-semibold">Name</label>
                                            <input disabled id="name" class="form-control" type="text" value="{{ $my->name }}">
                                            <small class="text-muted">Contact an administrator to change your official name.</small>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="username" class="font-weight-semibold">Username</label>
                                            @if($my->username)
                                                <input disabled id="username" class="form-control" type="text" value="{{ $my->username }}">
                                            @else
                                                <input id="username" name="username" type="text" class="form-control" placeholder="Enter username">
                                                @error('username')
                                                    <span class="form-text text-danger">{{ $message }}</span>
                                                @enderror
                                            @endif
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="email" class="font-weight-semibold">Email</label>
                                            <input id="email" value="{{ $my->email }}" name="email" type="email" class="form-control" placeholder="name@example.com">
                                            @error('email')
                                                <span class="form-text text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    {{-- Column 2 --}}
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="phone" class="font-weight-semibold">Phone</label>
                                            <input id="phone" value="{{ $my->phone }}" name="phone" type="text" class="form-control" placeholder="Primary mobile number">
                                            @error('phone')
                                                <span class="form-text text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="phone2" class="font-weight-semibold">Telephone</label>
                                            <input id="phone2" value="{{ $my->phone2 }}" name="phone2" type="text" class="form-control" placeholder="Secondary contact (optional)">
                                            @error('phone2')
                                                <span class="form-text text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="address" class="font-weight-semibold">Address</label>
                                            <input id="address" value="{{ $my->address }}" name="address" type="text" class="form-control" placeholder="Street address">
                                            @error('address')
                                                <span class="form-text text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    {{-- Column 3 --}}
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="nal_id" class="font-weight-semibold">Nationality</label>
                                            <select onchange="getStatesByCountry(this.value)" data-placeholder="Choose..." name="nal_id" id="nal_id" class="select-search form-control">
                                                <option value=""></option>
                                                @foreach($nationals ?? [] as $nal)
                                                    <option value="{{ $nal->id }}" {{ ($my->nal_id == $nal->id) ? 'selected' : '' }}>{{ $nal->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('nal_id')
                                                <span class="form-text text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="state_id" class="font-weight-semibold">State / Region</label>
                                            <select onchange="getLGA(this.value)" data-placeholder="Choose.." class="select-search form-control" name="state_id" id="state_id">
                                                <option value=""></option>
                                                @foreach($states ?? [] as $st)
                                                    <option value="{{ $st->id }}" {{ ($my->state_id == $st->id ? 'selected' : '') }}>{{ $st->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('state_id')
                                                <span class="form-text text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="lga_id" class="font-weight-semibold">District</label>
                                            <select onchange="getWards(this.value)" data-placeholder="Select State First" class="select-search form-control" name="lga_id" id="lga_id">
                                                <option value="{{ $my->lga_id ?? '' }}">{{ optional($my->lga ?? null)->name ?? '' }}</option>
                                            </select>
                                            @error('lga_id')
                                                <span class="form-text text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    {{-- Column 4 (location details + places + photo) --}}
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="ward" class="font-weight-semibold">Ward</label>
                                            <select onchange="getVillages(this.value)" data-placeholder="Choose.." class="select-search form-control" name="ward" id="ward">
                                                <option value="{{ $my->ward }}">{{ $my->ward }}</option>
                                            </select>
                                            @error('ward')
                                                <span class="form-text text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="street" class="font-weight-semibold">Street / Village</label>
                                            <select onchange="getPlaces(this.value)" data-placeholder="Choose.." class="select-search form-control" name="street" id="street">
                                                <option value="{{ $my->street }}">{{ $my->street }}</option>
                                            </select>
                                            @error('street')
                                                <span class="form-text text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="place_id" class="font-weight-semibold">Places</label>
                                            <select data-placeholder="Choose.." class="select-search form-control" name="place_id" id="place_id">
                                                <option value="{{ $my->place_id ?? '' }}">{{ optional($my->place ?? null)->name ?? '' }}</option>
                                            </select>
                                            @error('place_id')
                                                <span class="form-text text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="photo" class="font-weight-semibold">Change Photo</label>
                                            <div class="custom-file">
                                                <input id="photo" accept="image/*" type="file" name="photo" class="custom-file-input">
                                                <label class="custom-file-label" for="photo">Choose image...</label>
                                            </div>
                                            <span class="form-text text-muted">Accepted formats: jpeg, png, gif (max 2MB).</span>
                                            @error('photo')
                                                <span class="form-text text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="card mt-3">
                                    <div class="card-header">Current Photo</div>
                                    <div class="card-body text-center">
                                        <img id="photo-preview" src="{{ $my->photo }}" alt="Current photo" class="rounded-circle mb-2" style="width: 110px; height: 110px; object-fit: cover;">
                                        <p class="text-muted mb-0">This is the picture that is shown across the dashboard.</p>
                                    </div>
                                </div>

                                <div class="text-right mt-3">
                                    <button type="submit" class="btn btn-primary">Save Changes <i class="icon-paperplane ml-2"></i></button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @parent
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var photoInput = document.getElementById('photo');
            if (!photoInput) {
                return;
            }

            photoInput.addEventListener('change', function () {
                var label = this.nextElementSibling;
                var fileName = this.files && this.files.length ? this.files[0].name : 'Choose image...';
                if (label) {
                    label.textContent = fileName;
                }

                if (this.files && this.files[0]) {
                    var preview = document.getElementById('photo-preview');
                    if (preview) {
                        preview.src = URL.createObjectURL(this.files[0]);
                    }
                }
            });
        });
    </script>
@endsection
