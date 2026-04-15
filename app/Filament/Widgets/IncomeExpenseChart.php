<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class IncomeExpenseChart extends ChartWidget
{
    protected static ?string $heading = 'Chart';
    
    public ?int $span = null;

    public function getColumnSpan(): int|string|array
    {
        return $this->span ?? 'full';
    }

    protected function getData(): array
    {
        $income = DB::table('cash_flows')
            ->selectRaw('MONTH(date) as month, SUM(amount) as total')
            ->where('type', 'income')
            ->whereYear('date', now()->year)
            ->groupByRaw('MONTH(date)')
            ->pluck('total', 'month')
            ->toArray();

        $expenses = DB::table('cash_flows')
            ->selectRaw('MONTH(date) as month, SUM(amount) as total')
            ->where('type', 'expense')
            ->whereYear('date', now()->year)
            ->groupByRaw('MONTH(date)')
            ->pluck('total', 'month')
            ->toArray();

        return [
            'labels' => array_keys($income),
            'datasets' => [
                [
                    'label' => 'Income',
                    'data' => array_values($income),
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'borderColor' => 'rgba(75, 192, 192, 1)',
                ],
                [
                    'label' => 'Expenses',
                    'data' => array_values($expenses),
                    'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                    'borderColor' => 'rgba(255, 99, 132, 1)',
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
