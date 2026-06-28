<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BotActionLog;
use App\Models\BotKnowledge;
use App\Models\BotProduct;
use App\Models\BotTradeInPrice;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BotController extends Controller
{
    public function searchProducts(Request $request): JsonResponse
    {
        $payload = $this->parseRequestPayload($request);

        $query = trim($payload['query'] ?? '');
        $filter = $payload['filter'] ?? [];
        $ranking = $payload['ranking'] ?? [];
        $fields = $payload['fields'] ?? [];
        $limit = min((int) ($payload['limit'] ?? 40), 100);

        $dbQuery = BotProduct::query()
            ->where('is_active', true)
            ->whereNotNull('name');

        // Full-text-ish search across prepared search text.
        if ($query !== '') {
            $dbQuery->where(function ($q) use ($query) {
                $search = "%{$query}%";
                $q->where('search_text', 'like', $search)
                    ->orWhere('name', 'like', $search)
                    ->orWhere('brand', 'like', $search)
                    ->orWhere('category', 'like', $search)
                    ->orWhere('subcategory', 'like', $search);
            });
        }

        if (! empty($filter['brand_contains'])) {
            $dbQuery->where('brand', 'like', '%' . $filter['brand_contains'] . '%');
        }

        if (! empty($filter['name_contains'])) {
            $dbQuery->where(function ($q) use ($filter) {
                $term = '%' . $filter['name_contains'] . '%';
                $q->where('name', 'like', $term)
                    ->orWhere('search_text', 'like', $term);
            });
        }

        if (! empty($filter['category'])) {
            $dbQuery->where('category', 'like', '%' . $filter['category'] . '%');
        }

        if (! empty($filter['subcategory'])) {
            $dbQuery->where('subcategory', 'like', '%' . $filter['subcategory'] . '%');
        }

        if (! empty($filter['availability']) && $filter['availability'] === 'in_stock') {
            $dbQuery->where('availability', 'in_stock');
        }

        // Availability boost: in-stock first, then cheaper first.
        if (! empty($ranking['availability_boost'])) {
            $dbQuery->orderByRaw("CASE WHEN availability = 'in_stock' THEN 0 ELSE 1 END")
                ->orderBy('price', 'asc');
        } else {
            $dbQuery->orderBy('price', 'asc');
        }

        $results = $dbQuery->limit($limit)->get();

        // Post-filter metadata because SQLite/JSON operators vary across drivers.
        if (! empty($filter['metadata_filter']) && is_array($filter['metadata_filter'])) {
            $results = $results->filter(function (BotProduct $product) use ($filter) {
                $metadata = $product->metadata ?? [];
                foreach ($filter['metadata_filter'] as $key => $value) {
                    if (($metadata[$key] ?? null) != $value) {
                        return false;
                    }
                }
                return true;
            })->values();
        }

        // Projection support: if fields requested, keep only those keys.
        if (! empty($fields) && is_array($fields)) {
            $results = $results->map(fn (BotProduct $p) => $p->only($fields));
        } else {
            $results = $results->map(fn (BotProduct $p) => $p->toArray());
        }

        return response()->json($results);
    }

    public function findAlternatives(Request $request): JsonResponse
    {
        $data = $request->validate([
            'p_name' => ['nullable', 'string', 'max:1000'],
            'p_brand' => ['nullable', 'string', 'max:255'],
            'p_model_line' => ['nullable', 'string', 'max:255'],
            'p_price_from' => ['nullable', 'numeric', 'min:0'],
            'p_limit' => ['nullable', 'integer', 'min:1', 'max:20'],
        ]);

        $limit = min((int) ($data['p_limit'] ?? 5), 20);

        $query = BotProduct::query()
            ->where('is_active', true)
            ->where('availability', 'in_stock');

        if (! empty($data['p_brand'])) {
            $query->where('brand', 'like', '%' . $data['p_brand'] . '%');
        }

        if (! empty($data['p_model_line'])) {
            $query->where(function ($q) use ($data) {
                $term = '%' . $data['p_model_line'] . '%';
                $q->where('name', 'like', $term)
                    ->orWhere('search_text', 'like', $term);
            });
        }

        if (! empty($data['p_price_from'])) {
            $query->where('price', '>=', (float) $data['p_price_from']);
        }

        if (! empty($data['p_name'])) {
            $name = $data['p_name'];
            $query->where(function ($q) use ($name) {
                $term = '%' . $name . '%';
                $q->where('name', 'not like', $term)
                    ->where('search_text', 'not like', $term);
            });
        }

        $items = $query->orderBy('price', 'asc')->limit($limit)->get();

        return response()->json($items);
    }

    public function searchServices(Request $request): JsonResponse
    {
        $data = $request->validate([
            'p_query' => ['nullable', 'string', 'max:1000'],
            'p_brand' => ['nullable', 'string', 'max:255'],
            'p_model' => ['nullable', 'string', 'max:255'],
            'p_group' => ['nullable', 'string', 'max:64'],
        ]);

        $items = BotKnowledge::query()
            ->where('type', 'service')
            ->where('is_active', true)
            ->when(! empty($data['p_group']), function ($q) use ($data) {
                $q->where('group', $data['p_group']);
            })
            ->orderBy('sort')
            ->orderBy('id')
            ->get();

        // Post-filter by brand/model/query inside payload because JSON operators differ per driver.
        $filters = array_filter([
            $data['p_brand'] ?? null,
            $data['p_model'] ?? null,
            $data['p_query'] ?? null,
        ]);

        if (! empty($filters)) {
            $items = $items->filter(function (BotKnowledge $item) use ($filters) {
                $haystack = mb_strtolower($item->key . ' ' . json_encode($item->payload));
                foreach ($filters as $needle) {
                    if (! Str::contains($haystack, mb_strtolower($needle))) {
                        return false;
                    }
                }
                return true;
            })->values();
        }

        return response()->json($items->pluck('payload')->values());
    }

    public function checkTrigger(Request $request): JsonResponse
    {
        $data = $request->validate([
            'p_message' => ['required', 'string', 'max:4000'],
            'p_channel' => ['nullable', 'string', 'max:64'],
        ]);

        $message = mb_strtolower(trim($data['p_message']));

        $trigger = BotKnowledge::query()
            ->where('type', 'trigger')
            ->where('is_active', true)
            ->get()
            ->first(function (BotKnowledge $item) use ($message) {
                $phrase = mb_strtolower($item->key);
                return Str::contains($message, $phrase);
            });

        if ($trigger) {
            return response()->json([
                'triggered' => true,
                'action' => $trigger->payload['action'] ?? 'unknown',
                'message' => $trigger->payload['message'] ?? null,
            ]);
        }

        return response()->json([
            'triggered' => false,
            'action' => null,
            'message' => null,
        ]);
    }

    public function logAction(Request $request): JsonResponse
    {
        $data = $request->validate([
            'channel' => ['nullable', 'string', 'max:64'],
            'action' => ['required', 'string', 'max:128'],
            'payload' => ['nullable', 'array'],
            'metadata' => ['nullable', 'array'],
        ]);

        BotActionLog::create([
            'channel' => $data['channel'] ?? null,
            'action' => $data['action'],
            'payload' => $data['payload'] ?? null,
            'metadata' => $data['metadata'] ?? null,
            'ip' => $request->ip(),
        ]);

        return response()->json(['success' => true]);
    }

    public function getConfig(Request $request): JsonResponse
    {
        $data = $request->validate([
            'p_group' => ['required', 'string', 'max:64'],
            'p_key' => ['nullable', 'string', 'max:128'],
        ]);

        $query = BotKnowledge::query()
            ->where('type', 'config')
            ->where('is_active', true)
            ->where('group', $data['p_group']);

        if (! empty($data['p_key'])) {
            $query->where('key', $data['p_key']);
        }

        $items = $query->orderBy('sort')->orderBy('id')->get();

        if (! empty($data['p_key']) && $items->count() === 1) {
            return response()->json($items->first()->payload);
        }

        return response()->json($items->pluck('payload')->values());
    }

    public function getStores(Request $request): JsonResponse
    {
        $data = $request->validate([
            'p_city' => ['nullable', 'string', 'max:255'],
            'p_city_slug' => ['nullable', 'string', 'max:255'],
            'p_type' => ['nullable', 'string', 'max:64'],
        ]);

        $query = Store::query()
            ->where('is_active', true);

        if (! empty($data['p_city'])) {
            $query->where('city', 'like', '%' . $data['p_city'] . '%');
        }

        if (! empty($data['p_type'])) {
            // The stores table does not have a type column; map via payload if needed.
            // For now we treat any active store as a store.
        }

        $stores = $query->orderBy('sort')->orderBy('name')->get()->map(function (Store $store) {
            return [
                'id' => $store->id,
                'name' => $store->name,
                'city' => $store->city,
                'address' => $store->address,
                'phone' => $store->phone,
                'email' => $store->email,
                'schedule' => $store->schedule,
                'latitude' => $store->latitude,
                'longitude' => $store->longitude,
                'type' => 'store',
            ];
        });

        return response()->json($stores);
    }

    public function getTradeInPrice(Request $request): JsonResponse
    {
        $data = $request->validate([
            'p_brand' => ['nullable', 'string', 'max:255'],
            'p_model' => ['nullable', 'string', 'max:255'],
            'p_storage' => ['nullable', 'string', 'max:64'],
            'p_condition' => ['nullable', 'string', 'max:64'],
        ]);

        $query = BotTradeInPrice::query()
            ->where('is_active', true);

        if (! empty($data['p_brand'])) {
            $query->where('brand', 'like', '%' . $data['p_brand'] . '%');
        }

        if (! empty($data['p_model'])) {
            $query->where('model', 'like', '%' . $data['p_model'] . '%');
        }

        if (! empty($data['p_storage'])) {
            $query->where('storage', 'like', '%' . $data['p_storage'] . '%');
        }

        if (! empty($data['p_condition'])) {
            $query->where('condition', $data['p_condition']);
        }

        $items = $query->orderBy('price', 'desc')->limit(10)->get();

        return response()->json($items);
    }

    /**
     * The n8n workflow sends either a serialized parameters0 string
     * ({"parameters0":"{...}"}) or a raw JSON body.
     */
    private function parseRequestPayload(Request $request): array
    {
        $body = $request->all();

        if (! empty($body['parameters0']) && is_string($body['parameters0'])) {
            $decoded = json_decode($body['parameters0'], true);
            if (is_array($decoded)) {
                return $decoded;
            }

            throw ValidationException::withMessages([
                'parameters0' => ['Невалидная JSON-строка в parameters0.'],
            ]);
        }

        return $body;
    }
}
