<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Page;
use App\Models\Product;
use App\Models\SeoMetadata;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SeoController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $path = $request->query('path', '/');
        $path = '/'.trim($path, '/').'/';

        $seo = SeoMetadata::where('url', $path)->first();

        if (! $seo) {
            $entity = $this->resolveEntity($path);
            if ($entity) {
                $seo = $entity->seoMetadata;
            }
        }

        if (! $seo) {
            return response()->json([
                'path' => $path,
                'title' => null,
                'description' => null,
                'keywords' => null,
                'h1' => null,
                'canonical' => null,
                'og' => null,
                'json_ld' => null,
            ]);
        }

        return response()->json([
            'path' => $path,
            'title' => $seo->title,
            'description' => $seo->description,
            'keywords' => $seo->keywords,
            'h1' => $seo->h1,
            'canonical' => $seo->canonical,
            'og' => [
                'title' => $seo->og_title,
                'description' => $seo->og_description,
                'image' => $seo->og_image,
            ],
            'json_ld' => $seo->json_ld,
        ]);
    }

    private function resolveEntity(string $path): Category|Product|Page|null
    {
        return Category::where('url', $path)->first()
            ?? Product::where('url', $path)->first()
            ?? Page::where('url', $path)->first();
    }
}
