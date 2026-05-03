<?php
// app/Filament/Pages/Report.php

namespace App\Filament\Pages;

use App\Models\CashFlow;
use App\Models\Order;
use App\Models\Service;
use Carbon\Carbon;
use Filament\Pages\Page;
use Filament\Actions\Action;

class Report extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Report';
    protected static ?string $navigationGroup = 'Finance';
    protected static ?int $navigationSort = 1;
    protected static string $view = 'filament.pages.report';

    public string $filterMonth;
    public string $filterYear;

    public function mount(): void
    {
        $this->filterMonth = now()->format('m');
        $this->filterYear  = now()->format('Y');
    }

    protected function getViewData(): array
    {
        $month = (int) $this->filterMonth;
        $year  = (int) $this->filterYear;

        // ── Cash Flow records ──────────────────────────────────────────
        $cashFlows = CashFlow::whereMonth('date', $month)
            ->whereYear('date', $year)
            ->orderBy('date', 'desc')
            ->get();

        $totalIncome  = $cashFlows->where('type', 'income')->sum('amount');
        $totalExpense = $cashFlows->where('type', 'expense')->sum('amount');
        $saldoAkhir   = $totalIncome - $totalExpense;

        // ── Total penjualan per service (fix N+1) ──────────────────────
        $orders = Order::whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->whereNotNull('services')
            ->get();

        // Kumpulkan semua service_id dulu, lalu load sekaligus
        $allServiceIds = collect();
        foreach ($orders as $order) {
            foreach ((array) $order->services as $item) {
                if ($sid = $item['service_id'] ?? null) {
                    $allServiceIds->push($sid);
                }
            }
        }
        $serviceMap = Service::whereIn('id', $allServiceIds->unique())
            ->pluck('name', 'id');

        $serviceSales = [];
        foreach ($orders as $order) {
            foreach ((array) $order->services as $item) {
                $serviceId = $item['service_id'] ?? null;
                $qty       = (int) ($item['quantity'] ?? 1);
                $price     = (float) ($item['price'] ?? 0);

                if ($serviceId) {
                    if (!isset($serviceSales[$serviceId])) {
                        $serviceSales[$serviceId] = [
                            'name'     => $serviceMap[$serviceId] ?? 'Service #' . $serviceId,
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

        // ── Grafik 6 bulan ─────────────────────────────────────────────
        $chartData = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $inc  = CashFlow::where('type', 'income')
                ->whereYear('date', $date->year)
                ->whereMonth('date', $date->month)
                ->sum('amount');
            $exp  = CashFlow::where('type', 'expense')
                ->whereYear('date', $date->year)
                ->whereMonth('date', $date->month)
                ->sum('amount');
            $chartData[] = [
                'label'   => $date->translatedFormat('M Y'),
                'income'  => (float) $inc,
                'expense' => (float) $exp,
            ];
        }

        return [
            'cashFlows'    => $cashFlows,
            'totalIncome'  => $totalIncome,
            'totalExpense' => $totalExpense,
            'saldoAkhir'   => $saldoAkhir,
            'serviceSales' => $serviceSales,
            'chartData'    => $chartData,
            'month'        => $month,
            'year'         => $year,
            'monthLabel'   => Carbon::createFromDate($year, $month, 1)->translatedFormat('F Y'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportExcel')
                ->label('Export Excel')
                ->color('success')
                ->icon('heroicon-o-table-cells')
                ->url(fn () => route('reports.export.excel', [
                    'month' => $this->filterMonth,
                    'year'  => $this->filterYear,
                ]))
                ->openUrlInNewTab(),

            Action::make('exportPdf')
                ->label('Export PDF')
                ->color('danger')
                ->icon('heroicon-o-document-arrow-down')
                ->url(fn () => route('reports.export.pdf', [
                    'month' => $this->filterMonth,
                    'year'  => $this->filterYear,
                ]))
                ->openUrlInNewTab(),

            // ✅ Sekarang membawa filter type ke CashFlowResource
            Action::make('filterIncome')
                ->label('Lihat Detail Pemasukan')
                ->color('success')
                ->icon('heroicon-o-arrow-trending-up')
                ->url(fn () => route('filament.admin.resources.cash-flows.index')
                    . '?tableFilters[type][value]=income'),

            Action::make('filterExpense')
                ->label('Lihat Detail Pengeluaran')
                ->color('danger')
                ->icon('heroicon-o-arrow-trending-down')
                ->url(fn () => route('filament.admin.resources.cash-flows.index')
                    . '?tableFilters[type][value]=expense'),
        ];
    }
}