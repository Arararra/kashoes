<?php

namespace App\Filament\Resources;

use App\Models\User;
use App\Models\Order;
use App\Models\Service;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TextArea;
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
use App\Filament\Resources\OrderResource\Pages;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(3)->schema([
                    Card::make([
                        Fieldset::make('Customer Information')
                            ->schema([
                                Select::make('customer_id')
                                    ->relationship(
                                        name: 'customer',
                                        titleAttribute: 'name',
                                        modifyQueryUsing: fn ($query) => $query->role('customer')
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->columnSpanFull()
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        $customer = User::find($state);
                                        if ($customer) {
                                            $set('customer_name', $customer->name);
                                            $set('customer_phone', $customer->phone);
                                            $set('customer_address', $customer->address);
                                        } else {
                                            $set('customer_name', null);
                                            $set('customer_phone', null);
                                            $set('customer_address', null);
                                        }
                                    }),

                                TextInput::make('customer_name')
                                    ->required()
                                    ->disabled(fn (Get $get) => ! $get('customer_id'))
                                    ->dehydrated(),

                                TextInput::make('customer_phone')
                                    ->required()
                                    ->disabled(fn (Get $get) => ! $get('customer_id'))
                                    ->dehydrated(),

                                TextArea::make('customer_address')
                                    ->columnSpanFull()
                                    ->required()
                                    ->disabled(fn (Get $get) => ! $get('customer_id'))
                                    ->dehydrated(),
                            ])
                            ->columns(2),

                        Fieldset::make('Order Details')
                            ->schema([
                                Repeater::make('services')
                                    ->schema([
                                        Select::make('service_id')
                                            ->label('Service')
                                            ->options(Service::pluck('name', 'id'))
                                            ->required()
                                            ->live()
                                            ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculate($get, $set)),

                                        TextInput::make('price')
                                            ->numeric()
                                            ->disabled()
                                            ->dehydrated()
                                            ->required(),

                                        TextInput::make('quantity')
                                            ->numeric()
                                            ->minValue(1)
                                            ->default(1)
                                            ->columnSpanFull()
                                            ->required()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculate($get, $set)),

                                        TextArea::make('description')
                                            ->columnSpanFull()
                                            ->required(),
                                    ])
                                    ->columns(2)
                                    ->columnSpanFull()
                                    ->deleteAction(
                                        fn ($action) => $action->after(
                                            fn (Get $get, Set $set) => self::recalculateTotal($get, $set)
                                        )
                                    ),

                                DatePicker::make('estimated_date')->required(),
                                DatePicker::make('finished_date')->nullable(),

                                TextInput::make('total_price')
                                    ->numeric()
                                    ->required(),

                                Select::make('status')
                                    ->options([
                                        'pending' => 'Pending',
                                        'in_progress' => 'Diproses',
                                        'ready_for_pickup' => 'Siap Diambil',
                                        'completed' => 'Sudah Diambil',
                                        'cancelled' => 'Dibatalkan',
                                    ])
                                    ->default('pending')
                                    ->required(),
                            ]),
                    ])->columns(2)->columnSpan(2),

                    Card::make([
                        Placeholder::make('created_by')
                            ->content(fn (?Order $record) => $record?->creator?->name ?? 'N/A'),

                        Placeholder::make('created_at')
                            ->content(fn (?Order $record) => $record?->created_at?->format('Y-m-d H:i:s') ?? 'N/A'),

                        Placeholder::make('updated_at')
                            ->content(fn (?Order $record) => $record?->updated_at?->format('Y-m-d H:i:s') ?? 'N/A'),
                    ])->columnSpan(1),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer.name')->searchable(),
                TextColumn::make('estimated_date')->date(),
                TextColumn::make('finished_date')->date(),
                TextColumn::make('total_price')->money('USD'),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'pending' => 'Pending',
                        'in_progress' => 'Diproses',
                        'ready_for_pickup' => 'Siap Diambil',
                        'completed' => 'Sudah Diambil',
                        'cancelled' => 'Dibatalkan',
                        default => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'pending' => 'primary',
                        'in_progress' => 'warning',
                        'ready_for_pickup' => 'info',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                    }),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'pending' => 'Pending',
                    'in_progress' => 'Diproses',
                    'ready_for_pickup' => 'Siap Diambil',
                    'completed' => 'Sudah Diambil',
                    'cancelled' => 'Dibatalkan',
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

    private static function recalculate(Get $get, Set $set): void
    {
        $serviceId = $get('service_id');
        $quantity = (int) ($get('quantity') ?: 1);

        $service = Service::find($serviceId);
        $price = ($service?->price ?? 0) * $quantity;

        $set('price', $price);

        self::recalculateTotal($get, $set);
    }

    private static function recalculateTotal(Get $get, Set $set): void
    {
        $services = $get('../../services') ?? [];

        $total = collect($services)->sum(
            fn ($item) => (int) ($item['price'] ?? 0)
        );

        $set('../../total_price', $total);
    }
}