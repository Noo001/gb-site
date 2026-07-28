<?php

namespace App\Filament\Resources\BotProducts;

use App\Filament\Resources\BotProducts\Pages\ListBotProducts;
use App\Filament\Resources\BotProducts\Tables\BotProductsTable;
use App\Models\BotProduct;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class BotProductResource extends Resource
{
    protected static ?string $model = BotProduct::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    protected static UnitEnum|string|null $navigationGroup = 'Бот';

    protected static ?string $navigationLabel = 'Индекс товаров бота';

    protected static ?string $modelLabel = 'товар бота';

    protected static ?string $pluralModelLabel = 'индекс товаров бота';

    public static function table(Table $table): Table
    {
        return BotProductsTable::configure($table);
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
            'index' => ListBotProducts::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
