<?php

namespace App\Filament\Resources\TelegramManagerEmployees\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class TelegramManagerEmployeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('full_name')
                    ->label('ФИО / имя')
                    ->required()
                    ->maxLength(255),
                TextInput::make('telegram_chat_id')
                    ->label('Telegram Chat ID')
                    ->required()
                    ->maxLength(255)
                    ->helperText('Числовой ID чата менеджера. Можно получить, написав @userinfobot или посмотрев в логах бота.'),
                Toggle::make('is_active')
                    ->label('Активен')
                    ->default(true),
            ]);
    }
}
