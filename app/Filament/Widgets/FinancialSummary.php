<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\CashFlow;

class FinancialSummary extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $income   = (float) CashFlow::where('type', 'income')->whereYear('date', now()->year)->sum('amount');
        $expense  = (float) CashFlow::where('type', 'expense')->whereYear('date', now()->year)->sum('amount');
        $balance  = $income - $expense;

        return [
            Stat::make('Total Pemasukan', 'Rp ' . number_format($income, 0, ',', '.'))
                ->description('Seluruh pemasukan tahun ' . now()->year)
                ->color('success'),

            Stat::make('Total Pengeluaran', 'Rp ' . number_format($expense, 0, ',', '.'))
                ->description('Seluruh pengeluaran tahun ' . now()->year)
                ->color('danger'),

            Stat::make('Saldo Bersih', 'Rp ' . number_format(abs($balance), 0, ',', '.'))
                ->description($balance >= 0 ? 'Surplus' : 'Defisit')
                ->color($balance >= 0 ? 'success' : 'danger'),
        ];
    }
}
