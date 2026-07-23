<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrderItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Товары в заказе';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product_name')
                    ->label('Товар')
                    ->searchable(),

                TextColumn::make('offer_name')
                    ->label('Предложение')
                    ->placeholder('—'),

                TextColumn::make('quantity')
                    ->label('Количество'),

                TextColumn::make('price')
                    ->label('Цена')
                    ->money('RUB')
                    ->placeholder('—'),

                TextColumn::make('total')
                    ->label('Сумма')
                    ->money('RUB')
                    ->placeholder('—'),
            ]);
    }
}
