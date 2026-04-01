<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use App\Models\Order;
use Carbon\Carbon;

class RevenueWidget extends Widget
{
    protected static string $view = 'filament.widgets.revenue-widget';

    protected function getViewData(): array
    {
        $revenue = Order::where('created_at', '>=', Carbon::now()->subMonth())->sum('total_price');

        return [
            'revenue' => $revenue,
        ];
    }
}