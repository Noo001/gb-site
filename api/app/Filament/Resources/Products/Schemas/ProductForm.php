<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('external_id'),
                Select::make('category_id')
                    ->relationship('category', 'name')
                    ->placeholder('Без категории')
                    ->nullable(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                TextInput::make('sku')
                    ->label('SKU'),
                TextInput::make('brand'),
                Textarea::make('description')
                    ->columnSpanFull(),
                Textarea::make('content')
                    ->columnSpanFull(),
                TextInput::make('warranty_months')
                    ->numeric(),
                Toggle::make('is_active')
                    ->required(),
                TextInput::make('url')
                    ->url(),
                SpatieMediaLibraryFileUpload::make('images')
                    ->collection('images')
                    ->image()
                    ->multiple()
                    ->reorderable()
                    ->columnSpanFull(),
            ]);
    }
}
