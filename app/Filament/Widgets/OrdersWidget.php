<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use App\Models\Order;
use Carbon\Carbon;

class OrdersWidget extends Widget
{
    protected static string $view = 'filament.widgets.orders-widget';

    protected function getViewData(): array
    {
        $ordersCount = Order::where('created_at', '>=', Carbon::now()->subMonth())->count();

        return [
            'ordersCount' => $ordersCount,
        ];
    }
}