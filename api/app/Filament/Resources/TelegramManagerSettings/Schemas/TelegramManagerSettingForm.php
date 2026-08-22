<?php

namespace App\Filament\Resources\TelegramManagerSettings\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class TelegramManagerSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('key')
                    ->label('Ключ')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->helperText('Технический ключ, например: welcome_message, franchise_conditions'),
                TextInput::make('label')
                    ->label('Название')
                    ->required()
                    ->maxLength(255),
                Textarea::make('value')
                    ->label('Текст ответа')
                    ->rows(6)
                    ->columnSpanFull(),
                Toggle::make('is_active')
                    ->label('Активен')
                    ->default(true),
            ]);
    }
}
