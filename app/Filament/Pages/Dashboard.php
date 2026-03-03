<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use App\Filament\Widgets\NewCustomersWidget;
use App\Filament\Widgets\OrdersWidget;
use App\Filament\Widgets\RevenueWidget;

class Dashboard extends BaseDashboard
{
    protected function getHeaderWidgets(): array
    {
        return [
            NewCustomersWidget::class,
            OrdersWidget::class,
            RevenueWidget::class,
        ];
    }

    public function getColumns(): int|array
    {
        return [
            'default' => 12,
            'md' => 12,
            'lg' => 12,
        ];
    }
}