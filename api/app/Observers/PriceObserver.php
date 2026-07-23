<?php

namespace App\Observers;

use App\Models\Price;

class PriceObserver
{
    public function saved(Price $price): void
    {
        // Уведомления в 1С временно отключены: EXPORT_1C_WEBHOOK_URL не настроен,
        // джоба только падала в failed_jobs. Вернуть dispatch при настройке вебхука.
        return;
    }
}
