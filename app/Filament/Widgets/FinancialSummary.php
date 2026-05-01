<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class FinancialSummary extends BaseWidget
{
    public ?int $span = null;

    public function getColumnSpan(): int|string|array
    {
        return $this->span ?? 'full';
    }

    protected function getStats(): array
    {
        $income = DB::table('cash_flows')
            ->where('type', 'income')
            ->whereYear('date', now()->year)
            ->sum('amount');

        $expenses = DB::table('cash_flows')
            ->where('type', 'expense')
            ->whereYear('date', now()->year)
            ->sum('amount');

        $balance = $income - $expenses;

        return [
            Stat::make('Total Income', '$' . number_format($income, 2)),
            Stat::make('Total Expenses', '$' . number_format($expenses, 2)),
            Stat::make('Balance', '$' . number_format($balance, 2)),
        ];
    }
}
