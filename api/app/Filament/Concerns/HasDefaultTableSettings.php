<?php

namespace App\Filament\Concerns;

use Filament\Tables\Table;

trait HasDefaultTableSettings
{
    public static function applyDefaults(Table $table): Table
    {
        return $table
            ->paginationPageOptions([10, 25, 50, 100, 200])
            ->defaultPaginationPageOption(200)
            ->persistSearchInSession()
            ->persistFiltersInSession()
            ->persistSortInSession()
            ->persistColumnToggleInSession();
    }
}
