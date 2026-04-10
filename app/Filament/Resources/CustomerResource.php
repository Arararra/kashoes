<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Models\Customer;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Customers';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Customer')
                    ->description('Isi data customer dengan lengkap.')
                    ->icon('heroicon-o-user')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Customer')
                            ->placeholder('Masukkan nama lengkap customer')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('phone')
                            ->label('No Telepon')
                            ->placeholder('Contoh: 08123456789')
                            ->tel()
                            ->maxLength(20),

                        Textarea::make('address')
                            ->label('Alamat')
                            ->placeholder('Masukkan alamat lengkap customer')
                            ->rows(3)
                            ->columnSpanFull(),

                        Toggle::make('is_member')
                            ->label('Member')
                            ->helperText('Aktifkan jika customer adalah member terdaftar.')
                            ->default(false)
                            ->onColor('success')
                            ->offColor('gray'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Customer')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('phone')
                    ->label('No Telepon')
                    ->searchable()
                    ->placeholder('—'),

                TextColumn::make('address')
                    ->label('Alamat')
                    ->limit(40)
                    ->placeholder('—')
                    ->tooltip(fn ($record) => $record->address),

                IconColumn::make('is_member')
                    ->label('Member')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-mark')
                    ->trueColor('success')
                    ->falseColor('gray'),

                TextColumn::make('created_at')
                    ->label('Terdaftar')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('is_member')
                    ->label('Status Member')
                    ->options([
                        '1' => 'Member',
                        '0' => 'Non-Member',
                    ]),
                TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit'   => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
