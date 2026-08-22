<?php

namespace App\Filament\Resources\TelegramManagerSettings;

use App\Filament\Pages\ListRecords;
use App\Filament\Resources\TelegramManagerSettings\Pages\CreateTelegramManagerSetting;
use App\Filament\Resources\TelegramManagerSettings\Pages\EditTelegramManagerSetting;
use App\Filament\Resources\TelegramManagerSettings\Pages\ListTelegramManagerSettings;
use App\Filament\Resources\TelegramManagerSettings\Schemas\TelegramManagerSettingForm;
use App\Filament\Resources\TelegramManagerSettings\Tables\TelegramManagerSettingsTable;
use App\Models\TelegramManagerSetting;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class TelegramManagerSettingResource extends Resource
{
    protected static ?string $model = TelegramManagerSetting::class;

    protected static ?string $slug = 'telegram-manager-settings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog8Tooth;

    protected static UnitEnum|string|null $navigationGroup = 'Менеджер-бот';

    protected static ?string $navigationLabel = 'Автоответы';

    protected static ?string $modelLabel = 'автоответ';

    protected static ?string $pluralModelLabel = 'автоответы';

    public static function form(Schema $schema): Schema
    {
        return TelegramManagerSettingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TelegramManagerSettingsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTelegramManagerSettings::route('/'),
            'create' => CreateTelegramManagerSetting::route('/create'),
            'edit' => EditTelegramManagerSetting::route('/{record}/edit'),
        ];
    }
}
