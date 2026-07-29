<?php

namespace App\Console\Commands;

use App\Models\BotProduct;
use App\Models\IntegrationLog;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class Reconcile1CData extends Command
{
    protected $signature = 'reconcile:1c
        {--deactivate-missing : Деактивировать товары сайта без связи с 1С}
        {--cleanup-logs : Удалить логи обмена старше 30 дней}
        {--dry-run : Показать отчёт без изменений}';

    protected $description = 'Сверяет данные сайта с реальными данными 1С (bot_products) и формирует отчёт';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $deactivateMissing = $this->option('deactivate-missing');
        $cleanupLogs = $this->option('cleanup-logs');

        $report = [
            'checked_at' => now()->toDateTimeString(),
            'products_site' => Product::count(),
            'products_site_active' => Product::where('is_active', true)->count(),
            'products_1c' => BotProduct::count(),
            'products_1c_active' => BotProduct::where('is_active', true)->count(),
            'without_uuid_1c' => 0,
            'not_found_in_1c' => 0,
            'price_mismatch' => 0,
            'stock_mismatch' => 0,
            'deactivated' => 0,
            'logs_deleted' => 0,
        ];

        // Товары без uuid_1c — это данные, созданные на сайте (демо/тест/ручной ввод).
        $withoutUuid = Product::query()
            ->whereNull('uuid_1c')
            ->select(['id', 'name', 'sku', 'is_active'])
            ->get();

        $report['without_uuid_1c'] = $withoutUuid->count();

        // Товары с uuid_1c, которых нет в индексе 1С (bot_products).
        $linkedProductIds = BotProduct::query()->pluck('product_id')->toArray();
        $notFoundIn1c = Product::query()
            ->whereNotNull('uuid_1c')
            ->whereNotIn('id', $linkedProductIds)
            ->select(['id', 'name', 'sku', 'uuid_1c', 'is_active'])
            ->get();

        $report['not_found_in_1c'] = $notFoundIn1c->count();

        // Сверка цен и остатков по связанным товарам.
        $siteProducts = Product::query()
            ->whereNotNull('uuid_1c')
            ->whereIn('id', $linkedProductIds)
            ->with(['offers.prices', 'offers.stocks'])
            ->get();

        foreach ($siteProducts as $product) {
            $bot = BotProduct::query()->where('product_id', $product->id)->first();

            if (! $bot) {
                continue;
            }

            $sitePrice = $product->offers
                ->flatMap(fn ($offer) => $offer->prices->where('price', '>', 0))
                ->min('price');

            $siteStock = $product->offers
                ->flatMap(fn ($offer) => $offer->stocks)
                ->sum(fn ($stock) => max((float) $stock->quantity - (float) $stock->reserved, 0));

            if ($sitePrice !== null && (float) $sitePrice !== (float) $bot->price) {
                $report['price_mismatch']++;
            }

            if ((float) $siteStock !== (float) $bot->quantity) {
                $report['stock_mismatch']++;
            }
        }

        // Деактивация мусора.
        if ($deactivateMissing && ! $dryRun) {
            $toDeactivate = Product::query()
                ->where(function ($query) use ($linkedProductIds) {
                    $query->whereNull('uuid_1c')
                        ->orWhereNotIn('id', $linkedProductIds);
                })
                ->where('is_active', true)
                ->pluck('id');

            Product::whereIn('id', $toDeactivate)->update(['is_active' => false]);
            $report['deactivated'] = $toDeactivate->count();
        }

        // Очистка старых логов.
        if ($cleanupLogs && ! $dryRun) {
            $cutoff = Carbon::now()->subDays(30);
            $report['logs_deleted'] = IntegrationLog::query()
                ->where('created_at', '<', $cutoff)
                ->delete();
        }

        // Вывод отчёта.
        $this->newLine();
        $this->info('Отчёт сверки с 1С');
        $this->table(
            ['Показатель', 'Значение'],
            [
                ['Дата проверки', $report['checked_at']],
                ['Товаров на сайте', $report['products_site']],
                ['Активных на сайте', $report['products_site_active']],
                ['Товаров в индексе 1С', $report['products_1c']],
                ['Активных в индексе 1С', $report['products_1c_active']],
                ['Без uuid_1c (мусор/демо)', $report['without_uuid_1c']],
                ['Не найдены в 1С', $report['not_found_in_1c']],
                ['Расхождения цен', $report['price_mismatch']],
                ['Расхождения остатков', $report['stock_mismatch']],
                ['Деактивировано', $report['deactivated']],
                ['Удалено логов старше 30д', $report['logs_deleted']],
            ]
        );

        // Сохраняем отчёт в лог.
        Log::channel('daily')->info('reconcile:1c report', $report);

        if ($dryRun) {
            $this->warn('Режим dry-run: изменения не применены.');
        }

        return self::SUCCESS;
    }
}
