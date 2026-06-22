<?php

namespace App\Filament\Resources\Pages\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('url')
                    ->url()
                    ->required(),
                TextInput::make('type')
                    ->required()
                    ->default('page'),
                TextInput::make('title')
                    ->required(),
                TextInput::make('slug'),
                Textarea::make('content')
                    ->columnSpanFull(),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
