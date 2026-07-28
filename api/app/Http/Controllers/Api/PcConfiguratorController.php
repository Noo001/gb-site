<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PcDemoPart;
use App\Models\Product;
use App\Services\PcCompatibilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PcConfiguratorController extends Controller
{
    public function slots(PcCompatibilityService $service): JsonResponse
    {
        $slots = collect($service->slots())->map(fn (array $slot) => [
            'id' => $slot['id'],
            'title' => $slot['title'],
            'required' => $slot['required'],
            'empty' => ! $service->slotHasParts($slot['id']),
        ])->values();

        return response()->json([
            'data' => $slots,
            'demo' => $service->isDemoMode(),
        ]);
    }

    public function parts(Request $request, PcCompatibilityService $service): JsonResponse
    {
        $validated = $request->validate([
            'slot' => ['required', 'string'],
            'build' => ['nullable', 'string'],
        ]);

        $slot = $validated['slot'];

        if (! $service->hasSlot($slot)) {
            return response()->json([
                'message' => 'Неизвестный слот конфигуратора.',
                'errors' => ['slot' => ['Неизвестный слот конфигуратора.']],
            ], 422);
        }

        $build = [];

        if (! empty($validated['build'])) {
            $decoded = json_decode($validated['build'], true);

            if (! is_array($decoded)) {
                return response()->json([
                    'message' => 'Параметр build должен быть JSON-объектом вида {"cpu": 123}.',
                    'errors' => ['build' => ['Параметр build должен быть JSON-объектом вида {"cpu": 123}.']],
                ], 422);
            }

            foreach ($decoded as $buildSlot => $productId) {
                if (is_string($buildSlot) && is_numeric($productId)) {
                    $build[$buildSlot] = (int) $productId;
                }
            }
        }

        if ($service->isDemoMode()) {
            $parts = $service->availableDemoParts($slot, $build)
                ->map(fn (PcDemoPart $part) => [
                    'id' => $part->id,
                    'name' => $part->name,
                    'price' => (float) $part->price,
                    'stock' => $part->stock,
                    'attributes' => $service->resolveDemoAttributes($part),
                ])
                ->values();

            return response()->json([
                'slot' => $slot,
                'empty' => ! $service->slotHasParts($slot),
                'data' => $parts,
            ]);
        }

        $parts = $service->availableParts($slot, $build)
            ->map(fn (Product $product) => $this->partResource($service, $product))
            ->values();

        return response()->json([
            'slot' => $slot,
            'empty' => ! $service->slotHasParts($slot),
            'data' => $parts,
        ]);
    }

    private function partResource(PcCompatibilityService $service, Product $product): array
    {
        $price = $product->currentPrice();
        $stock = $product->offers
            ->flatMap(fn ($offer) => $offer->stocks)
            ->sum(fn ($stock) => (float) $stock->quantity);

        return [
            'id' => $product->id,
            'name' => $product->name,
            'price' => $price ? (float) $price->price : null,
            'stock' => $stock,
            'attributes' => $service->resolveAttributes($product),
        ];
    }
}
