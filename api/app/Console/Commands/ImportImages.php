<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\DomCrawler\Crawler;
use Throwable;

class ImportImages extends Command
{
    protected $signature = 'import:images
        {--type=products : categories|products|all}
        {--limit=0 : Max items per type}
        {--delay=200 : Delay between requests in ms}
        {--skip-existing=1 : Skip entities that already have media}
        {--timeout=30 : HTTP timeout}
        {--min-id=0 : Minimum product ID}
        {--max-id=0 : Maximum product ID}';

    protected $description = 'Download product/category images from old site into Spatie media library';

    private const BASE = 'https://gadget-bar.ru';

    public function handle(): int
    {
        $type = $this->option('type');
        $limit = (int) $this->option('limit');
        $delay = (int) $this->option('delay');

        $types = match ($type) {
            'all' => ['categories', 'products'],
            default => [$type],
        };

        foreach ($types as $t) {
            match ($t) {
                'categories' => $this->processCategories($limit, $delay),
                'products' => $this->processProducts($limit, $delay),
                default => $this->error("Unknown type: {$t}"),
            };
        }

        return self::SUCCESS;
    }

    private ?string $homeHtml = null;

    private ?array $brandLogos = null;

    private function processCategories(int $limit, int $delay): void
    {
        $query = Category::query()->whereNotNull('url');
        if ($limit > 0) {
            $query->limit($limit);
        }

        $total = $query->count();
        $this->info("Importing category images for {$total} categories...");
        $bar = $this->output->createProgressBar($total);

        $this->homeHtml = $this->fetch(self::BASE.'/');
        $this->loadBrandLogos();

        foreach ($query->cursor() as $category) {
            $this->importCategoryImage($category);
            usleep($delay * 1000);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
    }

    private function processProducts(int $limit, int $delay): void
    {
        $query = Product::query()->whereNotNull('url');

        $minId = (int) $this->option('min-id');
        $maxId = (int) $this->option('max-id');
        if ($minId > 0) {
            $query->where('id', '>=', $minId);
        }
        if ($maxId > 0) {
            $query->where('id', '<=', $maxId);
        }

        if ($limit > 0) {
            $query->limit($limit);
        }

        $total = $query->count();
        $this->info("Importing product images for {$total} products...");
        $bar = $this->output->createProgressBar($total);

        foreach ($query->cursor() as $product) {
            $this->importProductImages($product);
            usleep($delay * 1000);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
    }

    private function importCategoryImage(Category $category): void
    {
        if ($this->option('skip-existing') && $category->getFirstMedia('image')) {
            return;
        }

        $name = mb_strtolower(trim($category->name));
        $src = null;

        // Brand categories: try the original /brands/ page first.
        if (str_starts_with($category->url ?? '', '/brands/')) {
            $src = $this->findBrandImageSrc($name);
        }

        // Fallback to homepage menu icons.
        if (! $src) {
            $html = $this->homeHtml ?? $this->fetch(self::BASE.'/');
            $src = $this->findCategoryImageSrc($html, $name);
        }

        if (! $src) {
            return;
        }

        $src = $this->resolveSrc($src);
        $this->attach($category, $src, 'image');
    }

    private function loadBrandLogos(): void
    {
        $this->brandLogos = [];

        for ($page = 1; $page <= 20; $page++) {
            $url = self::BASE.'/brands/'.($page > 1 ? '?PAGEN_1='.$page : '');
            $html = $this->fetch($url);

            if ($html === null) {
                break;
            }

            $crawler = new Crawler($html);
            $wrappers = $crawler->filter('.brand-list-inner__wrapper');

            if ($wrappers->count() === 0) {
                break;
            }

            $wrappers->each(function (Crawler $node) {
                $titleNode = $node->filter('a.brand-list-inner__image')->first();
                $bgNode = $node->filter('span.brand-list-inner__image-bg')->first();

                if ($titleNode->count() === 0 || $bgNode->count() === 0) {
                    return;
                }

                $name = mb_strtolower(trim($titleNode->attr('title') ?? ''));
                $bg = $bgNode->attr('data-bg') ?? '';

                if ($name && $bg) {
                    $this->brandLogos[$name] = $this->resolveSrc($bg);
                }
            });
        }
    }

    private function findBrandImageSrc(string $name): ?string
    {
        return $this->brandLogos[$name] ?? null;
    }

    private function findCategoryImageSrc(?string $html, string $name): ?string
    {
        if ($html === null) {
            return null;
        }

        $crawler = new Crawler($html);

        $matches = $crawler->filter('img')->reduce(function (Crawler $node) use ($name) {
            $alt = mb_strtolower(trim($node->attr('alt') ?? ''));
            $title = mb_strtolower(trim($node->attr('title') ?? ''));
            $dataSrc = $node->attr('data-src') ?? $node->attr('src') ?? '';

            return ($alt === $name || $title === $name)
                && str_contains($dataSrc, 'resize_cache')
                && str_contains($dataSrc, '56_56_0');
        });

        if ($matches->count() === 0) {
            return null;
        }

        return $matches->first()->attr('data-src');
    }

    private function importProductImages(Product $product): void
    {
        if ($this->option('skip-existing') && $product->getMedia('images')->isNotEmpty()) {
            return;
        }

        $url = $this->buildUrl($product->url);
        $html = $this->fetch($url);
        if ($html === null) {
            return;
        }

        $crawler = new Crawler($html);

        // Primary gallery: detail big pictures.
        $nodes = $crawler->filter('img.detail-gallery-big__picture');
        if ($nodes->count() === 0) {
            $nodes = $crawler->filter('img.gallery__picture');
        }

        $srcs = [];
        $nodes->each(function (Crawler $node) use (&$srcs) {
            $src = $node->attr('data-src') ?: $node->attr('src');
            if ($src && str_starts_with($src, '/upload/')) {
                $srcs[] = $this->resolveSrc($src);
            }
        });

        $srcs = array_values(array_unique($srcs));

        if (empty($srcs)) {
            return;
        }

        // Replace existing media for this product to avoid duplicates.
        $product->clearMediaCollection('images');

        foreach ($srcs as $src) {
            $this->attach($product, $src, 'images');
        }
    }

    private function fetch(string $url): ?string
    {
        try {
            $response = Http::timeout((int) $this->option('timeout'))
                ->withUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36')
                ->get($url);

            if (! $response->successful()) {
                return null;
            }

            return $response->body();
        } catch (Throwable $e) {
            $this->warn("Failed to fetch {$url}: {$e->getMessage()}");
            return null;
        }
    }

    private function buildUrl(string $path): string
    {
        return self::BASE.rtrim($path, '/').'/';
    }

    private function resolveSrc(string $src): string
    {
        if (str_starts_with($src, 'http')) {
            return $src;
        }

        return self::BASE.$src;
    }

    private function attach($model, string $url, string $collection): void
    {
        try {
            $model->addMediaFromUrl($url)
                ->toMediaCollection($collection);
        } catch (Throwable $e) {
            $this->warn("Failed to attach {$url}: {$e->getMessage()}");
        }
    }
}
