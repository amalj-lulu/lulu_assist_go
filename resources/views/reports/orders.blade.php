@extends('layouts.coreui.app')

@section('title', 'Order Report')

@section('content')
    <div class="card mb-4">
        {{-- Header --}}
        <div class="card-header d-flex justify-content-between align-items-center py-2">
            <strong>
                <i class="bi bi-bar-chart-line me-2" style="font-size: 1rem;"></i> Order Report
            </strong>
            <div class="d-flex gap-1">
                <a href="{{ route('order-report.export.pdf', request()->query()) }}" class="btn btn-sm btn-outline-danger"
                    data-bs-toggle="tooltip" title="Export as PDF">
                    <i class="bi bi-file-earmark-pdf-fill"></i>
                </a>
                <a href="{{ route('order-report.export.excel', request()->query()) }}"
                    class="btn btn-sm btn-outline-success" data-bs-toggle="tooltip" title="Export to Excel">
                    <i class="bi bi-file-earmark-excel-fill"></i>
                </a>
            </div>
        </div>

        <div class="card-body">
            {{-- Summary --}}
            @include('reports.partials.summary')

            {{-- Filters --}}
            <div class="border rounded p-3 mb-4 bg-light">
                <h6 class="fw-semibold mb-3 text-muted">Filter Orders</h6>
                <form method="GET">
                    {{-- Line 1 --}}
                    <div class="row g-3 mb-2 align-items-end">
                        <div class="col-md-4">
                            <label for="user_id" class="form-label fw-semibold">User</label>
                            <select name="user_id" id="user_id" class="form-select">
                                <option value="">All Users</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}"
                                        {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="plant_id" class="form-label fw-semibold">Plant</label>
                            <select name="plant_id" id="plant_id" class="form-select">
                                <option value="">All Plants</option>
                                @foreach ($plants as $plant)
                                    <option value="{{ $plant->id }}"
                                        {{ request('plant_id') == $plant->id ? 'selected' : '' }}>
                                        {{ $plant->code }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="period" class="form-label fw-semibold">Period</label>
                            <select name="period" id="period" class="form-select"
                                onchange="toggleDateInputs(this.value)">
                                <option value="daily" {{ request('period') == 'daily' ? 'selected' : '' }}>Today</option>
                                <option value="weekly" {{ request('period') == 'weekly' ? 'selected' : '' }}>This Week
                                </option>
                                <option value="monthly" {{ request('period') == 'monthly' ? 'selected' : '' }}>This Month
                                </option>
                                <option value="custom" {{ request('period') == 'custom' ? 'selected' : '' }}>Custom
                                </option>
                            </select>
                        </div>
                    </div>

                    {{-- Line 2 --}}
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label for="from_date" class="form-label fw-semibold">From</label>
                            <input type="date" class="form-control" name="from_date" id="from_date"
                                value="{{ request('from_date') }}">
                        </div>
                        <div class="col-md-4">
                            <label for="to_date" class="form-label fw-semibold">To</label>
                            <input type="date" class="form-control" name="to_date" id="to_date"
                                value="{{ request('to_date') }}">
                        </div>
                        <div class="col-md-4 text-end">
                            <button type="submit" class="btn btn-primary" data-bs-toggle="tooltip" title="Apply Filters">
                                <i class="bi bi-funnel-fill"></i> Apply
                            </button>
                            <a href="{{ route('order-report') }}" class="btn btn-outline-secondary"
                                data-bs-toggle="tooltip" title="Reset Filters">
                                <i class="bi bi-x-circle"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Orders Table --}}
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;"></th>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Item Count</th>
                            <th>Total Price (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($report as $index => $row)
                            <tr>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-info toggle-details" type="button"
                                        data-target="details-{{ $index }}" data-bs-toggle="tooltip"
                                        title="Expand/Collapse">
                                        <i class="bi bi-plus-lg"></i>
                                    </button>
                                </td>
                                <td>{{ $row['order_id'] }}</td>
                                <td>{{ $row['order_date'] }}</td>
                                <td><span class="badge bg-secondary">{{ $row['item_count'] }}</span></td>
                                <td>₹{{ number_format($row['total_price'], 2) }}</td>
                            </tr>
                            <tr id="details-{{ $index }}" class="d-none bg-light">
                                <td colspan="5" class="p-3">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered mb-0">
                                            <thead class="table-secondary">
                                                <tr>
                                                    <th>Product</th>
                                                    <th>Serial Number</th>
                                                    <th>Price</th>
                                                    <th>User</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($row['items'] as $item)
                                                    <tr>
                                                        <td>{{ $item['product_name'] }}</td>
                                                        <td>{{ $item['serial_number'] }}</td>
                                                        <td>₹{{ number_format($item['price'], 2) }}</td>
                                                        <td>{{ $item['user_name'] }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">No records found for the selected filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="mt-3">
                {{ $report->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function toggleDateInputs(period) {
            const from = document.getElementById('from_date');
            const to = document.getElementById('to_date');

            const show = period === 'custom';
            from.style.display = show ? 'block' : 'none';
            to.style.display = show ? 'block' : 'none';

            // Also toggle labels (optional, if you want)
            from.previousElementSibling.style.display = show ? 'block' : 'none';
            to.previousElementSibling.style.display = show ? 'block' : 'none';
        }

        document.addEventListener('DOMContentLoaded', () => {
            toggleDateInputs(document.getElementById('period').value);

            document.querySelectorAll('.toggle-details').forEach(button => {
                button.addEventListener('click', function() {
                    const targetId = this.getAttribute('data-target');
                    const row = document.getElementById(targetId);
                    const icon = this.querySelector('i');
                    row.classList.toggle('d-none');
                    icon.classList.toggle('bi-plus-lg');
                    icon.classList.toggle('bi-dash-lg');
                });
            });

            // Enable tooltips
            [...document.querySelectorAll('[data-bs-toggle="tooltip"]')].forEach(el => new bootstrap.Tooltip(el));
        });
    </script>
@endpush
