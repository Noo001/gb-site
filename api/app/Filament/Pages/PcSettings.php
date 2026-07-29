<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
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
        $this->form->fill([
            'pc_demo_mode' => Setting::get('pc_demo_mode') === '1',
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Toggle::make('pc_demo_mode')
                    ->label('Демо-режим конфигуратора (тестовые данные)')
                    ->helperText('Конфигуратор показывает тестовые комплектующие из таблицы pc_demo_parts вместо реальных товаров.'),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        Setting::set('pc_demo_mode', ! empty($state['pc_demo_mode']) ? '1' : '0');

        Notification::make()
            ->title('Настройки сохранены')
            ->success()
            ->send();
    }
}
