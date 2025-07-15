{{-- Close button above card, right-aligned and styled --}}
<div class="d-flex justify-content-end pt-3 pe-3 mb-2">
    <button type="button" class="btn btn-sm btn-outline-primary shadow-sm px-3" data-bs-dismiss="modal" aria-label="Close">
        &times; Close
    </button>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0 text-start">
            <strong>User Form - {{ $isEdit ?? false ? 'Edit' : 'Create' }}</strong>
        </h5>
    </div>

    <div class="card-body">
        <form method="POST" action="{{ $action ?? route('plant-user.store') }}" id="plantUserForm" class="ajax-form"
            enctype="multipart/form-data">
            @csrf
            @if (!empty($method) && $method !== 'POST')
                @method($method)
            @endif

            {{-- Error Block --}}
            <div id="form-errors" class="alert alert-danger d-none">
                <ul class="mb-0" id="form-error-list"></ul>
            </div>

            {{-- Name & Email --}}
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="name" class="form-label text-start w-100">Name</label>
                    <input name="name" id="name" type="text" value="{{ $user->name ?? '' }}"
                        class="form-control" required>
                    <div class="invalid-feedback d-block text-start" id="error-name"></div>
                </div>

                <div class="col-md-6">
                    <label for="email" class="form-label text-start w-100">Email</label>
                    <input name="email" id="email" type="email" value="{{ $user->email ?? '' }}"
                        class="form-control" required>
                    <div class="invalid-feedback d-block text-start" id="error-email"></div>
                </div>
            </div>

            {{-- Mobile & Plants --}}
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="mobile" class="form-label text-start w-100">Mobile</label>
                    <input name="mobile" id="mobile" type="text" value="{{ $user->mobile ?? '' }}"
                        class="form-control">
                    <div class="invalid-feedback d-block text-start" id="error-mobile"></div>
                </div>

                <div class="col-md-6">
                    <label for="plants" class="form-label text-start w-100">Assign Plants</label>
                    <select name="plants[]" id="plants" class="form-select select2" multiple>
                        @foreach ($plants as $plant)
                            <option value="{{ $plant->id }}"
                                {{ isset($user) && $user->plants->pluck('id')->contains($plant->id) ? 'selected' : '' }}>
                                {{ $plant->name }}
                            </option>
                        @endforeach
                    </select>
                    <div class="invalid-feedback d-block text-start" id="error-plants"></div>
                </div>
            </div>

            {{-- Password & Confirm Password --}}
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="password" class="form-label text-start w-100">Password</label>
                    <input name="password" id="password" type="password" class="form-control"
                        {{ $isEdit ?? false ? '' : 'required' }}>
                    <div class="invalid-feedback d-block text-start" id="error-password"></div>
                </div>

                <div class="col-md-6">
                    <label for="password_confirmation" class="form-label text-start w-100">Confirm Password</label>
                    <input name="password_confirmation" id="password_confirmation" type="password" class="form-control"
                        {{ $isEdit ?? false ? '' : 'required' }}>
                    <div class="invalid-feedback d-block text-start" id="error-password_confirmation"></div>
                </div>
            </div>

            {{-- Profile Picture --}}
            @if ($user->profile_picture == true)
                <div class="mb-3">
                    <label for="profile_picture" class="form-label text-start w-100">Profile Picture</label>
                    <input type="file" class="form-control" id="profile_picture" name="profile_picture"
                        accept="image/*">
                    <div class="invalid-feedback d-block" id="error-profile_picture"></div>
                </div>

                @if (!empty($isEdit) && $user->profile_picture)
                    <div class="mb-3">
                        <label class="form-label text-start w-100">Current Picture</label><br>
                        <img src="{{ asset('storage/' . $user->profile_picture) }}" class="rounded shadow-sm"
                            width="100" alt="Profile Picture">
                    </div>
                @endif
            @endif

            {{-- Submit Buttons --}}
            <div class="mt-4 d-flex justify-content-end gap-2">
                <button type="submit" class="btn btn-primary d-flex align-items-center px-4">
                    {{ $isEdit ?? false ? 'Update' : 'Create' }}
                </button>
                <button type="button" class="btn btn-secondary d-flex align-items-center px-4" data-bs-dismiss="modal">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>
