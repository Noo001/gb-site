<?php

namespace App\Console\Commands;

use App\Models\Offer;
use App\Models\OneCStocksSnapshot;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Store;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class Compare1CStocks extends Command
{
    protected $signature = '1c:compare-stocks
        {--batch-id= : ID одной пачки snapshot для сравнения}
        {--window-minutes=30 : Максимальный разрыв между батчами одной выгрузки, в минутах}
        {--clear : Очистить использованные snapshot после сравнения}';

    protected $description = 'Сравнивает snapshot остатков из 1С с текущими остатками на сайте';

    public function handle(): int
    {
        $batchId = $this->option('batch-id');
        $windowMinutes = (int) $this->option('window-minutes');
        if ($windowMinutes <= 0) {
            $windowMinutes = 30;
        }

        if ($batchId) {
            $batchIds = [$batchId];
        } else {
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
                $this->error('Snapshot не найден. Сначала выполните выгрузку из 1С.');
                return self::FAILURE;
            }

            // Берём все батчи, входящие в последнюю выгрузку:
            // последовательные батчи с разрывом не более window-minutes.
            $batchIds = [];
            $previous = null;
            foreach ($batches as $batch) {
                if ($previous && $batch['last_seen']->diffInMinutes($previous) > $windowMinutes) {
                    break;
                }
                $batchIds[] = $batch['batch_id'];
                $previous = $batch['last_seen'];
            }
        }

        $snapshots = OneCStocksSnapshot::query()
            ->whereIn('batch_id', $batchIds)
            ->get();

        $this->info('Сравниваем snapshot: ' . count($batchIds) . ' batch(ей), ' . $snapshots->count() . ' записей');
        if (count($batchIds) > 1) {
            $this->line('  batch_id: ' . implode(', ', array_slice($batchIds, 0, 5)) . (count($batchIds) > 5 ? ' ...' : ''));
        }

        // Собираем 1С-остатки в мапу: offer_external_id + store_external_id => quantity
        $oneCMap = [];
        foreach ($snapshots as $s) {
            $key = $s->offer_external_id . '|' . ($s->store_external_id ?? '');
            $oneCMap[$key] = ($oneCMap[$key] ?? 0) + (float) $s->quantity;
        }

        // Собираем остатки сайта в мапу
        $siteMap = [];
        $stocks = Stock::with(['offer', 'store'])->get();
        foreach ($stocks as $stock) {
            $offerExternalId = $stock->offer?->external_id;
            $storeExternalId = $stock->store?->external_id;
            if (! $offerExternalId) {
                continue;
            }
            $key = $offerExternalId . '|' . ($storeExternalId ?? '');
            $siteMap[$key] = ($siteMap[$key] ?? 0) + (float) $stock->quantity;
        }

        // Сравнение
        $report = [
            'only_in_1c' => [],
            'only_on_site' => [],
            'mismatch' => [],
        ];

        foreach ($oneCMap as $key => $qty1c) {
            if (! isset($siteMap[$key])) {
                $report['only_in_1c'][$key] = $qty1c;
            } elseif (abs($siteMap[$key] - $qty1c) > 0.01) {
                $report['mismatch'][$key] = [
                    '1c' => $qty1c,
                    'site' => $siteMap[$key],
                    'diff' => $siteMap[$key] - $qty1c,
                ];
            }
        }

        foreach ($siteMap as $key => $qtySite) {
            if (! isset($oneCMap[$key])) {
                $report['only_on_site'][$key] = $qtySite;
            }
        }

        // Вывод
        $this->newLine();
        $this->info('Результат сравнения');
        $this->table(['Показатель', 'Количество'], [
            ['Всего в 1С snapshot', count($oneCMap)],
            ['Всего на сайте', count($siteMap)],
            ['Только в 1С', count($report['only_in_1c'])],
            ['Только на сайте', count($report['only_on_site'])],
            ['Расходятся по количеству', count($report['mismatch'])],
        ]);

        if (! empty($report['only_in_1c'])) {
            $this->warn('Товары/склады есть в 1С, но нет на сайте:');
            foreach (array_slice($report['only_in_1c'], 0, 10, true) as $key => $qty) {
                [$offerId, $storeId] = explode('|', $key);
                $this->line("  {$offerId} | {$storeId} = {$qty}");
            }
            if (count($report['only_in_1c']) > 10) {
                $this->line('  ... и ещё ' . (count($report['only_in_1c']) - 10));
            }
        }

        if (! empty($report['only_on_site'])) {
            $this->warn('Товары/склады есть на сайте, но нет в 1С:');
            foreach (array_slice($report['only_on_site'], 0, 10, true) as $key => $qty) {
                [$offerId, $storeId] = explode('|', $key);
                $this->line("  {$offerId} | {$storeId} = {$qty}");
            }
            if (count($report['only_on_site']) > 10) {
                $this->line('  ... и ещё ' . (count($report['only_on_site']) - 10));
            }
        }

        if (! empty($report['mismatch'])) {
            $this->warn('Расхождения по количеству:');
            $sorted = collect($report['mismatch'])->sortByDesc(fn ($v) => abs($v['diff']))->take(10);
            foreach ($sorted as $key => $v) {
                [$offerId, $storeId] = explode('|', $key);
                $this->line("  {$offerId} | {$storeId}: 1С={$v['1c']}, сайт={$v['site']}, diff={$v['diff']}");
            }
            if (count($report['mismatch']) > 10) {
                $this->line('  ... и ещё ' . (count($report['mismatch']) - 10));
            }
        }

        if ($this->option('clear')) {
            OneCStocksSnapshot::whereIn('batch_id', $batchIds)->delete();
            $this->info('Snapshot очищен: ' . count($batchIds) . ' batch(ей).');
        }

        return self::SUCCESS;
    }
}
