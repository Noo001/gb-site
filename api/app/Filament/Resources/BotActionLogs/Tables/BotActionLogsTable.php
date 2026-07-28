<?php

namespace App\Filament\Resources\BotActionLogs\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BotActionLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
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
