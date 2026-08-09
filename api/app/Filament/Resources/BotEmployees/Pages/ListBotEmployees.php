<?php

namespace App\Filament\Resources\BotEmployees\Pages;

use App\Filament\Resources\BotEmployees\BotEmployeeResource;
use Filament\Actions\CreateAction;
use App\Filament\Pages\ListRecords;

class ListBotEmployees extends ListRecords
{
    protected static string $resource = BotEmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
