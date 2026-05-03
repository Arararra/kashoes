<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class RevenueChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Revenue 6 Bulan Terakhir';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $months = collect(range(5, 0))->map(function ($monthsAgo) {
            $date = Carbon::now()->subMonths($monthsAgo);

            $revenue = Order::where('status', 'completed')
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->sum('total_price');

            $orders = Order::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();

            return [
                'label'   => $date->translatedFormat('M Y'),
                'revenue' => (float) $revenue,
                'orders'  => $orders,
            ];
        });

        return [
            'datasets' => [
                [
                    'label'           => 'Revenue (Rp)',
                    'data'            => $months->pluck('revenue')->toArray(),
                    'borderColor'     => '#6366f1',
                    'backgroundColor' => 'rgba(99, 102, 241, 0.1)',
                    'fill'            => true,
                    'tension'         => 0.4,
                    'yAxisID'         => 'y',
                ],
                [
                    'label'           => 'Jumlah Orders',
                    'data'            => $months->pluck('orders')->toArray(),
                    'borderColor'     => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                    'fill'            => true,
                    'tension'         => 0.4,
                    'yAxisID'         => 'y1',
                ],
            ],
            'labels' => $months->pluck('label')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'type'     => 'linear',
                    'display'  => true,
                    'position' => 'left',
                    'ticks'    => [
                        'callback' => 'function(value) { return "Rp " + new Intl.NumberFormat("id-ID").format(value); }',
                    ],
                ],
                'y1' => [
                    'type'     => 'linear',
                    'display'  => true,
                    'position' => 'right',
                    'grid'     => ['drawOnChartArea' => false],
                    'ticks'    => [
                        'callback' => 'function(value) { return value + " orders"; }',
                    ],
                ],
            ],
            'plugins' => [
                'legend' => ['display' => true],
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) {
                            if (context.datasetIndex === 0) {
                                return " Rp " + new Intl.NumberFormat("id-ID").format(context.raw);
                            }
                            return " " + context.raw + " orders";
                        }',
                    ],
                ],
            ],
        ];
    }
}
