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
        $now        = Carbon::now();
        $thisMonth  = $now->copy()->startOfMonth();
        $lastMonth  = $now->copy()->subMonth()->startOfMonth();
        $lastMonthEnd = $now->copy()->subMonth()->endOfMonth();

        // ── Revenue bulan ini vs bulan lalu ──────────────────────────────
        $revenueThis = Order::where('status', 'completed')
            ->whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->sum('total_price');

        $revenueLast = Order::where('status', 'completed')
            ->whereBetween('created_at', [$lastMonth, $lastMonthEnd])
            ->sum('total_price');

        $revenueDiff   = $revenueLast > 0
            ? round((($revenueThis - $revenueLast) / $revenueLast) * 100, 1)
            : ($revenueThis > 0 ? 100 : 0);
        $revenueColor  = $revenueDiff >= 0 ? 'success' : 'danger';
        $revenueIcon   = $revenueDiff >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down';

        // ── Orders bulan ini vs bulan lalu ────────────────────────────────
        $ordersThis = Order::whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->count();

        $ordersLast = Order::whereBetween('created_at', [$lastMonth, $lastMonthEnd])->count();

        $ordersDiff  = $ordersLast > 0
            ? round((($ordersThis - $ordersLast) / $ordersLast) * 100, 1)
            : ($ordersThis > 0 ? 100 : 0);
        $ordersColor = $ordersDiff >= 0 ? 'success' : 'danger';
        $ordersIcon  = $ordersDiff >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down';

        // ── Orders aktif (belum selesai) ──────────────────────────────────
        $activeOrders = Order::whereIn('status', ['pending', 'in_progress', 'ready_for_pickup'])->count();

        // ── Total customers ───────────────────────────────────────────────
        $totalCustomers  = Customer::count();
        $newThisMonth    = Customer::whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->count();

        // ── Saldo kas bulan ini ───────────────────────────────────────────
        $income  = CashFlow::where('type', 'income')
            ->whereMonth('date', $now->month)
            ->whereYear('date', $now->year)
            ->sum('amount');

        $expense = CashFlow::where('type', 'expense')
            ->whereMonth('date', $now->month)
            ->whereYear('date', $now->year)
            ->sum('amount');

        $balance      = $income - $expense;
        $balanceColor = $balance >= 0 ? 'success' : 'danger';

        return [
            Stat::make('Revenue Bulan Ini', 'Rp ' . number_format($revenueThis, 0, ',', '.'))
                ->description(($revenueDiff >= 0 ? '+' : '') . $revenueDiff . '% dari bulan lalu')
                ->descriptionIcon($revenueIcon)
                ->color($revenueColor)
                ->icon('heroicon-o-banknotes'),

            Stat::make('Orders Bulan Ini', $ordersThis)
                ->description(($ordersDiff >= 0 ? '+' : '') . $ordersDiff . '% dari bulan lalu')
                ->descriptionIcon($ordersIcon)
                ->color($ordersColor)
                ->icon('heroicon-o-clipboard-document-list'),

            Stat::make('Orders Aktif', $activeOrders)
                ->description('Pending, diproses, siap diambil')
                ->descriptionIcon('heroicon-m-clock')
                ->color($activeOrders > 0 ? 'warning' : 'success')
                ->icon('heroicon-o-arrow-path'),

            Stat::make('Total Customers', $totalCustomers)
                ->description($newThisMonth . ' customer baru bulan ini')
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('info')
                ->icon('heroicon-o-users'),

            Stat::make('Saldo Kas Bulan Ini', 'Rp ' . number_format($balance, 0, ',', '.'))
                ->description('Pemasukan: Rp ' . number_format($income, 0, ',', '.'))
                ->descriptionIcon($balance >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($balanceColor)
                ->icon('heroicon-o-wallet'),
        ];
    }
}
