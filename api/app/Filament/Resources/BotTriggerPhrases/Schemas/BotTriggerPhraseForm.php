<?php

namespace App\Filament\Resources\BotTriggerPhrases\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BotTriggerPhraseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('phrase')
                    ->required()
                    ->maxLength(255),
                TextInput::make('action')
                    ->maxLength(128),
                TextInput::make('response')
                    ->maxLength(1000)
                    ->columnSpanFull(),
                TextInput::make('sort')
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')
                    ->default(true),
            ]);
    }
}
