<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OrderReportService;
use Illuminate\Http\Request;
use App\Exports\OrderReportExport;
use App\Models\Plant;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class OrderReportController extends Controller
{
    public function __construct(protected OrderReportService $reportService) {}

    public function index(Request $request)
    {
        $filters = $request->only(['period', 'per_page', 'page', 'user_id','plant_id','from_date','to_date']);
        $reportData = $this->reportService->getOrderReport($filters);
        $users = User::orderBy('name')->get(); // load for dropdown
        $plants = Plant::orderBy('code')->get();


        return view('reports.orders', [
            'report'        => $reportData['orders'],
            'totalOrders'   => $reportData['total_orders'],
            'totalItems'    => $reportData['total_items'],
            'totalPrice'    => $reportData['total_price'],
            'users'         => $users,
            'plants'        => $plants
        ]);
    }
    public function exportExcel(Request $request)
    {
        $filters = $request->only(['user_id', 'period','plant_id','from_date','to_date']);

        // Call full (non-paginated) report method
        $reportData = app(OrderReportService::class)->getOrderReport($filters, false);

        $fileName = 'order_report_' . now()->format('Y-m-d_H-i-s') . '.xlsx';

        return Excel::download(new OrderReportExport($reportData), $fileName);
    }

    public function exportPdf(Request $request)
    {
        $filters = $request->all();
        $report = $this->reportService->getOrderReport($filters, false); // non-paginated
        $pdf = Pdf::loadView('exports.order_report_pdf', compact('report'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('order_report.pdf');
    }
}
