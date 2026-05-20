<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\CashFlow;
use Carbon\Carbon;

class IncomeExpenseChart extends ChartWidget
{
    protected static ?string $heading = 'Pemasukan vs Pengeluaran (Tahun Ini)';

    protected static ?int $sort = 7;

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $months = [];
        $income  = [];
        $expense = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months[] = $date->translatedFormat('M');

            $income[] = (float) CashFlow::where('type', 'income')
                ->whereYear('date', $date->year)
                ->whereMonth('date', $date->month)
                ->sum('amount');

            $expense[] = (float) CashFlow::where('type', 'expense')
                ->whereYear('date', $date->year)
                ->whereMonth('date', $date->month)
                ->sum('amount');
        }

        return [
            'labels'   => $months,
            'datasets' => [
                [
                    'label'           => 'Pemasukan',
                    'data'            => $income,
                    'backgroundColor' => 'rgba(74, 140, 111, 0.18)',   /* --ks-success */
                    'borderColor'     => '#4a8c6f',
                    'borderWidth'     => 2,
                    'borderRadius'    => 6,
                    'pointRadius'     => 3,
                ],
                [
                    'label'           => 'Pengeluaran',
                    'data'            => $expense,
                    'backgroundColor' => 'rgba(184, 76, 101, 0.18)',   /* --ks-primary */
                    'borderColor'     => '#b84c65',
                    'borderWidth'     => 2,
                    'borderRadius'    => 6,
                    'pointRadius'     => 3,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['position' => 'top', 'labels' => ['usePointStyle' => true]],
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(ctx){ return " Rp " + new Intl.NumberFormat("id-ID").format(ctx.raw); }',
                    ],
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'grid'  => ['color' => 'rgba(226,216,210,0.5)'],
                    'ticks' => [
                        'color'    => '#a08888',
                        'callback' => 'function(v){ return "Rp " + new Intl.NumberFormat("id-ID").format(v); }',
                    ],
                ],
                'x' => [
                    'grid'  => ['display' => false],
                    'ticks' => ['color' => '#6b5050'],
                ],
            ],
        ];
    }
}
