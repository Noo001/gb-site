<?php

namespace App\Filament\Resources\BotEmployees\Pages;

use App\Filament\Resources\BotEmployees\BotEmployeeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBotEmployee extends EditRecord
{
    protected static string $resource = BotEmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
