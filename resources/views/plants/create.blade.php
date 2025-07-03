 <div data-modal-title="Add New Plant"></div>
 <div class="card-body">
     <form action="{{ route('plants.store') }}" method="POST">
         @csrf

         <div class="form-group">
             <label for="name">Name</label>
             <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" id="name"
                 value="{{ old('name') }}" required>
             @error('name')
                 <span class="invalid-feedback">{{ $message }}</span>
             @enderror
         </div>

         <div class="form-group">
             <label for="code">Code</label>
             <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                 id="code" value="{{ old('code') }}" required>
             @error('code')
                 <span class="invalid-feedback">{{ $message }}</span>
             @enderror
         </div>
         <div class="d-flex justify-content-end mt-3">
             <button type="submit" class="btn btn-primary mr-2">
                 <i class="fas fa-save mr-1"></i> Create Plant
             </button>
             <a href="{{ route('plants.index') }}" class="btn btn-secondary">
                 <i class="fas fa-times-circle mr-1"></i> Cancel
             </a>
         </div>
     </form>
 </div>
