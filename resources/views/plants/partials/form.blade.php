{{-- Set modal title dynamically --}}
<div data-modal-title="{{ $isEdit ?? false ? 'Edit Plant' : 'Add New Plant' }}"></div>

{{-- Close button (above card, right-aligned) --}}
<div class="d-flex justify-content-end pt-3 pe-3 mb-2">
    <button type="button" class="btn btn-sm btn-outline-primary shadow-sm px-3" data-bs-dismiss="modal" aria-label="Close">
        &times; Close
    </button>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0 text-start">
            <strong>Plant Form - {{ $isEdit ?? false ? 'Edit' : 'Create' }}</strong>
        </h5>
    </div>

    <div class="card-body">
        <form method="POST" action="{{ $action ?? route('plants.store') }}" class="ajax-form" id="plantForm"
            enctype="multipart/form-data">
            @csrf
            @if (!empty($method) && $method !== 'POST')
                @method($method)
            @endif

            {{-- Global Error Block --}}
            <div id="form-errors" class="alert alert-danger d-none">
                <ul class="mb-0" id="form-error-list"></ul>
            </div>

            {{-- Name & Code --}}
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="name" class="form-label text-start w-100">Name</label>
                    <input name="name" id="name" type="text" value="{{ old('name', $plant->name ?? '') }}"
                        class="form-control" required>
                    <div class="invalid-feedback d-block text-start" id="error-name"></div>
                </div>

                <div class="col-md-6">
                    <label for="code" class="form-label text-start w-100">Code</label>
                    <input name="code" id="code" type="text" value="{{ old('code', $plant->code ?? '') }}"
                        class="form-control" required>
                    <div class="invalid-feedback d-block text-start" id="error-code"></div>
                </div>
            </div>

            {{-- Optional: Upload Logo or Image (uncomment if needed) --}}
            {{-- 
            <div class="mb-3">
                <label for="image" class="form-label text-start w-100">Image (optional)</label>
                <input type="file" class="form-control" id="image" name="image" accept="image/*">
                <div class="invalid-feedback d-block" id="error-image"></div>
            </div>
            --}}
            {{-- Is Warehouse (CoreUI styled checkbox) --}}
            <div class="row mb-3 align-items-center">
                <div class="col-md-3 text-end">
                    <label for="is_warehouse" class="form-label mb-0">
                        Is Warehouse?
                    </label>
                </div>
                <div class="col-md-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="is_warehouse" name="is_warehouse"
                            value="1" {{ old('is_warehouse', $plant->is_warehouse ?? false) ? 'checked' : '' }}>
                    </div>
                    <div class="invalid-feedback d-block" id="error-is_warehouse"></div>
                </div>
            </div>
            {{-- Submit & Cancel Buttons --}}
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
