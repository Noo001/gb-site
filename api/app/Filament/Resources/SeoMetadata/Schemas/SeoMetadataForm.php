<?php

namespace App\Filament\Resources\SeoMetadata\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class SeoMetadataForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('entity_type')
                    ->required(),
                TextInput::make('entity_id')
                    ->required()
                    ->numeric(),
                TextInput::make('url')
                    ->url(),
                TextInput::make('title'),
                Textarea::make('description')
                    ->columnSpanFull(),
                Textarea::make('keywords')
                    ->columnSpanFull(),
                TextInput::make('h1'),
                TextInput::make('og_title'),
                Textarea::make('og_description')
                    ->columnSpanFull(),
                FileUpload::make('og_image')
                    ->image(),
                TextInput::make('canonical'),
                TextInput::make('robots'),
                TextInput::make('json_ld'),
            ]);
    }
}
