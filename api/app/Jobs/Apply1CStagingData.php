<?php

namespace App\Jobs;

use App\Jobs\RebuildBotIndexJob;
use App\Models\OneCCategory;
use App\Models\OneCOffer;
use App\Models\OneCPrice;
use App\Models\OneCProduct;
use App\Models\OneCStock;
use App\Services\OneCSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class Apply1CStagingData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public function __construct(
        public ?string $batchId = null,
        private ?OneCSyncService $syncService = null
    ) {
        $this->syncService ??= app(OneCSyncService::class);
    }

    public function handle(): void
    {
        $records = collect()
            ->merge(OneCCategory::unprocessed()->when($this->batchId, fn ($q) => $q->where('batch_id', $this->batchId))->get())
            ->merge(OneCProduct::unprocessed()->when($this->batchId, fn ($q) => $q->where('batch_id', $this->batchId))->get())
            ->merge(OneCOffer::unprocessed()->when($this->batchId, fn ($q) => $q->where('batch_id', $this->batchId))->get())
            ->merge(OneCPrice::unprocessed()->when($this->batchId, fn ($q) => $q->where('batch_id', $this->batchId))->get())
            ->merge(OneCStock::unprocessed()->when($this->batchId, fn ($q) => $q->where('batch_id', $this->batchId))->get());

        $result = $this->syncService->apply($records->all());

        if ($result['failed'] === 0) {
            RebuildBotIndexJob::dispatch();
        }
    }
}
