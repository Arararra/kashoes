<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\CashFlow;

class LatestCashFlows extends BaseWidget
{
    public ?int $span = null;

    public function getColumnSpan(): int|string|array
    {
        return $this->span ?? 'full';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(CashFlow::latest()->take(5))
            ->columns([
                Tables\Columns\TextColumn::make('date')->label('Date'),
                Tables\Columns\TextColumn::make('type')->label('Type'),
                Tables\Columns\TextColumn::make('amount')->label('Amount')->money('USD'),
            ]);
    }
}
