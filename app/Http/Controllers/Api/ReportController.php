<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReportController extends Controller
{
    /**
     * Order report grouped by order and user, with items and total price per item.
     */
    public function orderReport(Request $request)
    {
        $perPage = $request->input('per_page', 10); // Default to 10 per page

        $orders = Order::with([
            'items.product',
            'items.creator',
            'items.serials' => fn($q) => $q->where('created_by', auth()->id()),
        ])
            ->whereHas('items.serials', fn($q) => $q->where('created_by', auth()->id()))
            ->whereBetween('created_at', $this->getDateRange($request->input('period', 'daily')))
            ->paginate($perPage);

        // Transform paginated data using map() but preserve pagination info
        $transformed = $orders->getCollection()->map(function ($order) {
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

        // Replace the collection with the transformed one
        $orders->setCollection($transformed);

        return response()->json([
            'status' => true,
            'data' => $orders,
        ]);
    }




    /**
     * Detailed serial number report for a single order item.
     */
    public function serialReport($orderItemId)
    {
        $item = OrderItem::with(['serials', 'product'])->findOrFail($orderItemId);

        return response()->json([
            'status'        => true,
            'order_item_id' => $item->id,
            'product_id'    => $item->product_id,
            'product_name'  => $item->product->product_name ?? '',
            'serials'       => $item->serials->map(function ($serial) {
                return [
                    'serial_number' => $serial->serial_number,
                    'price'         => $serial->price,
                ];
            })->values(),
        ]);
    }

    /**
     * Get start and end dates for daily, weekly, monthly filters.
     */
    private function getDateRange(string $period): array
    {
        $now = now(); // use Laravel timezone-aware helper

        return match ($period) {
            'daily' => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
            'weekly' => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()],
            'monthly' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            default => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
        };
    }
}
