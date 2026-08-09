<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PcDemoPart;
use App\Models\Product;
use App\Services\PcAutoBuildService;
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

        $result = $this->resolveSlotParts($validated['slot'], $validated['build'] ?? null, $service);

        if (isset($result['error'])) {
            return response()->json([
                'message' => $result['error'],
                'errors' => [$result['slot'] => [$result['error']]],
            ], 422);
        }

        return response()->json($result);
    }

    public function partsBatch(Request $request, PcCompatibilityService $service): JsonResponse
    {
        $validated = $request->validate([
            'slots' => ['required', 'array'],
            'slots.*' => ['string'],
            'build' => ['nullable', 'string'],
        ]);

        $buildJson = $validated['build'] ?? null;
        $results = [];

        foreach ($validated['slots'] as $slot) {
            $results[$slot] = $this->resolveSlotParts($slot, $buildJson, $service);
        }

        return response()->json([
            'data' => $results,
        ]);
    }

    private function resolveSlotParts(string $slot, ?string $buildJson, PcCompatibilityService $service): array
    {
        if (! $service->hasSlot($slot)) {
            return ['slot' => $slot, 'error' => 'Неизвестный слот конфигуратора.'];
        }

        $build = $this->parseBuild($buildJson);

        if (isset($build['error'])) {
            return ['slot' => $slot, 'error' => $build['error']];
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

            return [
                'slot' => $slot,
                'empty' => ! $service->slotHasParts($slot),
                'data' => $parts,
            ];
        }

        $parts = $service->availableParts($slot, $build)
            ->map(fn (Product $product) => $this->partResource($service, $product))
            ->values();

        return [
            'slot' => $slot,
            'empty' => ! $service->slotHasParts($slot),
            'data' => $parts,
        ];
    }

    private function parseBuild(?string $buildJson): array
    {
        if (empty($buildJson)) {
            return [];
        }

        $decoded = json_decode($buildJson, true);

        if (! is_array($decoded)) {
            return ['error' => 'Параметр build должен быть JSON-объектом вида {"cpu": 123}.'];
        }

        $build = [];

        foreach ($decoded as $buildSlot => $productId) {
            if (is_string($buildSlot) && is_numeric($productId)) {
                $build[$buildSlot] = (int) $productId;
            } elseif (is_string($buildSlot) && is_array($productId)) {
                $build[$buildSlot] = array_values(array_filter(array_map('intval', array_filter($productId, 'is_numeric'))));
            }
        }

        return $build;
    }

    public function autoBuild(Request $request, PcAutoBuildService $auto): JsonResponse
    {
        $validated = $request->validate([
            'budget' => ['required', 'numeric', 'min:1000'],
            'purpose' => ['nullable', 'string', 'in:games,work,office,other'],
            'wishes' => ['nullable', 'string', 'max:2000'],
        ]);

        $result = $auto->build($validated['budget'], $validated['purpose'] ?? null);

        if ($result === null) {
            return response()->json([
                'success' => false,
                'reason' => 'Не удалось подобрать комплектующие в указанный бюджет. Попробуйте увеличить бюджет или оставить заявку менеджеру.',
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    private function partResource(PcCompatibilityService $service, Product $product): array
    {
        $price = $product->currentPrice();
        $stock = $product->totalStock();

        return [
            'id' => $product->id,
            'name' => $product->name,
            'price' => $price ? (float) $price->price : null,
            'stock' => $stock,
            'attributes' => $service->resolveAttributes($product),
        ];
    }
}
