@extends('layouts.coreui.app')

@section('title', 'Customer Management')
@section('page-title', 'Customer Management')

@section('content')

@if (session('success'))
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
@endif

<div class="card mb-4 shadow-sm">
  <div class="card-header d-flex justify-content-between align-items-center">
    <div>
      <strong>Customers</strong>
      <span class="text-muted small">({{ $customers->total() }} total)</span>
    </div>
  </div>

  <div class="card-body">
    @if ($customers->isEmpty())
      <p class="text-muted">No customers found.</p>
    @else
      <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
          <thead class="table-light">
            <tr>
              <th scope="col">Name</th>
              <th scope="col">Mobile</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($customers as $customer)
              <tr>
                <td>{{ $customer->name }}</td>
                <td>{{ $customer->mobile ?? '-' }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>

  @if ($customers->hasPages())
    <div class="card-footer d-flex justify-content-center">
      {{ $customers->links('pagination::bootstrap-4') }}
    </div>
  @endif
</div>

@endsection
