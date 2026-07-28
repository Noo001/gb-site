<?php

namespace App\Filament\Resources\BotTradeInPrices;

use App\Filament\Resources\BotTradeInPrices\Pages\CreateBotTradeInPrice;
use App\Filament\Resources\BotTradeInPrices\Pages\EditBotTradeInPrice;
use App\Filament\Resources\BotTradeInPrices\Pages\ListBotTradeInPrices;
use App\Filament\Resources\BotTradeInPrices\Schemas\BotTradeInPriceForm;
use App\Filament\Resources\BotTradeInPrices\Tables\BotTradeInPricesTable;
use App\Models\BotTradeInPrice;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class BotTradeInPriceResource extends Resource
{
    protected static ?string $model = BotTradeInPrice::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

    protected static UnitEnum|string|null $navigationGroup = 'Бот';

    protected static ?string $navigationLabel = 'Trade-in цены';

    protected static ?string $modelLabel = 'цена trade-in';

    protected static ?string $pluralModelLabel = 'trade-in цены';

    public static function form(Schema $schema): Schema
    {
        return BotTradeInPriceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BotTradeInPricesTable::configure($table);
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
            'index' => ListBotTradeInPrices::route('/'),
            'create' => CreateBotTradeInPrice::route('/create'),
            'edit' => EditBotTradeInPrice::route('/{record}/edit'),
        ];
    }
}
