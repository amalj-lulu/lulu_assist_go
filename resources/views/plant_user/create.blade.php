@extends('layouts.admin')

@section('title', 'Add Plant User')
@section('page-title', 'Add New Plant User')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Add New Plant User</h3>
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

        <form method="POST" action="{{ route('plant-user.store') }}">
            @csrf

            <div class="form-group">
                <label for="name">Name</label>
                <input name="name" id="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" required>
                @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input name="email" id="email" type="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" required>
                @error('email')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label for="mobile">Mobile</label>
                <input name="mobile" id="mobile" value="{{ old('mobile') }}" class="form-control @error('mobile') is-invalid @enderror">
                @error('mobile')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input name="password" id="password" type="password" class="form-control @error('password') is-invalid @enderror" required>
                @error('password')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirm Password</label>
                <input name="password_confirmation" id="password_confirmation" type="password" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="plants">Assign Plants</label>
                <select name="plants[]" id="plants" class="form-control @error('plants') is-invalid @enderror" multiple>
                    @foreach ($plants as $plant)
                        <option value="{{ $plant->id }}" {{ (collect(old('plants'))->contains($plant->id)) ? 'selected' : '' }}>
                            {{ $plant->name }}
                        </option>
                    @endforeach
                </select>
                @error('plants')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>

            <button type="submit" class="btn btn-primary">Create User</button>
        </form>
    </div>
</div>
@endsection
