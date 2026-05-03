<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;

class LatestOrdersWidget extends BaseWidget
{
    protected static ?string $heading = 'Orders Terbaru';

    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::query()
                    ->with('customer')
                    ->latest()
                    ->limit(8)
            )
            ->columns([
                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('services')
                    ->label('Services')
                    ->formatStateUsing(function ($state) {
                        if (empty($state)) return '—';
                        $names = collect($state)->map(function ($s) {
                            $service = \App\Models\Service::find($s['service_id'] ?? null);
                            return $service?->name ?? '—';
                        })->filter()->implode(', ');
                        return $names ?: '—';
                    })
                    ->limit(40),

                TextColumn::make('total_price')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'pending'          => 'Pending',
                        'in_progress'      => 'Diproses',
                        'ready_for_pickup' => 'Siap Diambil',
                        'completed'        => 'Selesai',
                        'cancelled'        => 'Dibatalkan',
                        default            => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'pending'          => 'primary',
                        'in_progress'      => 'warning',
                        'ready_for_pickup' => 'info',
                        'completed'        => 'success',
                        'cancelled'        => 'danger',
                        default            => 'gray',
                    }),

                TextColumn::make('estimated_finished_date')
                    ->label('Est. Selesai')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('edit')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil-square')
                    ->url(fn (Order $record) => route('filament.admin.resources.orders.edit', $record))
                    ->color('gray'),
            ])
            ->paginated(false);
    }
}
