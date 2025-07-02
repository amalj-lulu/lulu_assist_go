@extends('layouts.admin')

@section('title', 'Edit Plant User')
@section('page-title', 'Edit Plant User')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Edit Plant User</h3>
        <a href="{{ route('plant-user.index') }}" class="btn btn-sm btn-secondary float-right">
            <i class="fas fa-arrow-left"></i> Back to Plant Users
        </a>
    </div>

    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('plant-user.update', $user) }}">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="name">Name</label>
                <input name="name" id="name" value="{{ old('name', $user->name) }}" class="form-control @error('name') is-invalid @enderror" required>
                @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input name="email" id="email" type="email" value="{{ old('email', $user->email) }}" class="form-control @error('email') is-invalid @enderror" required>
                @error('email')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label for="mobile">Mobile</label>
                <input name="mobile" id="mobile" value="{{ old('mobile', $user->mobile) }}" class="form-control @error('mobile') is-invalid @enderror">
                @error('mobile')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label for="password">Password <small>(leave blank to keep current)</small></label>
                <input name="password" id="password" type="password" class="form-control @error('password') is-invalid @enderror">
                @error('password')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirm Password</label>
                <input name="password_confirmation" id="password_confirmation" type="password" class="form-control">
            </div>

            <div class="form-group">
                <label for="role">Role</label>
                <select name="role" id="role" class="form-control @error('role') is-invalid @enderror" required>
                    <option value="user" {{ old('role', $user->role) == 'user' ? 'selected' : '' }}>User</option>
                    <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Admin</option>
                </select>
                @error('role')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label for="plants">Assign Plants</label>
                <select name="plants[]" id="plants" class="form-control @error('plants') is-invalid @enderror" multiple>
                    @foreach ($plants as $plant)
                        <option value="{{ $plant->id }}"
                            {{ in_array($plant->id, old('plants', $user->plants->pluck('id')->toArray())) ? 'selected' : '' }}>
                            {{ $plant->name }}
                        </option>
                    @endforeach
                </select>
                @error('plants')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>

            <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-primary">Update User</button>
                <a href="{{ route('plant-user.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
