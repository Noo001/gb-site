<?php

namespace App\Filament\Resources\BotTriggerPhrases;

use App\Filament\Resources\BotTriggerPhrases\Pages\CreateBotTriggerPhrase;
use App\Filament\Resources\BotTriggerPhrases\Pages\EditBotTriggerPhrase;
use App\Filament\Resources\BotTriggerPhrases\Pages\ListBotTriggerPhrases;
use App\Filament\Resources\BotTriggerPhrases\Schemas\BotTriggerPhraseForm;
use App\Filament\Resources\BotTriggerPhrases\Tables\BotTriggerPhrasesTable;
use App\Models\BotTriggerPhrase;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class BotTriggerPhraseResource extends Resource
{
    protected static ?string $model = BotTriggerPhrase::class;

    protected static ?string $slug = 'bot-trigger-phrases';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static UnitEnum|string|null $navigationGroup = 'Бот';

    protected static ?string $navigationLabel = 'Триггерные фразы';

    protected static ?string $modelLabel = 'триггерная фраза';

    protected static ?string $pluralModelLabel = 'триггерные фразы';

    public static function form(Schema $schema): Schema
    {
        return BotTriggerPhraseForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BotTriggerPhrasesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBotTriggerPhrases::route('/'),
            'create' => CreateBotTriggerPhrase::route('/create'),
            'edit' => EditBotTriggerPhrase::route('/{record}/edit'),
        ];
    }
}
