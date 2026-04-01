<?php

namespace App\Filament\Resources\CashFlowResource\Widgets;

use App\Models\CashFlow;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CashFlowStats extends BaseWidget
{
    protected function getStats(): array
    {
        $startDate = now()->subMonth();

        $incomeCount = CashFlow::where('type', 'income')
            ->where('date', '>=', $startDate)
            ->count();

        $incomeTotal = CashFlow::where('type', 'income')
            ->where('date', '>=', $startDate)
            ->sum('amount');

        $expenseCount = CashFlow::where('type', 'expense')
            ->where('date', '>=', $startDate)
            ->count();

        $expenseTotal = CashFlow::where('type', 'expense')
            ->where('date', '>=', $startDate)
            ->sum('amount');

        return [
            Stat::make('Income (1 Month)', 'Rp ' . number_format($incomeTotal))
                ->description($incomeCount . ' entries')
                ->color('success'),

            Stat::make('Expense (1 Month)', 'Rp ' . number_format($expenseTotal))
                ->description($expenseCount . ' entries')
                ->color('danger'),
        ];
    }
}
