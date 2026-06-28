<?php

namespace App\Observers;

use App\Jobs\NotifyPriceChangedTo1C;
use App\Models\Price;

class PriceObserver
{
    public function saved(Price $price): void
    {
        if (Price::$syncingFrom1C) {
            return;
        }

        $offer = $price->offer;
        if (! $offer) {
            return;
        }

        $product = $offer->product;
        if (! $product || empty($product->uuid_1c)) {
            return;
        }

        NotifyPriceChangedTo1C::dispatch(
            $product->uuid_1c,
            (float) $price->price,
            now()->toIso8601String(),
            'admin_panel'
        );
    }
}
