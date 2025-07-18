<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OrderReportService;
use Illuminate\Http\Request;

class OrderReportController extends Controller
{
    public function __construct(protected OrderReportService $reportService) {}

    public function index(Request $request)
    {
        $filters = $request->only(['period', 'per_page', 'page', 'user_id']);
        $reportData = $this->reportService->getOrderReport($filters);
        $users = User::orderBy('name')->get(); // load for dropdown


        return view('reports.orders', [
            'report'        => $reportData['orders'],
            'totalOrders'   => $reportData['total_orders'],
            'totalItems'    => $reportData['total_items'],
            'totalPrice'    => $reportData['total_price'],
            'users'         => $users,
        ]);
    }
}
