@extends('layouts.admin')

@section('title', 'Create Plant')
@section('page-title', 'Create New Plant')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Add New Plant</h3>
        <a href="{{ route('plants.index') }}" class="btn btn-sm btn-secondary float-right">
            <i class="fas fa-arrow-left"></i> Back to Plants
        </a>
    </div>

    <div class="card-body">
        <form action="{{ route('plants.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" id="name" value="{{ old('name') }}" required>
                @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label for="code">Code</label>
                <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" id="code" value="{{ old('code') }}" required>
                @error('code')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label for="location">Location</label>
                <input type="text" name="location" class="form-control @error('location') is-invalid @enderror" id="location" value="{{ old('location') }}" required>
                @error('location')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>

            <button type="submit" class="btn btn-primary">Create Plant</button>
        </form>
    </div>
</div>
@endsection
