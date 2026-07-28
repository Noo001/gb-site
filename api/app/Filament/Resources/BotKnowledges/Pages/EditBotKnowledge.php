<?php

namespace App\Filament\Resources\BotKnowledges\Pages;

use App\Filament\Resources\BotKnowledges\BotKnowledgeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBotKnowledge extends EditRecord
{
    protected static string $resource = BotKnowledgeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
