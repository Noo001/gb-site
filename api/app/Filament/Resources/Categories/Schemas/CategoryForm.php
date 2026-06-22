<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('parent_id')
                    ->relationship('parent', 'name')
                    ->placeholder('Корневая категория')
                    ->nullable(),
                TextInput::make('external_id'),
                TextInput::make('name')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                TextInput::make('full_path')
                    ->placeholder('avtoelektronika/avtomagnitoly'),
                Textarea::make('description')
                    ->columnSpanFull(),
                TextInput::make('sort')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')
                    ->required(),
                TextInput::make('seo_title'),
                Textarea::make('seo_description')
                    ->columnSpanFull(),
                Textarea::make('seo_keywords')
                    ->columnSpanFull(),
                TextInput::make('seo_h1'),
                TextInput::make('url')
                    ->url(),
                SpatieMediaLibraryFileUpload::make('image')
                    ->collection('image')
                    ->image()
                    ->maxFiles(1)
                    ->columnSpanFull(),
            ]);
    }
}
