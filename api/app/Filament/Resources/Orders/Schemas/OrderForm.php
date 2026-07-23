<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Order;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('status')
                    ->label('Статус')
                    ->options(Order::$statuses)
                    ->required()
                    ->native(false),

                TextInput::make('customer_name')
                    ->label('Имя клиента')
                    ->disabled(),

                TextInput::make('customer_phone')
                    ->label('Телефон')
                    ->disabled(),

                TextInput::make('customer_email')
                    ->label('Email')
                    ->disabled(),

                TextInput::make('customer_city')
                    ->label('Город')
                    ->disabled(),

                Textarea::make('customer_comment')
                    ->label('Комментарий клиента')
                    ->disabled()
                    ->columnSpanFull(),

                Textarea::make('manager_comment')
                    ->label('Комментарий менеджера')
                    ->columnSpanFull(),

                TextInput::make('total')
                    ->label('Сумма')
                    ->disabled()
                    ->prefix('₽'),
            ]);
    }
}
