<?php

namespace App\Filament\Widgets;

use App\Models\IntegrationLog;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Contracts\Auth\Authenticatable;

class LatestIntegrationLogsWidget extends TableWidget
{
    protected int | string | array $columnSpan = 'full';

    public static function canView(?Authenticatable $user = null): bool
    {
        return auth()->user()?->hasAnyPermission(['monitoring.view', 'monitoring.manage']) ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Последние события обмена 1С')
            ->query(
                IntegrationLog::query()
                    ->orderByDesc('created_at')
                    ->limit(20)
            )
            ->paginated(false)
            ->columns([
                TextColumn::make('created_at')
                    ->label('Дата')
                    ->dateTime('d.m.Y H:i:s')
                    ->sortable(),
                TextColumn::make('direction')
                    ->label('Направление')
                    ->badge(),
                TextColumn::make('endpoint')
                    ->label('Endpoint')
                    ->limit(60)
                    ->wrap(),
                TextColumn::make('method')
                    ->label('Метод')
                    ->badge(),
                TextColumn::make('status_code')
                    ->label('Код')
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state >= 500 => 'danger',
                        $state >= 400 => 'warning',
                        $state >= 200 && $state < 300 => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('response')
                    ->label('Ответ / ошибка')
                    ->limit(80)
                    ->wrap()
                    ->formatStateUsing(fn (?string $state): string => $state ? strip_tags($state) : '—')
                    ->color(fn (string $state): string => str_contains($state, '"success":false') ? 'danger' : 'gray'),
            ])
            ->striped();
    }
}
