<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use App\Filament\Widgets\StatsOverviewWidget;
use App\Filament\Widgets\RevenueChartWidget;
use App\Filament\Widgets\LatestOrdersWidget;
use App\Filament\Widgets\OrderStatusChartWidget;
use App\Filament\Widgets\TopServicesWidget;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?string $title = 'Dashboard';

    public function getWidgets(): array
    {
        return [
            StatsOverviewWidget::class,
            RevenueChartWidget::class,
            LatestOrdersWidget::class,
            OrderStatusChartWidget::class,
            TopServicesWidget::class,
        ];
    }

    public function getColumns(): int|array
    {
        return [
            'default' => 1,
            'md'      => 2,
            'xl'      => 2,
        ];
    }
}