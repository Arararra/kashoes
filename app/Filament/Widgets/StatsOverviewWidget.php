<?php

namespace App\Filament\Widgets;

use App\Models\CashFlow;
use App\Models\Customer;
use App\Models\Order;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $now = Carbon::now();
        $lastMonth = $now->copy()->subMonth()->startOfMonth();
        $lastMonthEnd = $now->copy()->subMonth()->endOfMonth();

        // Revenue masuk ke periode saat order selesai, bukan saat dibuat.
        $revenueThis = Order::where('status', 'completed')
            ->whereMonth('finished_date', $now->month)
            ->whereYear('finished_date', $now->year)
            ->sum('total_price');

        $revenueLast = Order::where('status', 'completed')
            ->whereBetween('finished_date', [$lastMonth, $lastMonthEnd])
            ->sum('total_price');

        $revenueDiff = $revenueLast > 0
            ? round((($revenueThis - $revenueLast) / $revenueLast) * 100, 1)
            : ($revenueThis > 0 ? 100 : 0);
        $revenueColor = $revenueDiff >= 0 ? 'success' : 'danger';
        $revenueIcon = $revenueDiff >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down';

        // ── Orders bulan ini ─────────────────────────────────────────
        $ordersThis = Order::whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->count();

        $ordersLast = Order::whereBetween('created_at', [$lastMonth, $lastMonthEnd])->count();

        $ordersDiff = $ordersLast > 0
            ? round((($ordersThis - $ordersLast) / $ordersLast) * 100, 1)
            : ($ordersThis > 0 ? 100 : 0);
        $ordersColor = $ordersDiff >= 0 ? 'success' : 'danger';
        $ordersIcon = $ordersDiff >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down';

        // ── Orders aktif ──────────────────────────────────────────────
        $activeOrders = Order::whereIn('status', ['pending', 'in_progress', 'ready_for_pickup'])->count();
        $pendingCount = Order::where('status', 'pending')->count();
        $progressCount = Order::where('status', 'in_progress')->count();

        // ── Total customers ───────────────────────────────────────────
        $totalCustomers = Customer::count();
        $memberCount = Customer::where('is_member', true)->count();
        $newThisMonth = Customer::whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->count();

        // ── Average Order Value (AOV) bulan ini ──────────────────────
        $completedThis = Order::where('status', 'completed')
            ->whereMonth('finished_date', $now->month)
            ->whereYear('finished_date', $now->year)
            ->count();
        $aov = $completedThis > 0 ? round($revenueThis / $completedThis, 0) : 0;

        // ── Saldo kas bulan ini ───────────────────────────────────────
        $income = CashFlow::where('type', 'income')
            ->whereMonth('date', $now->month)
            ->whereYear('date', $now->year)
            ->sum('amount');

        $expense = CashFlow::where('type', 'expense')
            ->whereMonth('date', $now->month)
            ->whereYear('date', $now->year)
            ->sum('amount');

        $balance = $income - $expense;
        $balanceColor = $balance >= 0 ? 'success' : 'danger';

        return [
            // 1. Revenue bulan ini
            Stat::make('Revenue Bulan Ini', 'Rp '.number_format($revenueThis, 0, ',', '.'))
                ->description(($revenueDiff >= 0 ? '+' : '').$revenueDiff.'% dari bulan lalu')
                ->descriptionIcon($revenueIcon)
                ->color($revenueColor)
                ->icon('heroicon-o-banknotes'),

            // 2. Orders bulan ini
            Stat::make('Orders Bulan Ini', $ordersThis)
                ->description(($ordersDiff >= 0 ? '+' : '').$ordersDiff.'% dari bulan lalu')
                ->descriptionIcon($ordersIcon)
                ->color($ordersColor)
                ->icon('heroicon-o-clipboard-document-list'),

            // 3. Orders aktif sekarang
            Stat::make('Orders Aktif', $activeOrders)
                ->description($pendingCount.' pending · '.$progressCount.' diproses')
                ->descriptionIcon('heroicon-m-clock')
                ->color($activeOrders > 0 ? 'warning' : 'success')
                ->icon('heroicon-o-arrow-path'),

            // 4. Customers & member
            Stat::make('Total Customers', $totalCustomers)
                ->description($memberCount.' member · +'.$newThisMonth.' bulan ini')
                ->descriptionIcon('heroicon-m-identification')
                ->color('info')
                ->icon('heroicon-o-users'),

            // 5. AOV — Average Order Value
            Stat::make('Rata-rata Order (AOV)', 'Rp '.number_format($aov, 0, ',', '.'))
                ->description('Per order selesai bulan ini')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('gray')
                ->icon('heroicon-o-chart-bar'),

            // 6. Saldo kas
            Stat::make('Saldo Kas Bulan Ini', 'Rp '.number_format($balance, 0, ',', '.'))
                ->description('Pemasukan: Rp '.number_format($income, 0, ',', '.'))
                ->descriptionIcon($balance >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($balanceColor)
                ->icon('heroicon-o-wallet'),
        ];
    }
}
