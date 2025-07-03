<div data-modal-title="Edit Plant"></div>

<div class="card-body">
    {{-- Validation Error Alert (for non-field specific errors) --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li><i class="fas fa-exclamation-circle text-danger mr-1"></i> {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('plants.update', $plant) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- Name --}}
        <div class="form-group">
            <label for="name">Name</label>
            <input type="text"
                   name="name"
                   id="name"
                   value="{{ old('name', $plant->name) }}"
                   class="form-control @error('name') is-invalid @enderror"
                   required>
            @error('name')
                <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
            @enderror
        </div>

        {{-- Code --}}
        <div class="form-group">
            <label for="code">Code</label>
            <input type="text"
                   name="code"
                   id="code"
                   value="{{ old('code', $plant->code) }}"
                   class="form-control @error('code') is-invalid @enderror"
                   required>
            @error('code')
                <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
            @enderror
        </div>

        {{-- Buttons --}}
        <div class="d-flex justify-content-end mt-3">
            <button type="submit" class="btn btn-primary mr-2">
                <i class="fas fa-save mr-1"></i> Update Plant
            </button>
            <button type="button" class="btn btn-secondary" data-dismiss="modal">
                <i class="fas fa-times-circle mr-1"></i> Cancel
            </button>
        </div>
    </form>
</div>