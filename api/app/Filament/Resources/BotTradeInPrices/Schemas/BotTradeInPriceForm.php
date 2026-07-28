<?php

namespace App\Filament\Resources\BotTradeInPrices\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BotTradeInPriceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('brand')
                    ->required(),
                TextInput::make('model')
                    ->required(),
                TextInput::make('storage'),
                TextInput::make('condition')
                    ->default('working'),
                TextInput::make('price')
                    ->required()
                    ->numeric(),
                Toggle::make('is_active')
                    ->default(true),
            ]);
    }
}
