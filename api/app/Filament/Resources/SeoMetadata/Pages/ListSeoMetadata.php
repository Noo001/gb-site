<?php

namespace App\Filament\Resources\SeoMetadata\Pages;

use App\Filament\Resources\SeoMetadata\SeoMetadataResource;
use Filament\Actions\CreateAction;
use App\Filament\Pages\ListRecords;

class ListSeoMetadata extends ListRecords
{
    protected static string $resource = SeoMetadataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
