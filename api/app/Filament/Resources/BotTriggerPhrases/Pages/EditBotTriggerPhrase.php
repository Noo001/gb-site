<?php

namespace App\Filament\Resources\BotTriggerPhrases\Pages;

use App\Filament\Resources\BotTriggerPhrases\BotTriggerPhraseResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBotTriggerPhrase extends EditRecord
{
    protected static string $resource = BotTriggerPhraseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
