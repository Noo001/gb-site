<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class PcSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = 'Настройки ПК-конфигуратора';

    protected static ?string $title = 'Настройки ПК-конфигуратора';

    protected string $view = 'filament.pages.pc-settings';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyPermission(['catalog.manage', 'site.manage']) ?? false;
    }

    public function mount(): void
    {
        $assemblyPrices = Setting::get('pc_assembly_prices');
        if (is_string($assemblyPrices)) {
            $assemblyPrices = json_decode($assemblyPrices, true);
        }
        if (empty($assemblyPrices) || ! is_array($assemblyPrices)) {
            $assemblyPrices = [
                ['name' => 'Lite', 'min' => 0, 'max' => 60000, 'price' => 4500],
                ['name' => 'Standart', 'min' => 60000, 'max' => 140000, 'price' => 6000],
                ['name' => 'Gaming', 'min' => 140000, 'max' => 300000, 'price' => 8000],
                ['name' => 'Ultra', 'min' => 300000, 'max' => null, 'price' => 10000],
            ];
        }

        $this->form->fill([
            'assembly_prices' => $assemblyPrices,
            'pc_demo_mode' => Setting::get('pc_demo_mode') === '1',
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Repeater::make('assembly_prices')
                    ->label('Тарифы сборки ПК')
                    ->helperText('Для каждого тарифа укажите диапазон суммы комплектующих и стоимость сборки. Последняя строка с пустым полем «До» охватывает все суммы сверху.')
                    ->schema([
                        TextInput::make('name')
                            ->label('Название тарифа')
                            ->required(),
                        TextInput::make('min')
                            ->label('От суммы, ₽')
                            ->numeric()
                            ->default(0)
                            ->required(),
                        TextInput::make('max')
                            ->label('До суммы, ₽ (пусто — без ограничения)')
                            ->numeric()
                            ->nullable(),
                        TextInput::make('price')
                            ->label('Стоимость сборки, ₽')
                            ->numeric()
                            ->required(),
                    ])
                    ->columns(4)
                    ->addable(false)
                    ->deletable(false)
                    ->reorderable(false)
                    ->default([
                        ['name' => 'Lite', 'min' => 0, 'max' => 60000, 'price' => 4500],
                        ['name' => 'Standart', 'min' => 60000, 'max' => 140000, 'price' => 6000],
                        ['name' => 'Gaming', 'min' => 140000, 'max' => 300000, 'price' => 8000],
                        ['name' => 'Ultra', 'min' => 300000, 'max' => null, 'price' => 10000],
                    ]),
                Toggle::make('pc_demo_mode')
                    ->label('Демо-режим конфигуратора (тестовые данные)')
                    ->helperText('Конфигуратор показывает тестовые комплектующие из таблицы pc_demo_parts вместо реальных товаров.'),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        $prices = $state['assembly_prices'] ?? [];
        Setting::set('pc_assembly_prices', json_encode($prices, JSON_UNESCAPED_UNICODE));
        Setting::set('pc_demo_mode', ! empty($state['pc_demo_mode']) ? '1' : '0');

        Notification::make()
            ->title('Настройки сохранены')
            ->success()
            ->send();
    }
}
