<?php

namespace App\Filament\Resources\BotActionLogs\Tables;

use App\Filament\Concerns\HasDefaultTableSettings;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BotActionLogsTable
{
    use HasDefaultTableSettings;

    public static function configure(Table $table): Table
    {
        return self::applyDefaults($table)
            ->columns([
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('channel')
                    ->searchable(),
                TextColumn::make('action')
                    ->searchable(),
                TextColumn::make('ip'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->recordActions([
                //
            ])
            ->toolbarActions([
                //
            ]);
    }
}
