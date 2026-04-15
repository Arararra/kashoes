<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade as PDF;
use App\Exports\ReportExport;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function exportToExcel()
    {
        $data = $this->getReportData();
        return Excel::download(new ReportExport($data), 'report_' . now()->format('Y_m') . '.xlsx');
    }

    public function exportToPdf()
    {
        $data = $this->getReportData();
        $pdf = PDF::loadView('exports.report', [
            'data' => $data,
        ]);

        return $pdf->download('report_' . now()->format('Y_m') . '.pdf');
    }

    private function getReportData()
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        return [
            'income' => DB::table('cash_flows')
                ->whereMonth('created_at', $currentMonth)
                ->whereYear('created_at', $currentYear)
                ->sum('income'),

            'expenses' => DB::table('cash_flows')
                ->whereMonth('created_at', $currentMonth)
                ->whereYear('created_at', $currentYear)
                ->sum('expenses'),

            'balance' => DB::table('cash_flows')
                ->whereMonth('created_at', $currentMonth)
                ->whereYear('created_at', $currentYear)
                ->sum(DB::raw('income - expenses')),
        ];
    }
}
