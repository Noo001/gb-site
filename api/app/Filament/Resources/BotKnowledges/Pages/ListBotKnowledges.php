<?php

namespace App\Filament\Resources\BotKnowledges\Pages;

use App\Filament\Resources\BotKnowledges\BotKnowledgeResource;
use Filament\Actions\CreateAction;
use App\Filament\Pages\ListRecords;

class ListBotKnowledges extends ListRecords
{
    protected static string $resource = BotKnowledgeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
