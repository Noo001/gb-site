@extends('layouts.site')

@section('title', $product->name . ' — Gadget Bar')

@section('content')
    <section class="page-section">
        <div class="container-theme">
            <x-breadcrumbs :items="$breadcrumbs" />

            <div class="product-detail">
                <div class="product-gallery">
                    @php
                        $fallbackImage = $product->category?->image ?? '/images/placeholder-product.svg';
                        $images = !empty($product->images) ? $product->images : [$fallbackImage];
                    @endphp
                    <div class="product-thumbs">
                        @foreach (array_slice($images, 0, 4) as $img)
                            <div class="product-thumb">
                                <img src="{{ $img }}" alt="">
                            </div>
                        @endforeach
                    </div>
                    <div class="product-main-image">
                        <img src="{{ $images[0] }}" alt="{{ $product->name }}">
                    </div>
                </div>

                <div class="product-info" x-data="{
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
                            alert('Добавлено в избранное');
                        } catch (e) {
                            alert(e.message);
                        } finally {
                            this.loading = false;
                        }
                    }
                }">
                    <div class="product-info-header">
                        <h1>{{ $product->name }}</h1>
                        <div class="product-info-actions">
                            <button type="button" class="btn btn-outline btn-wishlist" @click="toggleWishlist" :disabled="loading">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                            </button>
                        </div>
                    </div>

                    @php
                        $detailPrice = $product->price ?? null;
                        $detailStock = $product->stock ?? 0;
                    @endphp
                    <div class="product-buy-box">
                        <div class="product-buy-price">{{ $detailPrice !== null ? number_format($detailPrice, 0, '.', ' ') . ' ₽' : 'Цена по запросу' }}</div>
                        <div class="product-buy-stock">{{ $detailStock > 0 ? 'В наличии' : 'Под заказ' }}</div>
                        <button type="button" class="btn btn-primary btn-cart" @click="addToCart" :disabled="loading">
                            <span x-show="!loading">В корзину</span>
                            <span x-show="loading">Добавляем...</span>
                        </button>
                    </div>

                    @if (!empty($product->offers))
                        <div class="product-offers">
                            <h2>Предложения</h2>
                            <ul>
                                @foreach ($product->offers as $offer)
                                    <li>
                                        <span>{{ $offer->name }}</span>
                                        <span class="text-muted">{{ $offer->sku }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if (!empty($product->category))
                        <a href="{{ $product->category->url }}" class="btn btn-outline">Все товары {{ $product->category->name }}</a>
                    @endif
                </div>
            </div>

            <div class="product-tabs">
                <div class="product-tab-content">
                    <h2>Описание {{ $product->name }}</h2>
                    <div class="product-description">
                        {!! nl2br(e($product->description ?? 'Описание товара появится позже.')) !!}
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
