<?php

namespace App\Filament\Resources;

use App\Models\Order;
use App\Models\Service;
use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Placeholder;
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
                Grid::make(3)
                    ->schema([
                        Card::make([
                            Fieldset::make('Customer Information')
                                ->schema([
                                    Select::make('customer_id')
                                        ->required(),
                                ]),

                            Fieldset::make('Order Details')
                                ->schema([
                                    Repeater::make('services')
                                        ->schema([
                                            TextInput::make('name')
                                                ->label('Name')
                                                ->required(),
                                            Select::make('service_id')
                                                ->label('Service')
                                                ->options(Service::pluck('name', 'id'))
                                                ->required()
                                                ->reactive()
                                                ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                    $service = Service::find($state);
                                                    $quantity = (int) $get('quantity');
                                                    $price = ($service?->price ?? 0) * ($quantity ?: 1);

                                                    $set('price', $price);
                                                }),
                                            TextInput::make('quantity')
                                                ->label('Quantity')
                                                ->required()
                                                ->numeric()
                                                ->minValue(1)
                                                ->default(1)
                                                ->reactive()
                                                ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                    $serviceId = $get('service_id');
                                                    $service = Service::find($serviceId);

                                                    $price = ($service?->price ?? 0) * ((int) $state ?: 1);

                                                    $set('price', $price);
                                                }),
                                            TextInput::make('price')
                                                ->label('Total Price')
                                                ->numeric()
                                                ->disabled()
                                                ->dehydrated()
                                                ->required(),
                                        ])
                                        ->columns(2)
                                        ->columnSpanFull(),

                                    DatePicker::make('estimated_date')
                                        ->required(),

                                    DatePicker::make('finished_date')
                                        ->nullable(),

                                    TextInput::make('total_price')
                                        ->label('Total Price')
                                        ->required()
                                        ->numeric(),

                                    Select::make('status')
                                        ->options([
                                            'pending' => 'Pending',
                                            'processing' => 'Diproses',
                                            'ready_for_pickup' => 'Siap Diambil',
                                            'completed' => 'Sudah Diambil',
                                            'cancelled' => 'Dibatalkan',
                                        ])
                                        ->required(),
                                ]),
                        ])->columns(2)->columnSpan(2),

                        Card::make([
                            Placeholder::make('created_by')
                                ->label('Created By')
                                ->content(fn (?Order $record): string => $record?->creator?->name ?? 'N/A'),

                            Placeholder::make('created_at')
                                ->label('Created At')
                                ->content(fn (?Order $record): string => $record?->created_at?->format('Y-m-d H:i:s') ?? 'N/A'),

                            Placeholder::make('updated_at')
                                ->label('Updated At')
                                ->content(fn (?Order $record): string => $record?->updated_at?->format('Y-m-d H:i:s') ?? 'N/A'),
                        ])->columnSpan(1),
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
