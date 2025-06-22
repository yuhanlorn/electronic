<?php

namespace App\Filament\Admin\Resources;

// use App\Filament\Admin\Resources\CartResource\RelationManagers;
use App\Models\Cart;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CartResource extends Resource
{
    protected static ?string $model = Cart::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?int $navigationSort = 30;

    protected static ?string $navigationLabel = 'Shopping Carts';

    public static function getNavigationGroup(): ?string
    {
        return 'Sales & Orders';
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('is_active', true)->count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('account_id')
                    ->numeric(),
                Forms\Components\TextInput::make('product_id')
                    ->numeric(),
                Forms\Components\TextInput::make('session_id')
                    ->maxLength(255),
                Forms\Components\TextInput::make('item')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->prefix('$'),
                Forms\Components\TextInput::make('discount')
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('vat')
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('qty')
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('total')
                    ->numeric()
                    ->default(0),
                Forms\Components\Textarea::make('note')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('options'),
                Forms\Components\Toggle::make('is_active'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('account_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('product_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('session_id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('item')
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->money()
                    ->sortable(),
                Tables\Columns\TextColumn::make('discount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('vat')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('qty')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => \App\Filament\Admin\Resources\CartResource\Pages\ListCarts::route('/'),
            'create' => \App\Filament\Admin\Resources\CartResource\Pages\CreateCart::route('/create'),
            'edit' => \App\Filament\Admin\Resources\CartResource\Pages\EditCart::route('/{record}/edit'),
        ];
    }
}
