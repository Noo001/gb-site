@props(['product'])

@php
$image = $product->images[0] ?? $product->category?->image ?? '/images/placeholder-product.svg';
@endphp

<div class="card product-card" x-data="{
    loading: false,
    isAuth: @json(Auth::check()),
    async addToCart() {
        this.loading = true;
        try {
            const res = await fetch('/api/cart/items', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                },
                credentials: 'include',
                body: JSON.stringify({ product_id: {{ $product->id }}, quantity: 1 })
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Ошибка');
            if (Alpine.store('cart')) Alpine.store('cart').count = data.count ?? 0;
            alert('Товар добавлен в корзину');
        } catch (e) {
            alert(e.message);
        } finally {
            this.loading = false;
        }
    },
    async toggleWishlist() {
        if (!this.isAuth) {
            window.location.href = '/login';
            return;
        }
        this.loading = true;
        try {
            const res = await fetch('/api/wishlist', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                },
                credentials: 'include',
                body: JSON.stringify({ product_id: {{ $product->id }} })
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Ошибка');
            if (Alpine.store('wishlist')) Alpine.store('wishlist').count = data.count ?? 0;
        } catch (e) {
            alert(e.message);
        } finally {
            this.loading = false;
        }
    }
}">
    <a href="{{ $product->url }}" class="product-card-link">
        <div class="product-card-image">
            <img src="{{ $image }}" alt="{{ $product->name }}" loading="lazy">
            <span class="product-card-badge">ХИТ</span>
        </div>

        @if ($product->brand)
            <div class="product-card-brand">{{ $product->brand }}</div>
        @endif

        <div class="product-card-price">Цена по запросу</div>
        <div class="product-card-bonus"><span>GB</span> + 0 бонусов</div>
        <div class="product-card-name">{{ $product->name }}</div>
        <div class="product-card-stock">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            В наличии
        </div>
    </a>

    <div class="product-card-actions">
        <button type="button" class="btn btn-primary btn-cart" @click="addToCart" :disabled="loading">
            <span x-show="!loading">В корзину</span>
            <span x-show="loading">Добавляем...</span>
        </button>
        <button type="button" class="btn btn-outline btn-wishlist" @click="toggleWishlist" :disabled="loading" title="В избранное">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
        </button>
    </div>
</div>
