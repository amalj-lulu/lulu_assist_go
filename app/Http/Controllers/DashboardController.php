<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Customer;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Plant;
use App\Models\User;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $plantId = $request->query('plant_id');
        $plants = Plant::all();

        $userCount = User::when($plantId, function ($q) use ($plantId) {
            return $q->whereHas('plants', function ($q) use ($plantId) {
                $q->where('plants.id', $plantId); // avoid ambiguity
            });
        })->count();

        $customerCount = Customer::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();

        $conversionRateData = $this->getConversionRateChartData($plantId);
        $conversionRateChartJson = json_encode($conversionRateData);
        $orderCount = Order::when($plantId, function ($q) use ($plantId) {
            $q->whereHas('items.creator.plants', function ($query) use ($plantId) {
                $query->where('plant_id', $plantId);
            });
        })->distinct()->count('orders.id');

        $userWiseSales = $this->getUserWiseSalesChartData($plantId);
        $userWiseSalesJson = json_encode($userWiseSales);
        $orderTrendJson = json_encode($this->getOrderTrendChartData($plantId));
        $materialCategoryOrderJson = json_encode($this->getMaterialCategoryWiseOrderData($plantId));

        return view('dashboard.index', compact(
            'plants',
            'userCount',
            'customerCount',
            'orderCount',
            'userWiseSalesJson',
            'orderTrendJson',
            'conversionRateChartJson',
            'materialCategoryOrderJson'
        ));
    }
    public function getMaterialCategoryWiseOrderData($plantId): array
    {
        $orderItems = OrderItem::with(['product', 'creator'])
            ->when($plantId, function ($query) use ($plantId) {
                $query->whereHas('creator.plants', function ($q) use ($plantId) {
                    $q->where('plants.id', $plantId);
                });
            })
            ->get();
        $grouped = $orderItems->groupBy(fn($item) => $item->product->material_category);

        $labels = [];
        $data = [];
        $colors = ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#6f42c1', '#20c997', '#fd7e14', '#6610f2'];

        foreach ($grouped as $category => $items) {
            $totalQty = $items->sum('quantity');

            if ($totalQty > 0) {
                $labels[] = $category ?? 'Uncategorized';
                $data[] = $totalQty;
            }
        }

        return [
            'labels' => $labels,
            'datasets' => [[
                'label' => 'Ordered Items by Material Category',
                'data' => $data,
                'backgroundColor' => array_slice($colors, 0, count($labels)),
                'borderColor' => '#fff',
                'borderWidth' => 2
            ]]
        ];
    }


    public function getConversionRateChartData($plantId = null): array
    {
        $users = User::when($plantId, function ($q) use ($plantId) {
            $q->whereHas('plants', function ($q) use ($plantId) {
                $q->where('plants.id', $plantId);
            });
        })->get();

        $labels = [];
        $data = [];
        $colors = ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#6f42c1', '#20c997', '#fd7e14', '#6610f2'];

        foreach ($users as $i => $user) {
            $cartItems = CartItem::where('created_by', $user->id)->sum('quantity');
            $orderedItems = OrderItem::where('created_by', $user->id)->sum('quantity');

            $rate = $cartItems > 0 ? round(($orderedItems / $cartItems) * 100, 2) : 0;

            $labels[] = $user->name;
            $data[] = $rate;
        }

        return [
            'labels' => $labels,
            'datasets' => [[
                'label' => 'Conversion Rate (%)',
                'data' => $data,
                'backgroundColor' => array_slice($colors, 0, count($labels)),
                'borderColor' => '#fff',
                'borderWidth' => 2
            ]]
        ];
    }
    public function getUserWiseSalesChartData($plantId): array
    {
        $plantUsers = User::where('role', 'user')
            ->when($plantId, function ($query) use ($plantId) {
                $query->whereHas('plants', function ($q) use ($plantId) {
                    $q->where('plants.id', $plantId);
                });
            })->get();


        $labels = [];
        $data = [];
        $colors = [
            '#0d6efd',
            '#198754',
            '#ffc107',
            '#dc3545',
            '#6f42c1',
            '#20c997',
            '#fd7e14',
            '#6610f2'
        ];

        foreach ($plantUsers as $index => $user) {
            $totalSales = OrderItem::where('created_by', $user->id)->sum('total_price');
            $labels[] = $user->name;
            $data[] = round($totalSales, 2);
        }

        return [
            'labels' => $labels,
            'datasets' => [[
                'label' => 'Total Sales (₹)',
                'data' => $data,
                'backgroundColor' => array_slice($colors, 0, count($labels)),
                'borderRadius' => 5,
            ]]
        ];
    }
    public function getOrderTrendChartData($plantId = null): array
    {
        $labels = [];
        $data = [];

        for ($i = 5; $i >= 0; $i--) {
            $monthStart = Carbon::now()->subMonths($i)->startOfMonth();
            $monthEnd = Carbon::now()->subMonths($i)->endOfMonth();
            $monthLabel = $monthStart->format('F');

            $query = Order::whereBetween('created_at', [$monthStart, $monthEnd]);

            if ($plantId) {
                // Look into order_items.created_by → user.plant_id
                $query->whereHas('items.creator.plants', function ($q) use ($plantId) {
                    $q->where('plant_id', $plantId);
                });
            }

            $labels[] = $monthLabel;
            $data[] = $query->distinct()->count('orders.id'); // distinct in case multiple items in same order
        }

        return [
            'labels' => $labels,
            'datasets' => [[
                'label' => 'Orders',
                'data' => $data,
                'fill' => true,
                'backgroundColor' => 'rgba(13, 110, 253, 0.1)',
                'borderColor' => '#0d6efd',
                'tension' => 0.4,
                'pointBackgroundColor' => '#0d6efd',
                'pointBorderColor' => '#fff',
                'pointHoverBackgroundColor' => '#fff',
                'pointHoverBorderColor' => '#0d6efd',
            ]]
        ];
    }
}
