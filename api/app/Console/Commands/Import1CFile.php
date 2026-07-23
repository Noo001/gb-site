<?php

namespace App\Console\Commands;

use App\Jobs\Apply1CStagingData;
use App\Services\OneCStagingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Throwable;

class Import1CFile extends Command
{
    protected $signature = '1c:import-file
                            {path : Путь к JSON-файлу выгрузки 1С}
                            {--sync : Применить данные синхронно (по умолчанию — через очередь)}
                            {--chunk=100 : Размер пакета для записи в staging}';

    protected $description = 'Импорт каталога из JSON-файла 1С (staging -> apply). Обновляет существующие записи, не удаляет отсутствующие в файле.';

    public function handle(OneCStagingService $stagingService): int
    {
        $path = $this->argument('path');

        if (! File::exists($path)) {
            $this->error("Файл не найден: {$path}");
            return self::FAILURE;
        }

        $contents = File::get($path);
        $data = json_decode($contents, true);

        if (! is_array($data)) {
            $this->error('Файл должен содержать валидный JSON-объект.');
            return self::FAILURE;
        }

        try {
            $this->validateStructure($data);
        } catch (ValidationException $e) {
            foreach ($e->errors() as $field => $errors) {
                foreach ($errors as $message) {
                    $this->error("{$field}: {$message}");
                }
            }
            return self::FAILURE;
        }

        $this->info('Начинаю импорт из файла: ' . $path);
        $this->info('Категорий: ' . count($data['categories'] ?? []));
        $this->info('Товаров: ' . count($data['products'] ?? []));

        try {
            $batchId = $this->stageData($stagingService, $data);
        } catch (Throwable $e) {
            $this->error('Ошибка при записи в staging: ' . $e->getMessage());
            return self::FAILURE;
        }

        $this->info('Staging batch_id: ' . $batchId);

        if ($this->option('sync')) {
            $this->info('Применяю синхронно...');
            $result = $this->applySync($batchId);
            $this->info("Обработано: {$result['processed']}, ошибок: {$result['failed']}");

            if (! empty($result['errors'])) {
                foreach (array_slice($result['errors'], 0, 20) as $error) {
                    $this->error("[{$error['type']} {$error['external_id']}] {$error['error']}");
                }
            }

            return $result['failed'] === 0 ? self::SUCCESS : self::FAILURE;
        }

        Apply1CStagingData::dispatch($batchId);
        $this->info('Данные поставлены в очередь. Статус можно посмотреть через integration_logs / 1c_* таблицы.');

        return self::SUCCESS;
    }

    private function validateStructure(array $data): void
    {
        Validator::make($data, [
            'categories' => ['nullable', 'array'],
            'categories.*.external_id' => ['required', 'string', 'max:255'],
            'categories.*.name' => ['required', 'string', 'max:255'],
            'products' => ['nullable', 'array'],
            'products.*.external_id' => ['required', 'string', 'max:255'],
            'products.*.name' => ['required', 'string', 'max:1000'],
        ])->validate();
    }

    private function stageData(OneCStagingService $stagingService, array $data): string
    {
        return $stagingService->store($data);
    }

    private function applySync(string $batchId): array
    {
        $records = collect()
            ->merge(\App\Models\OneCCategory::where('batch_id', $batchId)->unprocessed()->get())
            ->merge(\App\Models\OneCProduct::where('batch_id', $batchId)->unprocessed()->get())
            ->merge(\App\Models\OneCOffer::where('batch_id', $batchId)->unprocessed()->get())
            ->merge(\App\Models\OneCPrice::where('batch_id', $batchId)->unprocessed()->get())
            ->merge(\App\Models\OneCStock::where('batch_id', $batchId)->unprocessed()->get())
            ->all();

        return app(\App\Services\OneCSyncService::class)->apply($records);
    }
}
