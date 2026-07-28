<?php

namespace App\Filament\Resources\BotActionLogs;

use App\Filament\Resources\BotActionLogs\Pages\ListBotActionLogs;
use App\Filament\Resources\BotActionLogs\Tables\BotActionLogsTable;
use App\Models\BotActionLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class BotActionLogResource extends Resource
{
    protected static ?string $model = BotActionLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static UnitEnum|string|null $navigationGroup = 'Бот';

    protected static ?string $navigationLabel = 'Лог действий бота';

    protected static ?string $modelLabel = 'запись лога';

    protected static ?string $pluralModelLabel = 'лог действий бота';

    public static function table(Table $table): Table
    {
        return BotActionLogsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBotActionLogs::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
