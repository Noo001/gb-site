<?php

namespace App\Filament\Resources\IntegrationLogs\Tables;

use App\Filament\Concerns\HasDefaultTableSettings;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class IntegrationLogsTable
{
    use HasDefaultTableSettings;

    public static function configure(Table $table): Table
    {
        return self::applyDefaults($table)
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
                    ->searchable()
                    ->limit(60)
                    ->wrap(),
                TextColumn::make('method')
                    ->label('Метод')
                    ->badge(),
                TextColumn::make('status_code')
                    ->label('Код')
                    ->badge()
                    ->color(fn (?int $state): string => match (true) {
                        $state === null => 'gray',
                        $state >= 500 => 'danger',
                        $state >= 400 => 'warning',
                        $state >= 200 && $state < 300 => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('response')
                    ->label('Ответ / ошибка')
                    ->limit(80)
                    ->wrap()
                    ->formatStateUsing(function ($state): string {
                        if (is_array($state)) {
                            $json = json_encode($state, JSON_UNESCAPED_UNICODE);

                            return $json === false ? '[invalid json]' : $json;
                        }

                        return strip_tags((string) $state);
                    })
                    ->color(function ($state): string {
                        $text = is_array($state) ? json_encode($state, JSON_UNESCAPED_UNICODE) : (string) $state;

                        return str_contains($text, '"success":false') ? 'danger' : 'gray';
                    }),
                TextColumn::make('duration_ms')
                    ->label('Время, мс')
                    ->numeric(),
                TextColumn::make('ip')
                    ->label('IP')
                    ->copyable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Filter::make('errors')
                    ->label('Только ошибки')
                    ->query(fn (Builder $query): Builder => $query->where('status_code', '>=', 400)),
            ])
            ->recordActions([
                //
            ])
            ->toolbarActions([
                //
            ]);
    }
}
