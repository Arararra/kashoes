<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CashFlowResource\Pages;
use App\Filament\Resources\CashFlowResource\RelationManagers;
use App\Models\CashFlow;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Placeholder;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CashFlowResource extends Resource
{
    protected static ?string $model = CashFlow::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Cash Flow';

    protected static ?string $navigationGroup = 'Finance';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(3)
                    ->schema([
                        Card::make([
                            Select::make('type')
                                ->options([
                                    'income' => 'Income',
                                    'expense' => 'Expense',
                                ])
                                ->required(),

                            DatePicker::make('date')
                                ->required(),

                            TextInput::make('title')
                                ->required()
                                ->maxLength(255),

                            TextInput::make('amount')
                                ->numeric()
                                ->required()
                                ->prefix('Rp'),

                            Textarea::make('description')
                                ->columnSpanFull(),
                        ])->columns(2)->columnSpan(2),

                        Card::make([
                            Placeholder::make('created_by')
                                ->label('Created By')
                                ->content(fn (?CashFlow $record): string => $record?->creator?->name ?? 'N/A'),

                            Placeholder::make('created_at')
                                ->label('Created At')
                                ->content(fn (?CashFlow $record): string => $record?->created_at?->format('Y-m-d H:i:s') ?? 'N/A'),

                            Placeholder::make('updated_at')
                                ->label('Updated At')
                                ->content(fn (?CashFlow $record): string => $record?->updated_at?->format('Y-m-d H:i:s') ?? 'N/A'),
                        ])->columnSpan(1),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')
                    ->date()
                    ->sortable(),

                BadgeColumn::make('type')
                    ->colors([
                        'success' => 'income',
                        'danger' => 'expense',
                    ]),

                TextColumn::make('title')
                    ->searchable(),

                TextColumn::make('amount')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('creator.name')
                    ->label('Input By'),
            ])
            ->filters([
                //
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
            'index' => Pages\ListCashFlows::route('/'),
            'create' => Pages\CreateCashFlow::route('/create'),
            'edit' => Pages\EditCashFlow::route('/{record}/edit'),
        ];
    }
}
