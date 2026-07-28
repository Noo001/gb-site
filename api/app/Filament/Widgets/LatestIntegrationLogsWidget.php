<?php

namespace App\Filament\Widgets;

use App\Models\IntegrationLog;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LatestIntegrationLogsWidget extends TableWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Последние события обмена 1С')
            ->query(
                IntegrationLog::query()
                    ->orderByDesc('created_at')
                    ->limit(10)
            )
            ->paginated(false)
            ->columns([
                TextColumn::make('created_at')
                    ->label('Дата')
                    ->dateTime('d.m.Y H:i:s'),
                TextColumn::make('direction')
                    ->label('Направление'),
                TextColumn::make('endpoint')
                    ->label('Endpoint')
                    ->limit(60),
                TextColumn::make('method')
                    ->label('Метод'),
            ]);
    }
}
