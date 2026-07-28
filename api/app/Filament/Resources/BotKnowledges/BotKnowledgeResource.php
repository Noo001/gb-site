<?php

namespace App\Filament\Resources\BotKnowledges;

use App\Filament\Resources\BotKnowledges\Pages\CreateBotKnowledge;
use App\Filament\Resources\BotKnowledges\Pages\EditBotKnowledge;
use App\Filament\Resources\BotKnowledges\Pages\ListBotKnowledges;
use App\Filament\Resources\BotKnowledges\Schemas\BotKnowledgeForm;
use App\Filament\Resources\BotKnowledges\Tables\BotKnowledgesTable;
use App\Models\BotKnowledge;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class BotKnowledgeResource extends Resource
{
    protected static ?string $model = BotKnowledge::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static UnitEnum|string|null $navigationGroup = 'Бот';

    protected static ?string $navigationLabel = 'Справочник бота';

    protected static ?string $modelLabel = 'запись справочника';

    protected static ?string $pluralModelLabel = 'справочник бота';

    public static function form(Schema $schema): Schema
    {
        return BotKnowledgeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BotKnowledgesTable::configure($table);
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
            'index' => ListBotKnowledges::route('/'),
            'create' => CreateBotKnowledge::route('/create'),
            'edit' => EditBotKnowledge::route('/{record}/edit'),
        ];
    }
}
