<section>
    <div class="mb-4">
        <h4 class="font-weight-bold">Update Password</h4>
        <p class="text-muted mb-0">
            Ensure your account is using a long, random password to stay secure.
        </p>
    </div>

    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        @method('put')

        <!-- Current Password -->
        <div class="form-group">
            <label for="current_password">Current Password</label>
            <input type="password"
                   name="current_password"
                   id="current_password"
                   class="form-control @error('current_password', 'updatePassword') is-invalid @enderror"
                   autocomplete="current-password">
            @error('current_password', 'updatePassword')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- New Password -->
        <div class="form-group">
            <label for="password">New Password</label>
            <input type="password"
                   name="password"
                   id="password"
                   class="form-control @error('password', 'updatePassword') is-invalid @enderror"
                   autocomplete="new-password">
            @error('password', 'updatePassword')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Confirm Password -->
        <div class="form-group">
            <label for="password_confirmation">Confirm New Password</label>
            <input type="password"
                   name="password_confirmation"
                   id="password_confirmation"
                   class="form-control"
                   autocomplete="new-password">
        </div>

        <!-- Save Button -->
        <div class="form-group mt-3">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save mr-1"></i> Save
            </button>

            @if (session('status') === 'password-updated')
                <span class="text-success ml-3">Saved.</span>
            @endif
        </div>
    </form>
</section>
