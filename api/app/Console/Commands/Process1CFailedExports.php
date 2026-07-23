<?php

namespace App\Console\Commands;

use App\Models\Failed1CExport;
use App\Services\OneCStagingService;
use Illuminate\Console\Command;
use Throwable;

class Process1CFailedExports extends Command
{
    protected $signature = '1c:process-queue
                            {--limit=50 : Maximum number of records to process per run}';

    protected $description = 'Process failed 1C exports stored in the database queue';

    public function handle(OneCStagingService $staging): int
    {
        $limit = (int) $this->option('limit');
        $records = Failed1CExport::pending()->orderBy('created_at')->limit($limit)->get();

        if ($records->isEmpty()) {
            $this->info('No pending 1C exports.');
            return self::SUCCESS;
        }

        $processed = 0;
        $failed = 0;

        foreach ($records as $record) {
            try {
                if ($record->endpoint === '/api/1c/products') {
                    $staging->stageAndApplyProduct($record->payload);
                } elseif ($record->endpoint === '/api/1c/prices') {
                    $staging->stageAndApplyPrice($record->payload);
                } elseif ($record->endpoint === '/api/1c/stocks') {
                    $staging->stageAndApplyStock($record->payload);
                } elseif ($record->endpoint === '/api/1c/categories') {
                    $staging->stageAndApplyCategory($record->payload);
                } else {
                    $staging->stageAndApplyProduct($record->payload);
                }

                $record->markProcessed();
                $processed++;
            } catch (Throwable $e) {
                $record->markFailed($e->getMessage());
                $failed++;
                $this->error("Failed export #{$record->id}: {$e->getMessage()}");
            }
        }

        $this->info("Processed: {$processed}, Failed: {$failed}");
        return self::SUCCESS;
    }
}
