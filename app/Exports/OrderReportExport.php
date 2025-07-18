<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class OrderReportExport implements FromView
{
    protected array $reportData;

    public function __construct(array $reportData)
    {
        $this->reportData = $reportData;
    }

    public function view(): View
    {
        return view('exports.order_report_excel', [
            'report'       => $this->reportData['orders'],
            'totalOrders'  => $this->reportData['total_orders'],
            'totalItems'   => $this->reportData['total_items'],
            'totalPrice'   => $this->reportData['total_price'],
        ]);
    }
}
