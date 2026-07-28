<?php

namespace App\Filament\Widgets;

use App\Models\Store;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class MonitoringStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return array_merge(
            $this->getCatalogStats(),
            $this->getExchangeStats(),
            $this->getHealthStats(),
        );
    }

    /**
     * @return array<Stat>
     */
    protected function getCatalogStats(): array
    {
        $productsTotal = DB::table('products')->count();
        $productsActive = DB::table('products')->where('is_active', true)->count();

        return [
            Stat::make('Товары', $productsTotal)
                ->description("Активных: {$productsActive}")
                ->color('success'),
            Stat::make('Торговые предложения', DB::table('offers')->count())
                ->color('success'),
            Stat::make('Цены', DB::table('prices')->count())
                ->color('success'),
            Stat::make('Остатки', DB::table('stocks')->count())
                ->color('success'),
            Stat::make('Категории', DB::table('categories')->count())
                ->color('success'),
            Stat::make('Склады', Store::count())
                ->description('Без учёта удалённых')
                ->color('success'),
        ];
    }

    /**
     * @return array<Stat>
     */
    protected function getExchangeStats(): array
    {
        $lastLogAt = DB::table('integration_logs')->max('created_at');
        $logsLast24h = DB::table('integration_logs')
            ->where('created_at', '>=', now()->subDay())
            ->count();
        $pendingExports = DB::table('failed_1c_exports')->whereNull('processed_at')->count();
        $failedJobs = DB::table('failed_jobs')->count();
        $queuedJobs = DB::table('jobs')->count();

        $lastLogDescription = $lastLogAt
            ? (int) Carbon::parse($lastLogAt)->diffInMinutes(now()) . ' мин назад'
            : 'нет записей';

        return [
            Stat::make('Последний обмен 1С', $lastLogAt ? Carbon::parse($lastLogAt)->format('d.m.Y H:i') : '—')
                ->description($lastLogDescription),
            Stat::make('Событий обмена за 24ч', $logsLast24h),
            Stat::make('Неотправленные выгрузки 1С', $pendingExports)
                ->description($pendingExports > 0 ? 'Требуется обработка' : 'Всё отправлено')
                ->color($pendingExports > 0 ? 'danger' : 'success'),
            Stat::make('Проваленные задачи', $failedJobs)
                ->color($failedJobs > 0 ? 'danger' : 'success'),
            Stat::make('Задач в очереди', $queuedJobs),
        ];
    }

    /**
     * @return array<Stat>
     */
    protected function getHealthStats(): array
    {
        $botProducts = DB::table('bot_products')->count();
        $productsActive = DB::table('products')->where('is_active', true)->count();

        $activeWithoutPrice = DB::table('products')
            ->where('is_active', true)
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('offers')
                    ->join('prices', 'prices.offer_id', '=', 'offers.id')
                    ->whereColumn('offers.product_id', 'products.id')
                    ->where('prices.price', '>', 0);
            })
            ->count();

        $withoutCategory = DB::table('products')->whereNull('category_id')->count();

        $duplicateStores = Store::select('name')
            ->groupBy('name')
            ->havingRaw('COUNT(*) > 1')
            ->count();

        return [
            Stat::make('Товары бота', $botProducts)
                ->description("Активных товаров в каталоге: {$productsActive}")
                ->color('success'),
            Stat::make('Активные товары без цены', $activeWithoutPrice)
                ->color($activeWithoutPrice > 0 ? 'warning' : 'success'),
            Stat::make('Товары без категории', $withoutCategory)
                ->color($withoutCategory > 0 ? 'warning' : 'success'),
            Stat::make('Дубли складов по имени', $duplicateStores)
                ->color($duplicateStores > 0 ? 'danger' : 'success'),
        ];
    }
}
