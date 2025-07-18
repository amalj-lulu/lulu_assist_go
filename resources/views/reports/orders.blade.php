@extends('layouts.coreui.app')

@section('title', 'Order Report')

@section('content')
    <div class="card mb-4">
        <div class="card-header">
            <strong>Order Report</strong>
        </div>

        <div class="card-body">
            {{-- Filter Form --}}
            <form method="GET" class="row g-3 mb-4 align-items-end">
                <div class="col-md-4">
                    <label for="user_id" class="form-label">User</label>
                    <select name="user_id" id="user_id" class="form-select">
                        <option value="">All Users</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="period" class="form-label">Period</label>
                    <select name="period" id="period" class="form-select">
                        <option value="daily" {{ request('period') == 'daily' ? 'selected' : '' }}>Today</option>
                        <option value="weekly" {{ request('period') == 'weekly' ? 'selected' : '' }}>This Week</option>
                        <option value="monthly" {{ request('period') == 'monthly' ? 'selected' : '' }}>This Month</option>
                    </select>
                </div>

                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-filter"></i> Apply Filters
                    </button>
                    <a href="#" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-x-lg"></i> Reset
                    </a>
                </div>
            </form>

            {{-- Orders Table --}}
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;"></th>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Item Count</th>
                            <th>Total Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($report as $index => $row)
                            <tr>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary toggle-details"
                                            type="button"
                                            data-target="details-{{ $index }}">+
                                    </button>
                                </td>
                                <td>{{ $row['order_id'] }}</td>
                                <td>{{ $row['order_date'] }}</td>
                                <td>{{ $row['item_count'] }}</td>
                                <td>{{ number_format($row['total_price'], 2) }}</td>
                            </tr>
                            <tr id="details-{{ $index }}" class="d-none bg-light">
                                <td colspan="5">
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
                                                        <td>{{ number_format($item['price'], 2) }}</td>
                                                        <td>{{ $item['user_name'] }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Toggle the visibility of the serial number rows
        document.querySelectorAll('.toggle-details').forEach(button => {
            button.addEventListener('click', function () {
                const targetId = this.getAttribute('data-target');
                const detailRow = document.getElementById(targetId);
                detailRow.classList.toggle('d-none');
                this.textContent = detailRow.classList.contains('d-none') ? '+' : '–';
            });
        });
    });
</script>
@endpush
