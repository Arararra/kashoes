<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use App\Models\User;
use Carbon\Carbon;

class NewCustomersWidget extends Widget
{
    public static function canView(): bool { return false; }

    protected static string $view = 'filament.widgets.new-customers-widget';

    protected function getViewData(): array
    {
        $newCustomersCount = User::where('created_at', '>=', Carbon::now()->subMonth())
            ->whereHas('roles', fn($query) => $query->where('name', 'customer'))
            ->count();

        return [
            'newCustomersCount' => $newCustomersCount,
        ];
    }
}