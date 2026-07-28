<?php

namespace App\Filament\Resources\BotTradeInPrices\Pages;

use App\Filament\Resources\BotTradeInPrices\BotTradeInPriceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBotTradeInPrices extends ListRecords
{
    protected static string $resource = BotTradeInPriceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
