@extends('layouts.admin')

@section('title', 'Customer Management')
@section('page-title', 'Customer Management')

@section('content')

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">Customers</h3>
        </div>

        <div class="card-body">
            @if ($customers->isEmpty())
                <p>No customers found.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Mobile</th>
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

        {{-- Pagination --}}
        <div class="card-footer clearfix">
            {{ $customers->links('pagination::bootstrap-4') }}
        </div>
    </div>

@endsection
