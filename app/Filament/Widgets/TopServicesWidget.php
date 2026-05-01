<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Service;
use Filament\Widgets\ChartWidget;

class TopServicesWidget extends ChartWidget
{
    protected static ?string $heading = 'Service Terpopuler';

    protected static ?int $sort = 5;

    protected int | string | array $columnSpan = [
        'default' => 'full',
        'md'      => 1,
    ];

    protected function getData(): array
    {
        // Ambil semua orders, flatten services JSON, hitung per service_id
        $orders = Order::whereNotNull('services')->get();

        $serviceCounts = [];

        foreach ($orders as $order) {
            foreach ((array) $order->services as $item) {
                $id = $item['service_id'] ?? null;
                if ($id) {
                    $qty = (int) ($item['quantity'] ?? 1);
                    $serviceCounts[$id] = ($serviceCounts[$id] ?? 0) + $qty;
                }
            }
        }

        arsort($serviceCounts);
        $top = array_slice($serviceCounts, 0, 6, true);

        $labels = [];
        $data   = [];
        $colors = [
            '#6366f1', '#f59e0b', '#22c55e',
            '#06b6d4', '#ef4444', '#8b5cf6',
        ];

        $i = 0;
        foreach ($top as $serviceId => $count) {
            $service  = Service::find($serviceId);
            $labels[] = $service?->name ?? 'Service #' . $serviceId;
            $data[]   = $count;
            $i++;
        }

        return [
            'datasets' => [
                [
                    'label'           => 'Jumlah Dipesan',
                    'data'            => $data,
                    'backgroundColor' => array_slice($colors, 0, count($data)),
                    'borderRadius'    => 6,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'plugins'   => [
                'legend' => ['display' => false],
            ],
            'scales' => [
                'x' => [
                    'ticks' => [
                        'stepSize' => 1,
                    ],
                    'beginAtZero' => true,
                ],
            ],
        ];
    }
}
