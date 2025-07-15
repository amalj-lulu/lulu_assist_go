@extends('layouts.coreui.app')

@section('title', 'Dashboard')
@section('page-title', 'Admin Dashboard')

@section('content')

    <div class="w-100 d-flex justify-content-end mb-3">
        <form method="GET" action="{{ route('dashboard') }}" class="d-flex align-items-center gap-2">
            <label for="plant_filter" class="form-label mb-0 me-2">Filter by Plant:</label>
            <select name="plant_id" id="plant_filter" class="form-select select2" style="width: 250px;"
                onchange="this.form.submit()">
                <option value="">All Plants</option>
                @foreach ($plants as $plant)
                    <option value="{{ $plant->id }}" {{ request('plant_id') == $plant->id ? 'selected' : '' }}>
                        {{ $plant->name }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    {{-- 📊 Dashboard Widgets --}}
    <div class="row">
        <!-- Orders Overview Card -->
         <div class="col-lg-3 col-12 mb-4">
            <div class="card shadow-lg border-light rounded">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0">New Orders</h6>
                    <i class="fas fa-user-plus fa-sm"></i>
                </div>
                <div class="card-body text-center">
                    <h4 class="fw-bold mb-1">{{ $orderCount }}</h4>
                    <p class="text-muted small mb-0">Orders placed this month</p>
                </div>
                <div class="card-footer bg-light text-center py-2">
                    <a href="#" class="btn btn-sm btn-link text-decoration-none">View
                        Details</a>
                </div>
            </div>
        </div>
        <!-- User Registrations Overview Card -->
        <div class="col-lg-3 col-12 mb-4">
            <div class="card shadow-lg border-light rounded">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0">New Users</h6>
                    <i class="fas fa-user-plus fa-sm"></i>
                </div>
                <div class="card-body text-center">
                    <h4 class="fw-bold mb-1">{{ $userCount }}</h4>
                    <p class="text-muted small mb-0">New users</p>
                </div>
                <div class="card-footer bg-light text-center py-2">
                    <a href="{{ route('plant-user.index') }}" class="btn btn-sm btn-link text-decoration-none">View
                        Details</a>
                </div>
            </div>
        </div>

        <!-- Customer Registrations Overview Card -->

        <div class="col-lg-3 col-12 mb-4">
            <div class="card shadow-lg border-light rounded">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0">New Customers</h6>
                    <i class="fas fa-user-plus fa-sm"></i>
                </div>
                <div class="card-body text-center">
                    <h4 class="fw-bold mb-1">{{ $customerCount }}</h4>
                    <p class="text-muted small mb-0">Customers</p>
                </div>
                <div class="card-footer bg-light text-center py-2">
                    <a href="{{ route('customers.index') }}" class="btn btn-sm btn-link text-decoration-none">View
                        Details</a>
                </div>
            </div>
        </div>

        <!-- Orders Overview Card -->

        <div class="col-lg-3 col-12 mb-4">
            <div class="card shadow-lg border-light rounded">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0">Orders</h6>
                    <i class="fas fa-user-plus fa-sm"></i>
                </div>
                <div class="card-body text-center">
                    <h4 class="fw-bold mb-1">{{ $customerCount }}</h4>
                    <p class="text-muted small mb-0">Orders</p>
                </div>
                <div class="card-footer bg-light text-center py-2">
                    <a href="{{ route('customers.index') }}" class="btn btn-sm btn-link text-decoration-none">View
                        Details</a>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4 shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center bg-primary text-white ">
                        <h5 class="mb-0 fs-6">Conversion Rate by Plant User</h5>
                        <i class="fas fa-chart-bar fa-lg"></i>
                    </div>
                    <div class="card-body" style="height: 300px;">
                        <canvas id="conversionRateChart" height="120"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card mb-4 shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center bg-primary text-white">
                        <h5 class="mb-0 fs-6">Material Category-wise Order</h5>
                        <i class="fas fa-chart-bar fa-lg"></i>
                    </div>
                    <div class="card-body" style="height: 300px;">
                        <canvas id="materialCategoryOrderChart" height="120"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center bg-info text-white">
                <h6 class="mb-0">Orders Trend</h6>
                <i class="fas fa-chart-line fa-lg"></i>
            </div>
            <div class="card-body" style="height: 300px;">
                <canvas id="ordersChart"></canvas>
            </div>
        </div>
        <div class="card mb-4 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center bg-secondary text-white">
                <h6 class="mb-0">User-wise Sales Report</h6>
                <i class="fas fa-chart-bar fa-lg"></i>
            </div>
            <div class="card-body" style="height: 300px;">
                <canvas id="userSalesChart"></canvas>
            </div>
        </div>
    </div>


@endsection

@push('scripts')
    {{-- Chart.js CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    {{-- Orders Chart --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const ctxUserSales = document.getElementById('userSalesChart').getContext('2d');
            const salesChartData = {!! $userWiseSalesJson !!};
            new Chart(ctxUserSales, {
                type: 'bar',
                data: salesChartData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                color: '#6c757d'
                            },
                            grid: {
                                color: '#e9ecef'
                            }
                        },
                        x: {
                            ticks: {
                                color: '#6c757d'
                            },
                            grid: {
                                color: '#f8f9fa'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            labels: {
                                color: '#495057',
                                font: {
                                    weight: 'bold'
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: '#343a40',
                            titleColor: '#fff',
                            bodyColor: '#fff'
                        }
                    }
                }
            });
        });
        document.addEventListener("DOMContentLoaded", function() {
            const ctx = document.getElementById('ordersChart').getContext('2d');
            const orderTrendData = {!! $orderTrendJson !!};
            new Chart(ctx, {
                type: 'line',
                data: orderTrendData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                color: '#6c757d',
                                stepSize: 50
                            },
                            grid: {
                                color: '#e9ecef'
                            }
                        },
                        x: {
                            ticks: {
                                color: '#6c757d'
                            },
                            grid: {
                                color: '#f1f3f5'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            labels: {
                                color: '#495057',
                                font: {
                                    weight: 'bold'
                                }
                            }
                        },
                        tooltip: {
                            enabled: true,
                            backgroundColor: '#0d6efd',
                            titleColor: '#fff',
                            bodyColor: '#fff'
                        }
                    }
                }
            });
        });
        document.addEventListener("DOMContentLoaded", function() {
            if (window.jQuery) {
                $('#plant_filter').select2({
                    placeholder: 'Select Plant',
                    allowClear: true,
                    width: 'resolve'
                });
            } else {
                console.error(' jQuery not loaded');
            }
        });
        const conversionRateData = {!! $conversionRateChartJson !!};

        new Chart(document.getElementById('conversionRateChart').getContext('2d'), {
            type: 'pie',
            data: conversionRateData,
            options: {
                responsive: true,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                return `${ctx.label}: ${ctx.parsed}%`;
                            }
                        }
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
        const materialOrderData = {!! $materialCategoryOrderJson !!};

        new Chart(document.getElementById('materialCategoryOrderChart').getContext('2d'), {
            type: 'pie',
            data: materialOrderData,
            options: {
                responsive: true,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                return `${ctx.label}: ${ctx.parsed} items`;
                            }
                        }
                    },
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#333'
                        }
                    }
                }
            }
        });
    </script>
@endpush

@push('styles')
    <style>
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
