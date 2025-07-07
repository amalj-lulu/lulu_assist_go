{{-- Modal Title Setter (used in JS to update modal header) --}}
<span data-modal-title="Add New Plant User" style="display: none;"></span>

<div class="modal-body p-0">
    <div class="card border-0 shadow-sm m-3">
        <div class="card-body">
            {{-- Validation Errors --}}
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li><i class="fas fa-exclamation-circle mr-1 text-danger"></i> {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Form --}}
            <form method="POST" action="{{ route('plant-user.store') }}" id="plantUserForm" enctype="multipart/form-data">
                @csrf

                <div class="row">
                    {{-- Name --}}
                    <div class="col-md-6 mb-3">
                        <label for="name">Name</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                            </div>
                            <input name="name" id="name" value="{{ old('name') }}"
                                class="form-control @error('name') is-invalid @enderror" required>
                        </div>
                        @error('name')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div class="col-md-6 mb-3">
                        <label for="email">Email</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            </div>
                            <input type="email" name="email" id="email" value="{{ old('email') }}"
                                class="form-control @error('email') is-invalid @enderror" required>
                        </div>
                        @error('email')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div class="col-md-6 mb-3">
                        <label for="password">Password</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            </div>
                            <input type="password" name="password" id="password"
                                class="form-control @error('password') is-invalid @enderror" required>
                        </div>
                        @error('password')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- Confirm Password --}}
                    <div class="col-md-6 mb-3">
                        <label for="password_confirmation">Confirm Password</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-check-circle"></i></span>
                            </div>
                            <input type="password" name="password_confirmation" id="password_confirmation"
                                class="form-control" required>
                        </div>
                    </div>

                    {{-- Mobile --}}
                    <div class="col-md-6 mb-3">
                        <label for="mobile">Mobile</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-phone"></i></span>
                            </div>
                            <input name="mobile" id="mobile" value="{{ old('mobile') }}"
                                class="form-control @error('mobile') is-invalid @enderror">
                        </div>
                        @error('mobile')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- Plants --}}
                    <div class="col-md-6 mb-3">
                        <label for="plants">Assign Plants</label>
                        <select name="plants[]" id="plants"
                            class="form-control select2 @error('plants') is-invalid @enderror" multiple
                            data-placeholder="Select Plants" style="width: 100%;">
                            @foreach ($plants as $plant)
                                <option value="{{ $plant->id }}"
                                    {{ in_array($plant->id, old('plants', [])) ? 'selected' : '' }}>
                                    {{ $plant->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('plants')
                            <small class="text-danger d-block">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="profile_picture">Profile Picture</label>
                        <div class="custom-file">
                            <input type="file"
                                class="custom-file-input @error('profile_picture') is-invalid @enderror"
                                id="profile_picture" name="profile_picture" accept="image/*">
                            <label class="custom-file-label" for="profile_picture">Choose file</label>
                        </div>
                        @error('profile_picture')
                            <small class="text-danger d-block">{{ $message }}</small>
                        @enderror
                    </div>

                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Footer --}}
<div class="modal-footer">
    <button type="submit" form="plantUserForm" class="btn btn-primary">
        <i class="fas fa-save mr-1"></i> Create
    </button>
    <button type="button" class="btn btn-secondary" data-dismiss="modal">
        <i class="fas fa-times-circle mr-1"></i> Cancel
    </button>
</div>
