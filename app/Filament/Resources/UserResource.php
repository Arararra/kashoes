<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TextArea;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(3)
                    ->schema([
                        Card::make([
                            TextInput::make('name')
                                ->required(),

                            TextInput::make('phone'),

                            TextArea::make('address')
                                ->columnSpanFull(),

                            TextInput::make('email')
                                ->email()
                                ->required()
                                ->columnSpanFull(),

                            TextInput::make('password')
                                ->password()
                                ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null)
                                ->dehydrated(fn ($state) => filled($state))
                                ->required(fn (string $context): bool => $context === 'create')
                                ->columnSpanFull(),

                            Select::make('roles')
                                ->label('Role')
                                ->multiple()
                                ->relationship('roles', 'name')
                                ->preload()
                                ->searchable()
                                ->required()
                                ->columnSpanFull(),
                        ])->columns(2)->columnSpan(2),

                        Card::make([
                            Placeholder::make('created_by')
                                ->label('Created By')
                                ->content(fn (?User $record): string => $record?->creator?->name ?? 'N/A'),

                            Placeholder::make('created_at')
                                ->label('Created At')
                                ->content(fn (?User $record): string => $record?->created_at?->format('Y-m-d H:i:s') ?? 'N/A'),

                            Placeholder::make('updated_at')
                                ->label('Updated At')
                                ->content(fn (?User $record): string => $record?->updated_at?->format('Y-m-d H:i:s') ?? 'N/A'),
                        ])->columnSpan(1),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),

                TextColumn::make('email')
                    ->searchable(),

                TextColumn::make('phone')
                    ->searchable(),

                TextColumn::make('roles.name')
                    ->label('Role')
                    ->badge()
                    ->separator(','),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
