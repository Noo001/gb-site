<?php

namespace App\Filament\Resources\BotEmployees\Tables;

use App\Filament\Concerns\HasDefaultTableSettings;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BotEmployeesTable
{
    use HasDefaultTableSettings;

    public static function configure(Table $table): Table
    {
        return self::applyDefaults($table)
            ->columns([
                TextColumn::make('full_name')
                    ->searchable(),
                TextColumn::make('department')
                    ->searchable(),
                TextColumn::make('b24_token')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('telegram_chat_id')
                    ->label('Telegram Chat ID')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')
                    ->boolean(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
