<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Page;
use App\Models\Product;
use App\Models\SeoMetadata;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ImportSeo extends Command
{
    protected $signature = 'import:seo
                            {--type=all : Entity types to parse: categories,products,pages,all}
                            {--limit=0 : Max pages per type (0 = unlimited)}
                            {--delay=500 : Delay between requests in milliseconds}';

    protected $description = 'Parse SEO metadata from old site pages';

    private string $baseUrl = 'https://gadget-bar.ru';

    public function handle(): int
    {
        $types = array_filter(explode(',', $this->option('type')));
        if (in_array('all', $types, true)) {
            $types = ['categories', 'products', 'pages'];
        }

        $limit = (int) $this->option('limit');
        $delay = (int) $this->option('delay');

        foreach ($types as $type) {
            match ($type) {
                'categories' => $this->processCategories($limit, $delay),
                'products' => $this->processProducts($limit, $delay),
                'pages' => $this->processPages($limit, $delay),
                default => $this->warn("Unknown type: {$type}"),
            };
        }

        $this->info('Done.');

        return self::SUCCESS;
    }

    private function processCategories(int $limit, int $delay): void
    {
        $query = Category::query()->whereNotNull('url')->orderBy('id');
        if ($limit > 0) {
            $query->limit($limit);
        }

        $items = $query->get();
        $bar = $this->output->createProgressBar($items->count());
        foreach ($items as $category) {
            $this->parseAndSave($category, $category->url);
            usleep($delay * 1000);
            $bar->advance();
        }
        $bar->finish();
        $this->newLine();
    }

    private function processProducts(int $limit, int $delay): void
    {
        $query = Product::query()->whereNotNull('url')->orderBy('id');
        if ($limit > 0) {
            $query->limit($limit);
        }

        $items = $query->get();
        $bar = $this->output->createProgressBar($items->count());
        foreach ($items as $product) {
            $this->parseAndSave($product, $product->url);
            usleep($delay * 1000);
            $bar->advance();
        }
        $bar->finish();
        $this->newLine();
    }

    private function processPages(int $limit, int $delay): void
    {
        $query = Page::query()->whereNotNull('url')->orderBy('id');
        if ($limit > 0) {
            $query->limit($limit);
        }

        $items = $query->get();
        $bar = $this->output->createProgressBar($items->count());
        foreach ($items as $page) {
            $this->parseAndSave($page, $page->url);
            usleep($delay * 1000);
            $bar->advance();
        }
        $bar->finish();
        $this->newLine();
    }

    private function parseAndSave(Category|Product|Page $entity, string $path): void
    {
        $url = $this->baseUrl.rtrim($path, '/').'/';
        try {
            $response = Http::timeout(30)->withUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36')->get($url);
            if (! $response->successful()) {
                return;
            }
        } catch (\Throwable $e) {
            $this->warn("Failed {$url}: {$e->getMessage()}");

            return;
        }

        $html = $response->body();
        $meta = $this->extractMeta($html, $url);

        SeoMetadata::updateOrCreate(
            ['entity_type' => $entity::class, 'entity_id' => $entity->id],
            [
                'url' => $path,
                'title' => $meta['title'],
                'description' => $meta['description'],
                'keywords' => $meta['keywords'],
                'h1' => $meta['h1'],
                'og_title' => $meta['og_title'],
                'og_description' => $meta['og_description'],
                'og_image' => $meta['og_image'],
                'canonical' => $meta['canonical'],
                'json_ld' => $meta['json_ld'],
            ]
        );

        // Try to assign product category from breadcrumbs
        if ($entity instanceof Product && ! empty($meta['breadcrumbs'])) {
            $this->assignProductCategory($entity, $meta['breadcrumbs']);
        }

        // Update entity name from H1 if it looks better than slug title
        if ($meta['h1'] && ! str_contains($meta['h1'], '404')) {
            if ($entity->name !== $meta['h1']) {
                $entity->update(['name' => $meta['h1']]);
            }
        }
    }

    private function extractMeta(string $html, string $url): array
    {
        $doc = new \DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="UTF-8"?>'.$html);
        libxml_clear_errors();
        $xpath = new \DOMXPath($doc);

        $get = function (string $query) use ($xpath): ?string {
            $node = $xpath->query($query)->item(0);

            return $node ? trim($node->nodeValue) : null;
        };

        $getAttr = function (string $query, string $attr) use ($xpath): ?string {
            $node = $xpath->query($query)->item(0);

            return $node ? $node->getAttribute($attr) : null;
        };

        $breadcrumbs = [];
        $nodes = $xpath->query('//div[contains(@class,"breadcrumbs__item") and @itemprop="itemListElement"]');
        foreach ($nodes as $node) {
            $nameNode = $xpath->query('.//span[@itemprop="name"]', $node)->item(0);
            $linkNode = $xpath->query('.//a[@itemprop="item"]', $node)->item(0);
            if ($nameNode) {
                $breadcrumbs[] = [
                    'name' => trim($nameNode->nodeValue),
                    'url' => $linkNode ? $linkNode->getAttribute('href') : null,
                ];
            }
        }

        $jsonLd = [];
        $ldNodes = $xpath->query('//script[@type="application/ld+json"]');
        foreach ($ldNodes as $node) {
            $json = trim($node->nodeValue);
            if ($json) {
                try {
                    $jsonLd[] = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
                } catch (\Throwable) {
                    // ignore malformed JSON-LD
                }
            }
        }

        return [
            'title' => $get('//title'),
            'description' => $getAttr('//meta[@name="description"]', 'content'),
            'keywords' => $getAttr('//meta[@name="keywords"]', 'content'),
            'h1' => $get('//h1'),
            'canonical' => $getAttr('//link[@rel="canonical"]', 'href'),
            'og_title' => $getAttr('//meta[@property="og:title"]', 'content'),
            'og_description' => $getAttr('//meta[@property="og:description"]', 'content'),
            'og_image' => $getAttr('//meta[@property="og:image"]', 'content'),
            'json_ld' => $jsonLd ?: null,
            'breadcrumbs' => $breadcrumbs,
        ];
    }

    private function assignProductCategory(Product $product, array $breadcrumbs): void
    {
        // Breadcrumbs example: Главная > Каталог > Apple > iPhone > Product name
        $catalogIndex = null;
        foreach ($breadcrumbs as $i => $crumb) {
            if (($crumb['name'] === 'Каталог' || $crumb['url'] === '/catalog/') && isset($breadcrumbs[$i + 1])) {
                $catalogIndex = $i + 1;
                break;
            }
        }

        if (! $catalogIndex || ! isset($breadcrumbs[$catalogIndex])) {
            return;
        }

        // Use the deepest catalog category before the product name
        $categoryUrl = null;
        for ($i = $catalogIndex; $i < count($breadcrumbs) - 1; $i++) {
            $url = $breadcrumbs[$i]['url'];
            if ($url && str_starts_with($url, '/catalog/')) {
                $categoryUrl = $url;
            }
        }

        if ($categoryUrl) {
            $category = Category::where('url', $categoryUrl)->first();
            if ($category) {
                $product->update(['category_id' => $category->id]);
            }
        }
    }
}
