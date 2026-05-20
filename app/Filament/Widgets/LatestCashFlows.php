<?php

namespace App\Filament\Widgets;

use App\Models\CashFlow;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestCashFlows extends BaseWidget
{
    protected static ?string $heading = 'Arus Kas Terbaru';

    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(CashFlow::latest()->limit(8))
            ->columns([
                TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('title')
                    ->label('Keterangan')
                    ->limit(40),

                TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state === 'income' ? 'Pemasukan' : 'Pengeluaran')
                    ->color(fn ($state) => $state === 'income' ? 'success' : 'danger'),

                TextColumn::make('amount')
                    ->label('Jumlah')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->color(fn ($record) => $record->type === 'income' ? 'success' : 'danger')
                    ->alignRight()
                    ->weight('bold'),
            ])
            ->paginated(false);
    }
}
