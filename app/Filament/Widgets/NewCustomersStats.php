<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class NewCustomersStats extends ChartWidget
{
    protected static ?string $heading = 'Chart';
    
    public ?int $span = null;

    public function getColumnSpan(): int|string|array
    {
        return $this->span ?? 'full';
    }

    protected function getData(): array
    {
        $data = DB::table('customers')
            ->selectRaw('MONTH(created_at) as month, COUNT(*) as total')
            ->whereYear('created_at', now()->year)
            ->groupByRaw('MONTH(created_at)')
            ->pluck('total', 'month')
            ->toArray();

        return [
            'labels' => array_keys($data),
            'datasets' => [
                [
                    'label' => 'New Customers',
                    'data' => array_values($data),
                    'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
