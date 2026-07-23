<?php

namespace App\Console\Commands;

use App\Services\BotIndexService;
use Illuminate\Console\Command;

class RebuildBotIndex extends Command
{
    protected $signature = 'bot:rebuild-index';

    protected $description = 'Rebuild the read-only bot_products search index from site data.';

    public function handle(BotIndexService $service): int
    {
        $this->info('Rebuilding bot product index...');

        $result = $service->rebuild();

        $this->info("Indexed {$result['created']} offers in {$result['duration_ms']} ms.");

        return self::SUCCESS;
    }
}
