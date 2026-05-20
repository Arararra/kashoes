<?php

namespace App\Filament\Resources\CashFlowResource\Pages;

use App\Filament\Resources\CashFlowResource;
use App\Filament\Resources\CashFlowResource\Widgets\CashFlowStats;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

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

    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();

        $ids = request()->query('anomaly_ids');
        if (! empty($ids)) {
            $idsArray = array_filter(array_map('trim', explode(',', $ids)));
            if (! empty($idsArray)) {
                $query = $query->whereIn('id', $idsArray);
            }
        }

        return $query;
    }
}
