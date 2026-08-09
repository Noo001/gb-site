<?php

namespace App\Filament\Resources\BotEmployees;

use App\Filament\Resources\BotEmployees\Pages\CreateBotEmployee;
use App\Filament\Resources\BotEmployees\Pages\EditBotEmployee;
use App\Filament\Resources\BotEmployees\Pages\ListBotEmployees;
use App\Filament\Resources\BotEmployees\Schemas\BotEmployeeForm;
use App\Filament\Resources\BotEmployees\Tables\BotEmployeesTable;
use App\Models\BotEmployee;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class BotEmployeeResource extends Resource
{
    protected static ?string $model = BotEmployee::class;

    protected static ?string $slug = 'bot-employees';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static UnitEnum|string|null $navigationGroup = 'Бот';

    protected static ?string $navigationLabel = 'Сотрудники';

    protected static ?string $modelLabel = 'сотрудник';

    protected static ?string $pluralModelLabel = 'сотрудники';

    public static function form(Schema $schema): Schema
    {
        return BotEmployeeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BotEmployeesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBotEmployees::route('/'),
            'create' => CreateBotEmployee::route('/create'),
            'edit' => EditBotEmployee::route('/{record}/edit'),
        ];
    }
}
