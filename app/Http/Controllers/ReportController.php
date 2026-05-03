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

        $month = str_pad($request->input('month', now()->month), 2, '0', STR_PAD_LEFT);
        $year  = $request->input('year', now()->year);

        return Excel::download(new ReportExport($data), "laporan_{$year}_{$month}.xlsx");
    }

    public function exportToPdf(Request $request)
    {
        $data = $this->getReportData($request);
        $data['date_generated'] = Carbon::now()->translatedFormat('d F Y H:i');

        $pdf = PDF::loadView('exports.report', $data);

        $month = str_pad($request->input('month', now()->month), 2, '0', STR_PAD_LEFT);
        $year  = $request->input('year', now()->year);

        return $pdf->download("laporan_{$year}_{$month}.pdf");
    }

    private function getReportData(Request $request): array
    {
        $month = (int) $request->input('month', now()->month);
        $year  = (int) $request->input('year', now()->year);

        $cashFlows = CashFlow::with('creator')
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->orderBy('date', 'desc')
            ->get();

        $income   = $cashFlows->where('type', 'income')->sum('amount');
        $expenses = $cashFlows->where('type', 'expense')->sum('amount');

        // ── Service sales ──────────────────────────────────────────────
        $orders = Order::whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->whereNotNull('services')
            ->get();

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

        $monthLabel = Carbon::createFromDate($year, $month, 1)->translatedFormat('F Y');

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
            'filter'     => 'monthly',
            'start_date' => Carbon::createFromDate($year, $month, 1)->format('Y-m-d'),
            'end_date'   => Carbon::createFromDate($year, $month, 1)->endOfMonth()->format('Y-m-d'),
            'month'      => $month,
            'year'       => $year,
            'monthLabel' => $monthLabel,
        ];
    }
}
