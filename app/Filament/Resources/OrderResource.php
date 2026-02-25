<?php

namespace App\Filament\Resources;

use App\Models\Order;
use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Fieldset::make('Customer Information')
                    ->schema([
                        Select::make('customer_id')
                            ->relationship('customer', 'name')
                            ->required(),
                    ]),
                Fieldset::make('Order Details')
                    ->schema([
                        Repeater::make('services')
                            ->schema([
                                TextInput::make('name')
                                    ->required(),
                                Select::make('service_id')
                                    ->relationship('service', 'name')
                                    ->required(),
                                TextInput::make('quantity')
                                    ->numeric()
                                    ->required(),
                            ])
                            ->required()
                            ->columnSpanFull(),
                        DatePicker::make('estimated_date')
                            ->required(),
                        DatePicker::make('finished_date')
                            ->nullable(),
                        TextInput::make('total_price')
                            ->numeric()
                            ->required(),
                        Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'in_progress' => 'In Progress',
                                'ready_for_pickup' => 'Ready for Pickup',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                            ])
                            ->required(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer.name')
                    ->label('Customer Name')
                    ->searchable(),
                TextColumn::make('services')
                    ->label('Services')
                    ->formatStateUsing(fn ($state) => collect($state)->pluck('name')->join(', ')),
                TextColumn::make('estimated_date')
                    ->label('Estimated Date')
                    ->date(),
                TextColumn::make('finished_date')
                    ->label('Finished Date')
                    ->date(),
                TextColumn::make('total_price')
                    ->label('Total Price')
                    ->money('USD'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'pending' => 'Pending',
                        'processing' => 'Diproses',
                        'ready_for_pickup' => 'Siap Diambil',
                        'completed' => 'Sudah Diambil',
                        'cancelled' => 'Dibatalkan',
                        default => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'pending' => 'primary',
                        'processing' => 'warning',
                        'ready_for_pickup' => 'info',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'in_progress' => 'In Progress',
                        'ready_for_pickup' => 'Ready for Pickup',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),
                TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
