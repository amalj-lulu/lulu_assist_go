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
        $period = $request->input('period', 'daily'); // default: daily

        $orders = Order::with([
                'user',
                'items.product',
                'items.serials'
            ])
            ->whereBetween('created_at', $this->getDateRange($period))
            ->get();

        $grouped = $orders->map(function ($order) {
            return [
                'order_id'   => $order->id,
                'order_date' => $order->created_at->toDateString(),
                'user_id'    => $order->user_id,
                'user_name'  => $order->user->name ?? '',
                'items'      => $order->items->map(function ($item) {
                    return [
                        'order_item_id' => $item->id,
                        'product_id'    => $item->product_id,
                        'product_name'  => $item->product->product_name ?? '',
                        'quantity'      => $item->quantity,
                        'total_price'   => $item->serials->sum('price'),
                    ];
                })->values(),
            ];
        });

        return response()->json([
            'status' => true,
            'data'   => $grouped->values(),
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
        $today = Carbon::today();

        return match ($period) {
            'daily' => [$today->startOfDay(), $today->endOfDay()],
            'weekly' => [$today->startOfWeek(), $today->endOfWeek()],
            'monthly' => [$today->startOfMonth(), $today->endOfMonth()],
            default => [$today->startOfDay(), $today->endOfDay()],
        };
    }
}
