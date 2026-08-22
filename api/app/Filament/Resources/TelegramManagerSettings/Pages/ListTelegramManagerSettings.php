<?php

namespace App\Filament\Resources\TelegramManagerSettings\Pages;

use App\Filament\Pages\ListRecords;
use App\Filament\Resources\TelegramManagerSettings\TelegramManagerSettingResource;
use Filament\Actions\CreateAction;

class ListTelegramManagerSettings extends ListRecords
{
    protected static string $resource = TelegramManagerSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
