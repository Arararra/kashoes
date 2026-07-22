<?php

namespace App\Http\Controllers;

use App\Exports\ReportExport;
use App\Models\CashFlow;
use App\Models\Order;
use App\Models\Service;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function exportToExcel(Request $request)
    {
        $data = $this->getReportData($request);
        return Excel::download(new ReportExport($data), "laporan_{$data['filenameLabel']}.xlsx");
    }

    public function exportToPdf(Request $request)
    {
        $data = $this->getReportData($request);
        $data['date_generated'] = Carbon::now()->translatedFormat('d F Y H:i');

        $pdf = PDF::loadView('exports.report', $data);

        return $pdf->download("laporan_{$data['filenameLabel']}.pdf");
    }

    private function getReportData(Request $request): array
    {
        $period = $request->input('period', 'this_month');
        $month = (int) $request->input('month', now()->month);
        $year  = (int) $request->input('year', now()->year);

        $cashFlowQuery = CashFlow::with('creator')->orderBy('date', 'desc');
        $orderQuery = Order::whereNotNull('services');

        if ($period === 'this_month') {
            $month = now()->month;
            $year = now()->year;
            $cashFlowQuery->whereMonth('date', $month)->whereYear('date', $year);
            $orderQuery->whereMonth('created_at', $month)->whereYear('created_at', $year);
            $monthLabel = now()->translatedFormat('F Y');
            $start = Carbon::now()->startOfMonth()->format('Y-m-d');
            $end = Carbon::now()->endOfMonth()->format('Y-m-d');
            $filenameLabel = "{$year}_" . str_pad($month, 2, '0', STR_PAD_LEFT);
        } elseif ($period === 'this_year') {
            $year = now()->year;
            $cashFlowQuery->whereYear('date', $year);
            $orderQuery->whereYear('created_at', $year);
            $monthLabel = "Tahun $year";
            $start = Carbon::now()->startOfYear()->format('Y-m-d');
            $end = Carbon::now()->endOfYear()->format('Y-m-d');
            $filenameLabel = "{$year}";
        } elseif ($period === 'all_time') {
            $monthLabel = "Semua Waktu";
            $start = CashFlow::min('date') ? Carbon::parse(CashFlow::min('date'))->format('Y-m-d') : '1970-01-01';
            $end = Carbon::now()->format('Y-m-d');
            $filenameLabel = "semua_waktu";
        } else {
            $cashFlowQuery->whereMonth('date', $month)->whereYear('date', $year);
            $orderQuery->whereMonth('created_at', $month)->whereYear('created_at', $year);
            $monthLabel = Carbon::createFromDate($year, $month, 1)->translatedFormat('F Y');
            $start = Carbon::createFromDate($year, $month, 1)->format('Y-m-d');
            $end = Carbon::createFromDate($year, $month, 1)->endOfMonth()->format('Y-m-d');
            $filenameLabel = "{$year}_" . str_pad($month, 2, '0', STR_PAD_LEFT);
        }

        $cashFlows = $cashFlowQuery->get();

        $income   = $cashFlows->where('type', 'income')->sum('amount');
        $expenses = $cashFlows->where('type', 'expense')->sum('amount');

        // ── Service sales ──────────────────────────────────────────────
        $orders = $orderQuery->get();

        $serviceSales = [];
        foreach ($orders as $order) {
            foreach ((array) $order->services as $item) {
                $serviceId = $item['service_id'] ?? null;
                $qty       = (int) ($item['quantity'] ?? 1);
                $price     = (float) ($item['price'] ?? 0);
                if ($serviceId) {
                    if (!isset($serviceSales[$serviceId])) {
                        $svc = Service::find($serviceId);
                        $serviceSales[$serviceId] = [
                            'name'     => $svc?->name ?? 'Service #' . $serviceId,
                            'quantity' => 0,
                            'revenue'  => 0,
                        ];
                    }
                    $serviceSales[$serviceId]['quantity'] += $qty;
                    $serviceSales[$serviceId]['revenue']  += $price;
                }
            }
        }
        usort($serviceSales, fn($a, $b) => $b['revenue'] <=> $a['revenue']);

        return [
            'cashFlows'    => $cashFlows,
            'income'       => $income,
            'expenses'     => $expenses,
            'netFlow'      => $income - $expenses,
            'serviceSales' => $serviceSales,
            'details'      => $cashFlows->map(fn (CashFlow $flow) => [
                'date'        => $flow->date->format('Y-m-d'),
                'type'        => $flow->type === 'income' ? 'Pemasukan' : 'Pengeluaran',
                'title'       => $flow->title,
                'description' => $flow->description,
                'created_by'  => $flow->creator?->name ?? 'N/A',
                'amount'      => (float) $flow->amount,
            ])->toArray(),
            'filter'     => $period,
            'start_date' => $start,
            'end_date'   => $end,
            'month'      => $month,
            'year'       => $year,
            'monthLabel' => $monthLabel,
            'filenameLabel' => $filenameLabel,
        ];
    }
}
