<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use UnitEnum;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class BonusSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Gift;

    protected static UnitEnum|string|null $navigationGroup = 'Сайт';

    protected static ?string $navigationLabel = 'Настройки бонусов';

    protected static ?string $title = 'Настройки бонусов';

    protected string $view = 'filament.pages.bonus-settings';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyPermission(['catalog.manage', 'site.manage']) ?? false;
    }

    public function mount(): void
    {
        $this->form->fill([
            'bonus_free_spins_enabled' => Setting::get('bonus_free_spins_enabled', '1') === '1',
            'bonus_registration_amount' => (int) Setting::get('bonus_registration_amount', 500),
            'bonus_daily_amount' => (int) Setting::get('bonus_daily_amount', 10),
            'bonus_streak_amount' => (int) Setting::get('bonus_streak_amount', 30),
            'bonus_spin_cost' => (int) Setting::get('bonus_spin_cost', 10),
            'bonus_purchase_percent' => (float) Setting::get('bonus_purchase_percent', 0.25),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Toggle::make('bonus_free_spins_enabled')
                    ->label('Бесплатные прокрутки включены')
                    ->helperText('Когда выключено, пользователи не получают бесплатных прокруток за ежедневный вход и не видят кнопку «Бесплатная прокрутка».')
                    ->default(true),

                TextInput::make('bonus_registration_amount')
                    ->label('Бонус за регистрацию, ₽')
                    ->numeric()
                    ->minValue(0)
                    ->required()
                    ->default(500),

                TextInput::make('bonus_daily_amount')
                    ->label('Ежедневный бонус, ₽')
                    ->numeric()
                    ->minValue(0)
                    ->required()
                    ->default(10),

                TextInput::make('bonus_streak_amount')
                    ->label('Бонус за серию 7 дней, ₽')
                    ->numeric()
                    ->minValue(0)
                    ->required()
                    ->default(30),

                TextInput::make('bonus_spin_cost')
                    ->label('Стоимость платной прокрутки, ₽')
                    ->numeric()
                    ->minValue(0)
                    ->required()
                    ->default(10),

                TextInput::make('bonus_purchase_percent')
                    ->label('Процент бонусов от покупки, %')
                    ->numeric()
                    ->minValue(0)
                    ->step(0.01)
                    ->required()
                    ->default(0.25),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        Setting::set('bonus_free_spins_enabled', ! empty($state['bonus_free_spins_enabled']) ? '1' : '0');
        Setting::set('bonus_registration_amount', (int) ($state['bonus_registration_amount'] ?? 500));
        Setting::set('bonus_daily_amount', (int) ($state['bonus_daily_amount'] ?? 10));
        Setting::set('bonus_streak_amount', (int) ($state['bonus_streak_amount'] ?? 30));
        Setting::set('bonus_spin_cost', (int) ($state['bonus_spin_cost'] ?? 10));
        Setting::set('bonus_purchase_percent', (float) ($state['bonus_purchase_percent'] ?? 0.25));

        Notification::make()
            ->title('Настройки бонусов сохранены')
            ->success()
            ->send();
    }
}
