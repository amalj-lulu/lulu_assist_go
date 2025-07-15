{{-- Dynamic Modal Title --}}
<span data-modal-title="{{ $isEdit ?? false ? 'Edit Plant User' : 'Add New Plant User' }}" style="display: none;"></span>

<div class="modal-body p-0">
    <div class="card border-0 shadow-sm m-3">
        <div class="card-body">

            {{-- Error Container for AJAX --}}
            <div id="form-errors" class="alert alert-danger d-none">
                <ul class="mb-0" id="form-error-list"></ul>
            </div>

            {{-- Form --}}
            <form method="POST"
                  action="{{ $action ?? route('plant-user.store') }}"
                  id="plantUserForm"
                  enctype="multipart/form-data">

                @csrf
                @if(($method ?? 'POST') !== 'POST')
                    @method($method)
                @endif

                <div class="row">
                    {{-- Name --}}
                    <div class="col-md-6 mb-3">
                        <label for="name">Name</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                            </div>
                            <input name="name" id="name" value="{{ old('name', $user->name ?? '') }}"
                                   class="form-control">
                        </div>
                    </div>

                    {{-- Email --}}
                    <div class="col-md-6 mb-3">
                        <label for="email">Email</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            </div>
                            <input type="email" name="email" id="email" value="{{ old('email', $user->email ?? '') }}"
                                   class="form-control">
                        </div>
                    </div>

                    {{-- Password --}}
                    <div class="col-md-6 mb-3">
                        <label for="password">Password</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            </div>
                            <input type="password" name="password" id="password" class="form-control"
                                   {{ $isEdit ?? false ? '' : 'required' }}>
                        </div>
                    </div>

                    {{-- Confirm Password --}}
                    <div class="col-md-6 mb-3">
                        <label for="password_confirmation">Confirm Password</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-check-circle"></i></span>
                            </div>
                            <input type="password" name="password_confirmation" id="password_confirmation"
                                   class="form-control" {{ $isEdit ?? false ? '' : 'required' }}>
                        </div>
                    </div>

                    {{-- Mobile --}}
                    <div class="col-md-6 mb-3">
                        <label for="mobile">Mobile</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-phone"></i></span>
                            </div>
                            <input name="mobile" id="mobile" value="{{ old('mobile', $user->mobile ?? '') }}"
                                   class="form-control">
                        </div>
                    </div>

                    {{-- Plants --}}
                    <div class="col-md-6 mb-3">
                        <label for="plants">Assign Plants</label>
                        <select name="plants[]" id="plants"
                                class="form-control select2"
                                multiple data-placeholder="Select Plants" style="width: 100%;">
                            @foreach ($plants as $plant)
                                <option value="{{ $plant->id }}"
                                    {{ in_array($plant->id, old('plants', $user->plants->pluck('id')->toArray() ?? [])) ? 'selected' : '' }}>
                                    {{ $plant->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Profile Picture --}}
                    <div class="col-md-6 mb-3">
                        <label for="profile_picture">Profile Picture</label>
                        <div class="custom-file">
                            <input type="file"
                                   class="custom-file-input"
                                   id="profile_picture" name="profile_picture" accept="image/*">
                            <label class="custom-file-label" for="profile_picture">Choose file</label>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Footer --}}
<div class="modal-footer">
    <button type="submit" form="plantUserForm" class="btn btn-primary">
        <i class="fas fa-save mr-1"></i> {{ $isEdit ?? false ? 'Update' : 'Create' }}
    </button>
    <button type="button" class="btn btn-secondary" data-dismiss="modal">
        <i class="fas fa-times-circle mr-1"></i> Cancel
    </button>
</div>
