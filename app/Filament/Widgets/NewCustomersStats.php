<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Customer;
use Carbon\Carbon;

class NewCustomersStats extends ChartWidget
{
    protected static ?string $heading = 'Pelanggan Baru (12 Bulan)';

    protected static ?int $sort = 8;

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $months = [];
        $counts = [];

        for ($i = 11; $i >= 0; $i--) {
            $date     = Carbon::now()->subMonths($i);
            $months[] = $date->translatedFormat('M');
            $counts[] = Customer::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
        }

        return [
            'labels'   => $months,
            'datasets' => [
                [
                    'label'           => 'Pelanggan Baru',
                    'data'            => $counts,
                    'backgroundColor' => 'rgba(184, 76, 101, 0.15)',   /* --ks-primary */
                    'borderColor'     => '#b84c65',
                    'borderWidth'     => 2,
                    'pointBackgroundColor' => '#b84c65',
                    'pointRadius'     => 4,
                    'fill'            => true,
                    'tension'         => 0.4,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['position' => 'top', 'labels' => ['usePointStyle' => true]],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'grid' => ['color' => 'rgba(226,216,210,0.5)'],
                    'ticks' => ['color' => '#a08888', 'stepSize' => 1],
                ],
                'x' => [
                    'grid' => ['display' => false],
                    'ticks' => ['color' => '#6b5050'],
                ],
            ],
        ];
    }
}
