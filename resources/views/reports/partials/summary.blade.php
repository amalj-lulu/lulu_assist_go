<div class="row mb-4">
    <div class="col-md-4">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <div class="text-value">{{ $totalOrders }}</div>
                <div>Total Orders</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-success">
            <div class="card-body">
                <div class="text-value">{{ $totalItems }}</div>
                <div>Total Items</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-info">
            <div class="card-body">
                <div class="text-value">₹{{ number_format($totalPrice, 2) }}</div>
                <div>Total Price</div>
            </div>
        </div>
    </div>
</div>
