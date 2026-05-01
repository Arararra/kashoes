<?php

namespace App\Http\Controllers;

use App\Exports\ReportExport;
use App\Models\CashFlow;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function exportToExcel(Request $request)
    {
        $data = $this->getReportData($request);

        return Excel::download(new ReportExport($data), 'report_'.now()->format('Y_m').'.xlsx');
    }

    public function exportToPdf(Request $request)
    {
        $data = $this->getReportData($request);
        $data['date_generated'] = Carbon::now()->translatedFormat('d F Y H:i');

        $pdf = PDF::loadView('exports.report', $data);

        return $pdf->download('report_'.now()->format('Y_m').'.pdf');
    }

    private function getReportData(Request $request): array
    {
        $query = $this->getFilteredCashFlows($request);
        $cashFlows = $query->get();

        $income = $cashFlows->where('type', 'income')->sum('amount');
        $expenses = $cashFlows->where('type', 'expense')->sum('amount');

        return [
            'cashFlows' => $cashFlows,
            'income' => $income,
            'expenses' => $expenses,
            'netFlow' => $income - $expenses,
            'details' => $cashFlows->map(function (CashFlow $flow) {
                return [
                    'date' => $flow->date->format('Y-m-d'),
                    'type' => ucfirst($flow->type),
                    'title' => $flow->title,
                    'description' => $flow->description,
                    'created_by' => $flow->creator?->name ?? 'N/A',
                    'amount' => (float) $flow->amount,
                ];
            })->toArray(),
            'filter' => $request->input('filter', 'all'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'month' => Carbon::now()->month,
            'year' => Carbon::now()->year,
        ];
    }

    private function getFilteredCashFlows(Request $request)
    {
        $filter = $request->input('filter');
        $query = CashFlow::query()->with('creator')->orderBy('date', 'desc');

        if ($filter === 'daily') {
            $query->whereDate('date', Carbon::today());
        } elseif ($filter === 'weekly') {
            $query->whereBetween('date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
        } elseif ($filter === 'monthly') {
            $query->whereMonth('date', Carbon::now()->month)->whereYear('date', Carbon::now()->year);
        } elseif ($filter === 'yearly') {
            $query->whereYear('date', Carbon::now()->year);
        } elseif ($filter === 'custom' && $request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('date', [$request->input('start_date'), $request->input('end_date')]);
        }

        return $query;
    }
}
