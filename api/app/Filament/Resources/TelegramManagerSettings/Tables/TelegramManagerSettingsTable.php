<?php

namespace App\Filament\Resources\TelegramManagerSettings\Tables;

use App\Filament\Concerns\HasDefaultTableSettings;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TelegramManagerSettingsTable
{
    use HasDefaultTableSettings;

    public static function configure(Table $table): Table
    {
        return self::applyDefaults($table)
            ->columns([
                TextColumn::make('key')
                    ->label('Ключ')
                    ->searchable(),
                TextColumn::make('label')
                    ->label('Название')
                    ->searchable(),
                TextColumn::make('value')
                    ->label('Текст')
                    ->limit(80),
                IconColumn::make('is_active')
                    ->label('Активен')
                    ->boolean(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
