<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceResource\Pages;
use App\Filament\Resources\ServiceResource\RelationManagers;
use App\Models\Service;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Placeholder;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(3)
                    ->schema([
                        Card::make([
                            TextInput::make('name')
                                ->required()
                                ->maxLength(255)
                                ->columnSpanFull(),

                            TextInput::make('price')
                                ->required()
                                ->numeric()
                                ->columnSpanFull(),
                        ])->columns(2)->columnSpan(2),

                        Card::make([
                            Placeholder::make('created_by')
                                ->label('Created By')
                                ->content(fn (?Service $record): string => $record?->creator?->name ?? 'N/A'),

                            Placeholder::make('created_at')
                                ->label('Created At')
                                ->content(fn (?Service $record): string => $record?->created_at?->format('Y-m-d H:i:s') ?? 'N/A'),

                            Placeholder::make('updated_at')
                                ->label('Updated At')
                                ->content(fn (?Service $record): string => $record?->updated_at?->format('Y-m-d H:i:s') ?? 'N/A'),
                        ])->columnSpan(1),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('price')
                    ->label('Price')
                    ->sortable()
                    ->searchable(),
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
            'index' => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit' => Pages\EditService::route('/{record}/edit'),
        ];
    }
}
