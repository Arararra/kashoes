<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use App\Models\Order;
use Carbon\Carbon;

class RevenueWidget extends Widget
{
    protected static bool $isLazy = false;

    public static function canView(): bool { return false; }

    protected static string $view = 'filament.widgets.revenue-widget';

    protected function getViewData(): array
    {
        $revenue = Order::where('created_at', '>=', Carbon::now()->subMonth())->sum('total_price');

        return [
            'revenue' => $revenue,
        ];
    }
}