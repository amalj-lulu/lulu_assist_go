@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Admin Dashboard')

@section('content')
<div class="row">
    <!-- Orders Overview Card -->
    <div class="col-lg-4 col-12 mb-4">
        <div class="card shadow-lg border-light rounded">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h3 class="card-title">Total Orders</h3>
                <i class="fas fa-box-open fa-2x"></i>
            </div>
            <div class="card-body text-center">
                <h2 class="display-4 font-weight-bold">150</h2>
                <p class="text-muted">Orders placed this month</p>
            </div>
            <div class="card-footer bg-light text-center">
                <a href="#" class="btn btn-link">View Details</a>
            </div>
        </div>
    </div>

    <!-- User Registrations Overview Card -->
    <div class="col-lg-4 col-12 mb-4">
        <div class="card shadow-lg border-light rounded">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <h3 class="card-title">User Registrations</h3>
                <i class="fas fa-user-plus fa-2x"></i>
            </div>
            <div class="card-body text-center">
                <h2 class="display-4 font-weight-bold">{{$userCount}}</h2>
                <p class="text-muted">New users registered this month</p>
            </div>
            <div class="card-footer bg-light text-center">
                <a href="#" class="btn btn-link">View Details</a>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<!-- No sales graph script needed anymore -->
@endpush

@push('styles')
<style>
    /* Custom Styling */
    .card {
        border-radius: 15px;
        transition: transform 0.3s ease-in-out, box-shadow 0.3s ease;
    }
    
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }

    .card-header {
        background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
        border-radius: 15px 15px 0 0;
    }

    .card-title {
        font-weight: bold;
    }

    .card-footer {
        background-color: #f8f9fa;
        border-radius: 0 0 15px 15px;
    }

    .btn-link {
        color: #007bff;
        text-decoration: none;
        font-size: 14px;
    }

    .btn-link:hover {
        text-decoration: underline;
    }

    .fa-2x {
        font-size: 2em;
    }

    .display-4 {
        font-size: 3rem;
        font-weight: bold;
    }

    .text-muted {
        color: #6c757d;
    }
</style>
@endpush
