<?php

namespace App\Filament\Pages;

use Filament\Resources\Pages\ListRecords as BaseListRecords;

abstract class ListRecords extends BaseListRecords
{
    public function updatedTableRecordsPerPage($value): void
    {
        session(['filament.records_per_page' => $value]);
    }
}
