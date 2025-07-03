@extends('layouts.admin')

@section('title', 'Edit Plant')
@section('page-title', 'Edit Plant: ' . $plant->name)

@section('content')
    <div class="card">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0"> Edit Plant</h3>
        </div>

        <div class="card-body">
            <form action="{{ route('plants.update', $plant) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                        id="name" value="{{ old('name', $plant->name) }}" required>
                    @error('name')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="code">Code</label>
                    <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                        id="code" value="{{ old('code', $plant->code) }}" required>
                    @error('code')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
                <div class="d-flex justify-content-end mt-3">
                    <button type="submit" class="btn btn-primary mr-2">
                        <i class="fas fa-save mr-1"></i> Update Plant
                    </button>
                    <a href="{{ route('plant-user.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times-circle mr-1"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
