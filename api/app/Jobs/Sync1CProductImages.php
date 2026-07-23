<?php

namespace App\Jobs;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class Sync1CProductImages implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $productId,
        public array $urls
    ) {
    }

    public function handle(): void
    {
        $product = Product::find($this->productId);

        if (! $product) {
            return;
        }

        foreach ($this->urls as $url) {
            try {
                $product
                    ->addMediaFromUrl($url)
                    ->toMediaCollection('images');
            } catch (\Throwable $e) {
                Log::warning('1C image sync failed', [
                    'product_id' => $this->productId,
                    'url' => $url,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
