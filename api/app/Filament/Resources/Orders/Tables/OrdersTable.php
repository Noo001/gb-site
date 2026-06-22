<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Models\Order;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('№')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Статус')
                    ->formatStateUsing(fn (string $state): string => Order::$statuses[$state] ?? $state)
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Order::STATUS_PENDING => 'warning',
                        Order::STATUS_PROCESSING => 'info',
                        Order::STATUS_COMPLETED => 'success',
                        Order::STATUS_CANCELLED => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('customer_name')
                    ->label('Имя')
                    ->searchable(),

                TextColumn::make('customer_phone')
                    ->label('Телефон')
                    ->searchable(),

                TextColumn::make('customer_email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('items_count')
                    ->label('Позиций')
                    ->counts('items')
                    ->sortable(),

                TextColumn::make('total')
                    ->label('Сумма')
                    ->money('RUB')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Статус')
                    ->options(Order::$statuses)
                    ->native(false),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
