<?php

namespace App\Filament\Resources\CashFlowResource\Pages;

use App\Filament\Resources\CashFlowResource;
use App\Filament\Resources\CashFlowResource\Widgets\CashFlowStats;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCashFlows extends ListRecords
{
    protected static string $resource = CashFlowResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    
    protected function getHeaderWidgets(): array
    {
        return [
            CashFlowStats::class,
        ];
    }
}
