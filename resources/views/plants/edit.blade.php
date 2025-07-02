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
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" id="name" value="{{ old('name', $plant->name) }}" required>
                @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label for="code">Code</label>
                <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" id="code" value="{{ old('code', $plant->code) }}" required>
                @error('code')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>

            <button type="submit" class="btn btn-warning">Update Plant</button>
        </form>
    </div>
</div>
@endsection
