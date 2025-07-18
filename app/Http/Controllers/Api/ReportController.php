<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\OrderReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReportController extends Controller
{
    public function __construct(protected OrderReportService $reportService) {}

    public function orderReport(Request $request)
    {
        $filters = $request->only(['period', 'per_page', 'page']);
        $report = $this->reportService->getOrderReport($filters);

        return response()->json([
            'status' => true,
            'data' => $report,
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
