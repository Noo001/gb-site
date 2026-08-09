<?php

namespace App\Filament\Resources\BotTriggerPhrases\Pages;

use App\Filament\Resources\BotTriggerPhrases\BotTriggerPhraseResource;
use Filament\Actions\CreateAction;
use App\Filament\Pages\ListRecords;

class ListBotTriggerPhrases extends ListRecords
{
    protected static string $resource = BotTriggerPhraseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
