<?php

namespace App\Filament\Resources\BotEmployees\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BotEmployeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('full_name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('b24_token')
                    ->label('Токен B24')
                    ->maxLength(255),
                TextInput::make('telegram_chat_id')
                    ->label('Telegram Chat ID')
                    ->maxLength(255)
                    ->helperText('Числовой ID чата менеджера в Telegram для пересылки лидов. Получить у @userinfobot.'),
                TextInput::make('department')
                    ->label('Отдел')
                    ->maxLength(255),
                KeyValue::make('permissions')
                    ->label('Права доступа')
                    ->columnSpanFull(),
                Toggle::make('is_active')
                    ->default(true),
            ]);
    }
}
