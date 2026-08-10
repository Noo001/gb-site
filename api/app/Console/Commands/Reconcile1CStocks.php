<?php

namespace App\Console\Commands;

use App\Jobs\RebuildBotIndexJob;
use App\Models\Offer;
use App\Models\OneCStocksSnapshot;
use App\Models\Stock;
use App\Models\Store;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class Reconcile1CStocks extends Command
{
    protected $signature = '1c:reconcile-stocks
        {--window-minutes=30 : Максимальный разрыв между батчами одной выгрузки, в минутах}
        {--dry-run : Показать отчёт без изменений}
        {--clear : Очистить использованный snapshot после реконциляции}';

    protected $description = 'Приводит остатки на сайте в соответствие со snapshot из 1С';

    public function handle(): int
    {
        $windowMinutes = (int) $this->option('window-minutes');
        if ($windowMinutes <= 0) {
            $windowMinutes = 30;
        }

        $batches = OneCStocksSnapshot::query()
            ->select('batch_id')
            ->selectRaw('MAX(created_at) as last_seen')
            ->groupBy('batch_id')
            ->orderByDesc('last_seen')
            ->get()
            ->map(fn ($row) => [
                'batch_id' => $row->batch_id,
                'last_seen' => Carbon::parse($row->last_seen),
            ]);

        if ($batches->isEmpty()) {
            $this->error('Snapshot не найден. Сначала выполните полную выгрузку из 1С.');
            return self::FAILURE;
        }

        $batchIds = [];
        $previous = null;
        foreach ($batches as $batch) {
            if ($previous && $batch['last_seen']->diffInMinutes($previous) > $windowMinutes) {
                break;
            }
            $batchIds[] = $batch['batch_id'];
            $previous = $batch['last_seen'];
        }

        $snapshotRows = OneCStocksSnapshot::query()
            ->whereIn('batch_id', $batchIds)
            ->get();

        $snapshot = [];
        $storeNames = [];
        foreach ($snapshotRows as $s) {
            $key = $s->offer_external_id . '|' . ($s->store_external_id ?? '');
            $snapshot[$key] = ($snapshot[$key] ?? 0) + (float) $s->quantity;

            if ($s->store_external_id && $s->store_name && ! isset($storeNames[$s->store_external_id])) {
                $storeNames[$s->store_external_id] = $s->store_name;
            }
        }

        $siteStocks = Stock::with(['offer', 'store'])->get();
        $siteMap = [];
        $siteRowsByKey = [];
        foreach ($siteStocks as $stock) {
            $offerExternalId = $stock->offer?->external_id;
            $storeExternalId = $stock->store?->external_id;
            if (! $offerExternalId) {
                continue;
            }
            $key = $offerExternalId . '|' . ($storeExternalId ?? '');
            $siteMap[$key] = ($siteMap[$key] ?? 0) + (float) $stock->quantity;
            $siteRowsByKey[$key][] = $stock;
        }

        $toCreate = [];
        $toUpdate = [];
        $toDelete = [];

        foreach ($snapshot as $key => $qty) {
            if (! isset($siteMap[$key])) {
                $toCreate[] = $key;
            } elseif (abs($siteMap[$key] - $qty) > 0.01) {
                $toUpdate[] = $key;
            }
        }

        foreach ($siteMap as $key => $qty) {
            if (! isset($snapshot[$key])) {
                $toDelete[] = $key;
            }
        }

        $dryRun = $this->option('dry-run');

        $this->newLine();
        $this->info('Реконциляция остатков с 1С');
        $this->table(['Показатель', 'Количество'], [
            ['Батчей в окне', count($batchIds)],
            ['Ключей в snapshot', count($snapshot)],
            ['Ключей на сайте', count($siteMap)],
            ['Будет создано', count($toCreate)],
            ['Будет обновлено', count($toUpdate)],
            ['Будет удалено', count($toDelete)],
        ]);

        if ($dryRun) {
            $this->warn('Режим dry-run: изменения не применены.');
            return self::SUCCESS;
        }

        $this->info('Применение изменений...');

        $created = 0;
        $updated = 0;
        $deleted = 0;
        $skipped = 0;

        foreach (array_merge($toCreate, $toUpdate) as $key) {
            [$offerExternalId, $storeExternalId] = explode('|', $key);
            $offer = Offer::where('external_id', $offerExternalId)->first();

            if (! $offer) {
                $this->warn("Пропущен (offer не найден): {$key}");
                $skipped++;
                continue;
            }

            $store = null;
            if ($storeExternalId !== '') {
                $storeName = $storeNames[$storeExternalId] ?? $storeExternalId;
                $store = Store::firstOrCreate(
                    ['external_id' => $storeExternalId],
                    ['name' => $storeName, 'is_active' => true, 'sort' => 0]
                );

                if ($store->name !== $storeName && $storeName !== $storeExternalId) {
                    $store->update(['name' => $storeName]);
                }
            }

            Stock::updateOrCreate(
                [
                    'offer_id' => $offer->id,
                    'store_id' => $store?->id,
                ],
                [
                    'quantity' => $snapshot[$key],
                ]
            );

            if (in_array($key, $toCreate, true)) {
                $created++;
            } else {
                $updated++;
            }
        }

        foreach ($toDelete as $key) {
            foreach ($siteRowsByKey[$key] ?? [] as $stock) {
                $stock->delete();
                $deleted++;
            }
        }

        RebuildBotIndexJob::dispatch();

        $this->info("Создано: {$created}, обновлено: {$updated}, удалено: {$deleted}, пропущено: {$skipped}.");

        if ($this->option('clear')) {
            OneCStocksSnapshot::whereIn('batch_id', $batchIds)->delete();
            $this->info('Snapshot очищен: ' . count($batchIds) . ' batch(ей).');
        }

        return self::SUCCESS;
    }
}
