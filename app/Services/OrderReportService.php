<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class OrderReportService
{
    public function getOrderReport(array $filters = []): array
    {
        $authUser = Auth::user();
        $role = $authUser->role; // adjust if using spatie or guards

        // Auto-set user_id if not passed and role is not admin
        if (!isset($filters['user_id']) && $role !== 'admin') {
            $filters['user_id'] = $authUser->id;
        }
        $userId  = $filters['user_id'] ?? null;
        $period  = $filters['period'] ?? 'daily';
        $perPage = $filters['per_page'] ?? 10;

        [$start, $end] = $this->getDateRange($period);

        // Shared query base
        $baseQuery = Order::with([
            'items.product',
            'items.creator',
            'items.serials' => function ($q) use ($userId) {
                if ($userId) {
                    $q->where('created_by', $userId);
                }
            },
        ])
            ->whereBetween('created_at', [$start, $end]);

        if ($userId) {
            $baseQuery->whereHas('items.serials', fn($q) => $q->where('created_by', $userId));
        }

        // Clone query for pagination
        $paginatedOrders = (clone $baseQuery)->paginate($perPage);

        // Clone query for totals (no pagination)
        $allOrders = (clone $baseQuery)->get();

        $totalOrders = $allOrders->count();
        $totalItems = 0;
        $totalPrice = 0;

        foreach ($allOrders as $order) {
            foreach ($order->items as $item) {
                foreach ($item->serials as $serial) {
                    $totalItems++;
                    $totalPrice += $serial->price;
                }
            }
        }

        // Transform only paginated results for view
        $transformed = $paginatedOrders->getCollection()->map(function ($order) {
            $serials = collect();
            $itemDetails = [];

            foreach ($order->items as $item) {
                foreach ($item->serials as $serial) {
                    $serials->push($serial);
                    $itemDetails[] = [
                        'order_item_id' => $item->id,
                        'product_id'    => $item->product_id,
                        'product_name'  => $item->product->product_name ?? '',
                        'serial_number' => $serial->serial_number,
                        'price'         => $serial->price,
                        'user_id'       => $item->creator?->id,
                        'user_name'     => $item->creator?->name,
                    ];
                }
            }

            return [
                'order_id'     => $order->id,
                'order_date'   => $order->created_at->toDateString(),
                'item_count'   => $serials->count(),
                'total_price'  => $serials->sum('price'),
                'items'        => $itemDetails,
            ];
        });

        $paginatedOrders->setCollection($transformed);

        return [
            'orders'       => $paginatedOrders,
            'total_orders' => $totalOrders,
            'total_items'  => $totalItems,
            'total_price'  => $totalPrice,
        ];
    }


    private function getDateRange(string $period): array
    {
        $now = now();

        return match ($period) {
            'daily' => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
            'weekly' => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()],
            'monthly' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            default => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
        };
    }
}
