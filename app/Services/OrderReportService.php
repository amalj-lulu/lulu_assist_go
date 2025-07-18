<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class OrderReportService
{
    public function getOrderReport(array $filters = [], bool $paginate = true): array
    {
        $authUser = Auth::user();
        $role = $authUser->role;

        if (!isset($filters['user_id']) && $role !== 'admin') {
            $filters['user_id'] = $authUser->id;
        }

        $userId   = $filters['user_id'] ?? null;
        $plantId  = $filters['plant_id'] ?? null;
        $period   = $filters['period'] ?? 'daily';
        $perPage  = $filters['per_page'] ?? 10;
        $fromDate = $filters['from_date'] ?? null;
        $toDate   = $filters['to_date'] ?? null;

        [$start, $end] = $this->getDateRange($period, $fromDate, $toDate);

        $baseQuery = Order::with([
            'items.product',
            'items.creator',
            'items.serials' => function ($q) use ($userId, $plantId) {
                if ($userId) {
                    $q->where('created_by', $userId);
                }
                if ($plantId) {
                    $q->whereHas('creator.plants', fn($query) => $query->where('plants.id', $plantId));
                }
            },
        ])->whereBetween('created_at', [$start, $end]);

        if ($userId) {
            $baseQuery->whereHas('items.serials', fn($q) => $q->where('created_by', $userId));
        }

        if ($plantId) {
            $baseQuery->whereHas('items.serials.creator.plants', fn($q) => $q->where('plants.id', $plantId));
        }

        $orders = $paginate ? $baseQuery->paginate($perPage) : $baseQuery->get();

        $totalOrders = 0;
        $totalItems = 0;
        $totalPrice = 0;

        $transformed = collect($orders instanceof \Illuminate\Pagination\AbstractPaginator ? $orders->items() : $orders)
            ->map(function ($order) use (&$totalOrders, &$totalItems, &$totalPrice) {
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

                $totalOrders++;
                $totalItems += $serials->count();
                $totalPrice += $serials->sum('price');

                return [
                    'order_id'     => $order->id,
                    'order_date'   => $order->created_at->toDateString(),
                    'item_count'   => $serials->count(),
                    'total_price'  => $serials->sum('price'),
                    'items'        => $itemDetails,
                ];
            });

        // Return based on pagination
        if ($paginate) {
            $orders->setCollection($transformed);
            return [
                'orders'       => $orders,
                'total_orders' => $orders->total(),
                'total_items'  => $totalItems,
                'total_price'  => $totalPrice,
            ];
        }

        return [
            'orders'       => $transformed,
            'total_orders' => $totalOrders,
            'total_items'  => $totalItems,
            'total_price'  => $totalPrice,
        ];
    }


    public function getFullOrderReport(array $filters = []): array
    {
        $authUser = Auth::user();
        $role = $authUser->role;

        if (!isset($filters['user_id']) && $role !== 'admin') {
            $filters['user_id'] = $authUser->id;
        }

        $userId  = $filters['user_id'] ?? null;
        $plantId = $filters['plant_id'] ?? null;
        $period  = $filters['period'] ?? 'daily';
        $fromDate = $filters['from_date'] ?? null;
        $toDate = $filters['to_date'] ?? null;

        [$start, $end] = $this->getDateRange($period, $fromDate, $toDate);

        $query = Order::with([
            'items.product',
            'items.creator',
            'items.serials' => function ($q) use ($userId, $plantId) {
                if ($userId) {
                    $q->where('created_by', $userId);
                }
                if ($plantId) {
                    $q->whereHas('creator.plants', fn($query) => $query->where('plants.id', $plantId));
                }
            },
        ])->whereBetween('created_at', [$start, $end]);

        if ($userId) {
            $query->whereHas('items.serials', fn($q) => $q->where('created_by', $userId));
        }

        if ($plantId) {
            $query->whereHas('items.serials.creator.plants', fn($q) => $q->where('plants.id', $plantId));
        }

        $orders = $query->get(); // No pagination

        $totalOrders = 0;
        $totalItems = 0;
        $totalPrice = 0;

        $transformed = $orders->map(function ($order) use (&$totalOrders, &$totalItems, &$totalPrice) {
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

            $totalOrders++;
            $totalItems += $serials->count();
            $totalPrice += $serials->sum('price');

            return [
                'order_id'     => $order->id,
                'order_date'   => $order->created_at->toDateString(),
                'item_count'   => $serials->count(),
                'total_price'  => $serials->sum('price'),
                'items'        => $itemDetails,
            ];
        });

        return [
            'orders'       => $transformed,
            'total_orders' => $totalOrders,
            'total_items'  => $totalItems,
            'total_price'  => $totalPrice,
        ];
    }

    private function getDateRange(string $period, ?string $from = null, ?string $to = null): array
    {
        $now = now();

        return match ($period) {
            'daily' => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
            'weekly' => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()],
            'monthly' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            'custom' => [
                $from ? now()->parse($from)->startOfDay() : $now->copy()->startOfDay(),
                $to ? now()->parse($to)->endOfDay() : $now->copy()->endOfDay(),
            ],
            default => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
        };
    }
}
