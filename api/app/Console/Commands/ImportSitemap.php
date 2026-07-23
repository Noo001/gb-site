<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Page;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ImportSitemap extends Command
{
    protected $signature = 'import:sitemap {url=https://gadget-bar.ru/sitemap.xml}';

    protected $description = 'Import URLs from sitemap into categories, products and pages';

    public function handle(): int
    {
        $indexUrl = $this->argument('url');
        $this->info("Loading sitemap index: {$indexUrl}");

        $indexXml = $this->fetchXml($indexUrl);
        if (! $indexXml) {
            $this->error('Unable to load sitemap index.');

            return self::FAILURE;
        }

        $sitemapUrls = [];
        foreach ($indexXml->sitemap as $sitemap) {
            $sitemapUrls[] = (string) $sitemap->loc;
        }

        $categoryUrls = [];
        $productUrls = [];
        $pageUrls = [];

        foreach ($sitemapUrls as $sitemapUrl) {
            $this->info("Loading {$sitemapUrl}");
            $xml = $this->fetchXml($sitemapUrl);
            if (! $xml) {
                continue;
            }

            foreach ($xml->url as $urlNode) {
                $loc = (string) $urlNode->loc;
                $path = parse_url($loc, PHP_URL_PATH);

                if (str_starts_with($path, '/catalog/')) {
                    if (str_ends_with($path, '/')) {
                        $categoryUrls[] = $loc;
                    }
                } elseif (str_starts_with($path, '/product/')) {
                    $productUrls[] = $loc;
                } elseif (str_starts_with($path, '/brands/')) {
                    $categoryUrls[] = $loc;
                } else {
                    $pageUrls[] = $loc;
                }
            }
        }

        $this->info('Found: '.count($categoryUrls).' categories, '.count($productUrls).' products, '.count($pageUrls).' pages');

        $this->importCategories($categoryUrls);
        $this->importProducts($productUrls);
        $this->importPages($pageUrls);

        $this->info('Done.');

        return self::SUCCESS;
    }

    private function fetchXml(string $url): ?\SimpleXMLElement
    {
        try {
            $response = Http::timeout(60)->withUserAgent('Mozilla/5.0')->get($url);
            if (! $response->successful()) {
                return null;
            }

            return simplexml_load_string($response->body());
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return null;
        }
    }

    private function importCategories(array $urls): void
    {
        // Sort by path depth so parents are created first
        usort($urls, fn (string $a, string $b) => substr_count(parse_url($a, PHP_URL_PATH), '/') <=> substr_count(parse_url($b, PHP_URL_PATH), '/'));

        $bar = $this->output->createProgressBar(count($urls));
        foreach ($urls as $url) {
            $path = parse_url($url, PHP_URL_PATH);
            $segments = array_filter(explode('/', trim($path, '/')));

            // /brands/apple/ -> handle as category under "Бренды" virtual parent
            $isBrand = $segments[0] === 'brands';
            if ($isBrand) {
                array_shift($segments);
            }

            $slug = end($segments) ?: null;
            if (! $slug) {
                continue;
            }

            $parentUrl = null;
            if (count($segments) > 1) {
                $parentPath = '/'.implode('/', array_slice($segments, 0, -1)).'/';
                $parentUrl = $isBrand ? '/brands'.$parentPath : '/catalog'.$parentPath;
            } elseif ($isBrand) {
                $parentUrl = '/brands/';
            }

            $name = $this->titleFromSlug($slug);
            $fullPath = $path;

            $parentId = $parentUrl ? Category::where('url', $parentUrl)->value('id') : null;

            Category::updateOrCreate(
                ['url' => $fullPath],
                [
                    'parent_id' => $parentId,
                    'name' => $name,
                    'slug' => $slug,
                    'full_path' => $fullPath,
                    'is_active' => true,
                ]
            );

            $bar->advance();
        }
        $bar->finish();
        $this->newLine();
    }

    private function importProducts(array $urls): void
    {
        $bar = $this->output->createProgressBar(count($urls));
        foreach ($urls as $url) {
            $path = parse_url($url, PHP_URL_PATH);
            $slug = basename(trim($path, '/'));
            $name = $this->titleFromSlug($slug);

            Product::updateOrCreate(
                ['url' => $path],
                [
                    'name' => $name,
                    'slug' => $slug,
                    'category_id' => null,
                    'is_active' => true,
                ]
            );

            $bar->advance();
        }
        $bar->finish();
        $this->newLine();
    }

    private function importPages(array $urls): void
    {
        $bar = $this->output->createProgressBar(count($urls));
        foreach ($urls as $url) {
            $path = parse_url($url, PHP_URL_PATH);
            $segments = array_filter(explode('/', trim($path, '/')));
            $slug = end($segments) ?: null;
            $type = match (true) {
                str_starts_with($path, '/blog/') => 'article',
                str_starts_with($path, '/sales/') => 'sale',
                str_starts_with($path, '/company/stores/') => 'store',
                default => 'page',
            };

            $name = $slug ? $this->titleFromSlug($slug) : 'Главная';

            Page::updateOrCreate(
                ['url' => $path],
                [
                    'type' => $type,
                    'title' => $name,
                    'slug' => $slug,
                    'is_active' => true,
                ]
            );

            $bar->advance();
        }
        $bar->finish();
        $this->newLine();
    }

    private function titleFromSlug(string $slug): string
    {
        return Str::title(str_replace('-', ' ', $slug));
    }
}
