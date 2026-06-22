<?php

namespace App\Filament\Resources\Regions\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class RegionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('external_id'),
                TextInput::make('name')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                Select::make('default_store_id')
                    ->relationship('defaultStore', 'name'),
                Select::make('prices_store_id')
                    ->relationship('pricesStore', 'name'),
                Select::make('stocks_store_id')
                    ->relationship('stocksStore', 'name'),
                Toggle::make('is_default')
                    ->required(),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
