<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;

class OrderStatusChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Distribusi Status Orders';

    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = [
        'default' => 'full',
        'md'      => 1,
    ];

    protected function getData(): array
    {
        $statuses = [
            'pending'          => 'Pending',
            'in_progress'      => 'Diproses',
            'ready_for_pickup' => 'Siap Diambil',
            'completed'        => 'Selesai',
            'cancelled'        => 'Dibatalkan',
        ];

        $colors = [
            'pending'          => '#6366f1',
            'in_progress'      => '#f59e0b',
            'ready_for_pickup' => '#06b6d4',
            'completed'        => '#22c55e',
            'cancelled'        => '#ef4444',
        ];

        $counts = Order::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $labels     = [];
        $data       = [];
        $bgColors   = [];

        foreach ($statuses as $key => $label) {
            $count = $counts[$key] ?? 0;
            if ($count > 0) {
                $labels[]   = $label;
                $data[]     = $count;
                $bgColors[] = $colors[$key];
            }
        }

        return [
            'datasets' => [
                [
                    'data'            => $data,
                    'backgroundColor' => $bgColors,
                    'hoverOffset'     => 6,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const pct = Math.round((context.raw / total) * 100);
                            return " " + context.raw + " orders (" + pct + "%)";
                        }',
                    ],
                ],
            ],
            'cutout' => '65%',
        ];
    }
}
