<?php

namespace App\Filament\Resources\TelegramManagerEmployees;

use App\Filament\Pages\ListRecords;
use App\Filament\Resources\TelegramManagerEmployees\Pages\CreateTelegramManagerEmployee;
use App\Filament\Resources\TelegramManagerEmployees\Pages\EditTelegramManagerEmployee;
use App\Filament\Resources\TelegramManagerEmployees\Pages\ListTelegramManagerEmployees;
use App\Filament\Resources\TelegramManagerEmployees\Schemas\TelegramManagerEmployeeForm;
use App\Filament\Resources\TelegramManagerEmployees\Tables\TelegramManagerEmployeesTable;
use App\Models\TelegramManagerEmployee;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class TelegramManagerEmployeeResource extends Resource
{
    protected static ?string $model = TelegramManagerEmployee::class;

    protected static ?string $slug = 'telegram-manager-employees';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static UnitEnum|string|null $navigationGroup = 'Менеджер-бот';

    protected static ?string $navigationLabel = 'Менеджеры';

    protected static ?string $modelLabel = 'менеджер';

    protected static ?string $pluralModelLabel = 'менеджеры';

    public static function form(Schema $schema): Schema
    {
        return TelegramManagerEmployeeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TelegramManagerEmployeesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTelegramManagerEmployees::route('/'),
            'create' => CreateTelegramManagerEmployee::route('/create'),
            'edit' => EditTelegramManagerEmployee::route('/{record}/edit'),
        ];
    }
}
