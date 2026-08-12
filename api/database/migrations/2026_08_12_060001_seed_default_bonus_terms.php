<?php

use App\Models\BonusTerm;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (BonusTerm::current()) {
            return;
        }

        BonusTerm::create([
            'version' => 1,
            'content' => implode("\n", [
                '1. Бонусная программа действует для авторизованных пользователей сайта.',
                '2. Бонусы начисляются за регистрацию, ежедневный вход, покупки и участие в акциях.',
                '3. 1 бонус = 1 рубль скидки при оформлении заказа.',
                '4. Бонусы за покупку замораживаются на 7 дней с момента выполнения заказа.',
                '5. Администрация оставляет за собой право изменять правила программы.',
            ]),
            'is_active' => true,
        ]);
    }

    public function down(): void
    {
        BonusTerm::where('version', 1)->delete();
    }
};
