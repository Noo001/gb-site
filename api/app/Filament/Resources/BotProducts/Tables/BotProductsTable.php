<?php

namespace App\Filament\Resources\BotProducts\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BotProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('brand')
                    ->searchable(),
                TextColumn::make('category')
                    ->searchable(),
                TextColumn::make('price')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('availability')
                    ->badge(),
                TextColumn::make('quantity')
                    ->numeric(),
            ])
            ->defaultSort('name')
            ->filters([
                Filter::make('in_stock_only')
                    ->label('Не показывать товары с нулевым остатком')
                    ->checkbox()
                    ->query(fn (Builder $query): Builder => $query->where('quantity', '>', 0)),
                SelectFilter::make('availability')
                    ->options([
                        'in_stock' => 'in_stock',
                        'out_of_stock' => 'out_of_stock',
                    ]),
            ])
            ->recordActions([
                //
            ])
            ->toolbarActions([
                //
            ]);
    }
}
