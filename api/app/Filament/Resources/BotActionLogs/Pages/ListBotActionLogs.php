<?php

namespace App\Filament\Resources\BotActionLogs\Pages;

use App\Filament\Resources\BotActionLogs\BotActionLogResource;
use Filament\Resources\Pages\ListRecords;

class ListBotActionLogs extends ListRecords
{
    protected static string $resource = BotActionLogResource::class;
}
