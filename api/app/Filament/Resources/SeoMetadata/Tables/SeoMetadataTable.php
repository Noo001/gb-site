<?php

namespace App\Filament\Resources\SeoMetadata\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SeoMetadataTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('entity_type')
                    ->searchable(),
                TextColumn::make('entity_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('url')
                    ->searchable(),
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('h1')
                    ->searchable(),
                TextColumn::make('og_title')
                    ->searchable(),
                ImageColumn::make('og_image'),
                TextColumn::make('canonical')
                    ->searchable(),
                TextColumn::make('robots')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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
