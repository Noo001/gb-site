<?php

namespace App\Filament\Resources\IntegrationLogs;

use App\Filament\Resources\IntegrationLogs\Pages\ListIntegrationLogs;
use App\Filament\Resources\IntegrationLogs\Tables\IntegrationLogsTable;
use App\Models\IntegrationLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class IntegrationLogResource extends Resource
{
    protected static ?string $model = IntegrationLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;

    protected static UnitEnum|string|null $navigationGroup = '1С';

    protected static ?string $navigationLabel = 'Логи обмена с 1С';

    protected static ?string $modelLabel = 'запись лога';

    protected static ?string $pluralModelLabel = 'логи обмена с 1С';

    protected static ?int $navigationSort = 10;

    public static function table(Table $table): Table
    {
        return IntegrationLogsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListIntegrationLogs::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }
}
