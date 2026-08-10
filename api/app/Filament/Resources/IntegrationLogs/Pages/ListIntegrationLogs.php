<?php

namespace App\Filament\Resources\IntegrationLogs\Pages;

use App\Filament\Pages\ListRecords;
use App\Filament\Resources\IntegrationLogs\IntegrationLogResource;

class ListIntegrationLogs extends ListRecords
{
    protected static string $resource = IntegrationLogResource::class;
}
