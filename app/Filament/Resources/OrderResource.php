<?php

namespace App\Filament\Resources;

use App\Models\Customer;
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

    protected static ?string $navigationLabel = 'Orders';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(3)->schema([
                    Card::make([
                        Fieldset::make('Customer Information')
                            ->schema([
                                Select::make('customer_id')
                                    ->label('Customer')
                                    ->relationship(
                                        name: 'customer',
                                        titleAttribute: 'name',
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->columnSpanFull()
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        $customer = Customer::find($state);
                                        if ($customer) {
                                            $set('customer_name', $customer->name);
                                            $set('customer_phone', $customer->phone);
                                            $set('customer_address', $customer->address);
                                            $set('is_member', $customer->is_member ? '1' : '0');
                                        } else {
                                            $set('customer_name', null);
                                            $set('customer_phone', null);
                                            $set('customer_address', null);
                                            $set('is_member', null);
                                        }
                                    }),

                                TextInput::make('customer_name')
                                    ->label('Nama Customer')
                                    ->required()
                                    ->disabled(fn (Get $get) => ! $get('customer_id'))
                                    ->dehydrated(),

                                TextInput::make('customer_phone')
                                    ->label('No Telepon')
                                    ->required()
                                    ->disabled(fn (Get $get) => ! $get('customer_id'))
                                    ->dehydrated(),

                                Placeholder::make('is_member')
                                    ->label('Status Member')
                                    ->content(function (Get $get) {
                                        $customerId = $get('customer_id');
                                        if (! $customerId) return '—';
                                        $customer = Customer::find($customerId);
                                        return $customer?->is_member
                                            ? '⭐ Member'
                                            : '— Non-Member';
                                    })
                                    ->extraAttributes(fn (Get $get) => [
                                        'class' => Customer::find($get('customer_id'))?->is_member
                                            ? 'text-success-600 font-bold'
                                            : 'text-gray-500',
                                    ]),

                                TextArea::make('customer_address')
                                    ->label('Alamat')
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
                                            ->required()
                                            ->live(onBlur: true),
                                    ])
                                    ->columns(2)
                                    ->columnSpanFull()
                                    ->addActionLabel('Add another service')
                                    ->deleteAction(
                                        fn ($action) => $action->after(
                                            fn (Get $get, Set $set) => self::recalculateTotal($get, $set)
                                        )
                                    ),

                                DatePicker::make('estimated_finished_date')
                                    ->label('Estimated Finished Date')
                                    ->required(),

                                DatePicker::make('finished_date')
                                    ->label('Finished Date')
                                    ->nullable(),

                                TextInput::make('discount')
                                    ->label('Diskon (Rp)')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->nullable()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Get $get, Set $set) => self::recalculateFinal($get, $set))
                                    ->prefix('Rp'),

                                TextInput::make('total_price')
                                    ->label('Total Harga')
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated()
                                    ->required(),

                                Select::make('status')
                                    ->options([
                                        'pending' => 'Pending',
                                        'in_progress' => 'Diproses',
                                        'ready_for_pickup' => 'Siap Diambil',
                                        'completed' => 'Selesai',
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
                TextColumn::make('customer.name')->label('Customer')->searchable(),
                TextColumn::make('estimated_finished_date')->label('Est. Finished Date')->date(),
                TextColumn::make('finished_date')->label('Finished Date')->date()->placeholder('—'),
                TextColumn::make('discount')->label('Diskon')->money('IDR')->placeholder('—'),
                TextColumn::make('total_price')->money('IDR'),
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
                TextColumn::make('creator.name')
                    ->label('Input By'),
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
        $quantity  = (int) ($get('quantity') ?: 1);

        $service = Service::find($serviceId);
        $price   = ($service?->price ?? 0) * $quantity;

        $set('price', $price);

        self::recalculateTotal($get, $set);
    }

    private static function recalculateTotal(Get $get, Set $set): void
    {
        $services = $get('../../services') ?? [];

        $subtotal = collect($services)->sum(
            fn ($item) => (int) ($item['price'] ?? 0)
        );

        $set('../../subtotal', $subtotal);

        self::recalculateFinal($get, $set);
    }

    private static function recalculateFinal(Get $get, Set $set): void
    {
        $services = $get('../../services') ?? $get('services') ?? [];
        $subtotal = collect($services)->sum(
            fn ($item) => (int) ($item['price'] ?? 0)
        );

        $discount = (float) ($get('../../discount') ?? $get('discount') ?? 0);
        $total    = max(0, $subtotal - $discount);

        $set('../../total_price', $total);
        $set('total_price', $total);
    }
}