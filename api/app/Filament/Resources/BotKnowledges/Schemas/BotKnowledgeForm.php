<?php

namespace App\Filament\Resources\BotKnowledges\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BotKnowledgeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('type')
                    ->options([
                        'config' => 'config',
                        'service' => 'service',
                        'trigger' => 'trigger',
                    ])
                    ->required(),
                TextInput::make('group'),
                TextInput::make('key'),
                KeyValue::make('payload')
                    ->columnSpanFull(),
                TextInput::make('sort')
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')
                    ->default(true),
            ]);
    }
}
