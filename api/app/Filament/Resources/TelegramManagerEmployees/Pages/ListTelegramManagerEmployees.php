<?php

namespace App\Filament\Resources\TelegramManagerEmployees\Pages;

use App\Filament\Pages\ListRecords;
use App\Filament\Resources\TelegramManagerEmployees\TelegramManagerEmployeeResource;
use Filament\Actions\CreateAction;

class ListTelegramManagerEmployees extends ListRecords
{
    protected static string $resource = TelegramManagerEmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
