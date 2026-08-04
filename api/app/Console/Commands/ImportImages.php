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
        {--max-id=0 : Maximum product ID}
        {--use-slug : Build URL from slug if product url is empty}
        {--search-fallback=1 : Search gadget-bar.ru by product name when slug URL fails}';

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

    /** @var array<string, string|null> */
    private array $searchCache = [];

    private function processCategories(int $limit, int $delay): void
    {
        $query = Category::query()->whereNotNull('full_path');
        if ($limit > 0) {
            $query->limit($limit);
        }

        $total = $query->count();
        $this->info("Importing category images for {$total} categories...");
        $bar = $this->output->createProgressBar($total);

        $this->homeHtml = $this->fetch(self::BASE.'/');
        $this->loadBrandLogos();

        foreach ($query->get() as $category) {
            $this->importCategoryImage($category);
            usleep($delay * 1000);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
    }

    private function processProducts(int $limit, int $delay): void
    {
        $query = Product::query()->where(function ($q) {
            $q->whereNotNull('url')->orWhereNotNull('slug');
        });

        $minId = (int) $this->option('min-id');
        $maxId = (int) $this->option('max-id');
        if ($minId > 0) {
            $query->where('id', '>=', $minId);
        }
        if ($maxId > 0) {
            $query->where('id', '<=', $maxId);
        }

        $total = $query->count();
        $this->info("Importing product images for {$total} products...");

        if ($limit > 0) {
            $query->limit($limit);
            $this->info("Limit enabled: processing up to {$limit} products.");
        }

        $bar = $this->output->createProgressBar(min($total, $limit > 0 ? $limit : $total));

        $processed = 0;
        $withImages = 0;
        $failed = 0;

        $processOne = function (Product $product) use (&$processed, &$withImages, &$failed, $delay, $bar) {
            $result = $this->importProductImages($product);
            $processed++;
            if ($result === true) {
                $withImages++;
            } elseif ($result === false) {
                $failed++;
            }
            usleep($delay * 1000);
            $bar->advance();
        };

        if ($limit > 0) {
            foreach ($query->limit($limit)->get() as $product) {
                $processOne($product);
            }
        } else {
            $query->chunk(50, function ($products) use ($processOne) {
                foreach ($products as $product) {
                    $processOne($product);
                }
            });
        }

        $bar->finish();
        $this->newLine();
        $this->info("Processed: {$processed}, with images: {$withImages}, failed: {$failed}");
    }

    private function importCategoryImage(Category $category): void
    {
        if ($this->option('skip-existing') && $category->getFirstMedia('image')) {
            return;
        }

        $name = mb_strtolower(trim($category->name));
        $src = null;

        // Brand categories: try the original /brands/ page first.
        $path = $category->url ?? $category->full_path ?? '';
        if (str_starts_with($path, '/brands/') || str_starts_with($path, '/catalog/')) {
            $brandSlug = trim($category->slug ?: basename($path), '/');
            $src = $this->findBrandImageSrc($brandSlug) ?: $this->findBrandImageSrc($name);
        }

        // Fallback to homepage menu icons / brand logos.
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
        $nameLower = mb_strtolower(trim($name));

        // Brand logos on the homepage are 200x80 images in the brands list.
        $brandImages = $crawler->filter('img.brands-list__image')->reduce(function (Crawler $node) use ($nameLower) {
            $alt = mb_strtolower(trim($node->attr('alt') ?? ''));
            $title = mb_strtolower(trim($node->attr('title') ?? ''));

            return $alt === $nameLower || $title === $nameLower;
        });

        if ($brandImages->count() > 0) {
            return $brandImages->first()->attr('src') ?: $brandImages->first()->attr('data-src');
        }

        // Fallback to small menu icons (56x56).
        $matches = $crawler->filter('img')->reduce(function (Crawler $node) use ($nameLower) {
            $alt = mb_strtolower(trim($node->attr('alt') ?? ''));
            $title = mb_strtolower(trim($node->attr('title') ?? ''));
            $dataSrc = $node->attr('data-src') ?? $node->attr('src') ?? '';

            return ($alt === $nameLower || $title === $nameLower)
                && str_contains($dataSrc, 'resize_cache')
                && str_contains($dataSrc, '56_56_0');
        });

        if ($matches->count() === 0) {
            return null;
        }

        return $matches->first()->attr('data-src') ?: $matches->first()->attr('src');
    }

    private function importProductImages(Product $product): ?bool
    {
        if ($this->option('skip-existing') && $product->getMedia('images')->isNotEmpty()) {
            return null;
        }

        $path = $product->url;
        if (empty($path) && $this->option('use-slug') && ! empty($product->slug)) {
            $path = '/product/'.$product->slug.'/';
        }

        if (empty($path)) {
            $this->warn("Product #{$product->id} has no URL/slug");
            return false;
        }

        $url = $this->buildUrl($path);
        $html = $this->fetch($url);

        if ($html === null && $this->option('search-fallback')) {
            $searchUrl = $this->searchProductUrl($product->name);
            if ($searchUrl !== null) {
                $url = $searchUrl;
                $html = $this->fetch($url);
            }
        }

        if ($html === null) {
            $this->warn("Product #{$product->id} failed to fetch {$url}");
            return false;
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
            $this->warn("Product #{$product->id} no images found at {$url}");
            return false;
        }

        // Replace existing media for this product to avoid duplicates.
        $product->clearMediaCollection('images');

        $attached = 0;
        foreach ($srcs as $src) {
            if ($this->attach($product, $src, 'images')) {
                $attached++;
            }
        }

        return $attached > 0;
    }

    private function fetch(string $url): ?string
    {
        try {
            $response = Http::timeout((int) $this->option('timeout'))
                ->withUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36')
                ->withOptions(['verify' => false])
                ->get($url);

            if (! $response->successful()) {
                $this->warn("Failed to fetch {$url}: HTTP {$response->status()}");
                return null;
            }

            return $response->body();
        } catch (Throwable $e) {
            $this->warn("Failed to fetch {$url}: [".get_class($e)."] {$e->getMessage()}");
            return null;
        }
    }

    private function buildUrl(string $path): string
    {
        return self::BASE.rtrim($path, '/').'/';
    }

    private function searchProductUrl(string $name): ?string
    {
        $name = trim($name);
        if (isset($this->searchCache[$name])) {
            return $this->searchCache[$name];
        }

        $query = substr($name, 0, 100);
        $url = self::BASE.'/search/?q='.urlencode($query);
        $html = $this->fetch($url);

        if ($html === null) {
            $this->searchCache[$name] = null;
            return null;
        }

        $crawler = new Crawler($html);
        $links = $crawler->filter('a[href^="/product/"]');

        if ($links->count() === 0) {
            $this->searchCache[$name] = null;
            return null;
        }

        $href = $links->first()->attr('href');
        $resolved = $href ? $this->buildUrl($href) : null;
        $this->searchCache[$name] = $resolved;

        return $resolved;
    }

    private function resolveSrc(string $src): string
    {
        if (str_starts_with($src, 'http')) {
            return $src;
        }

        return self::BASE.$src;
    }

    private function attach($model, string $url, string $collection): bool
    {
        $tmpPath = null;
        try {
            $response = Http::timeout((int) $this->option('timeout'))
                ->withUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36')
                ->withOptions(['verify' => false])
                ->get($url);

            if (! $response->successful()) {
                $this->warn("Failed to download {$url}: HTTP {$response->status()}");
                return false;
            }

            $extension = pathinfo(parse_url($url, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION) ?: 'jpg';
            $tmpPath = tempnam(sys_get_temp_dir(), 'img_').'.'.$extension;
            file_put_contents($tmpPath, $response->body());

            $model->addMedia($tmpPath)
                ->toMediaCollection($collection);

            return true;
        } catch (Throwable $e) {
            $this->warn("Failed to attach {$url}: {$e->getMessage()}");
            return false;
        } finally {
            if ($tmpPath && file_exists($tmpPath)) {
                @unlink($tmpPath);
            }
        }
    }
}
