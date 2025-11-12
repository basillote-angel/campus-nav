<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use PDF;
use App\Models\Item;

class AdminController extends Controller
{
    public function downloadMonthlyReport()
    {
        $month = now()->format('F');
        $year = now()->format('Y');
        
        // Example: get items created this month
        $lostItems = Item::whereMonth('created_at', now()->month)
                        ->whereYear('created_at', now()->year)
                        ->get();
        
        $pdf = \PDF::loadView('admin.reports.monthly', compact('lostItems', 'month', 'year'));
        return $pdf->download("LostFound_Report_{$month}_{$year}.pdf");
    }
}
