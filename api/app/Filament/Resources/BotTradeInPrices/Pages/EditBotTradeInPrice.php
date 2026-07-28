<?php

namespace App\Filament\Resources\BotTradeInPrices\Pages;

use App\Filament\Resources\BotTradeInPrices\BotTradeInPriceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBotTradeInPrice extends EditRecord
{
    protected static string $resource = BotTradeInPriceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
