@extends('layouts.site')

@section('title', 'Корзина — Gadget Bar')

@section('content')
    <section class="page-section" x-data="cartPage" x-init="load()">
        <div class="container-theme">
            <h1 class="section-title">Корзина</h1>

            <template x-if="loading">
                <div class="empty-state">Загрузка...</div>
            </template>

            <template x-if="!loading && items.length === 0">
                <div class="empty-state">Корзина пуста. <a href="/catalog" style="color: var(--accent);">Перейти в каталог</a></div>
            </template>

            <template x-if="!loading && items.length > 0">
                <div>
                    <div class="cart-items">
                        <template x-for="item in items" :key="item.id">
                            <div class="cart-item">
                                <img x-bind:src='item.product?.images?.[0] || "/images/placeholder-product.svg"' x-bind:alt='item.product?.name'>
                                <div class="cart-item-info">
                                    <a x-bind:href='item.product?.url' x-text='item.product?.name'></a>
                                    <span class="text-muted" x-text="item.offer?.name ?? ''"></span>
                                </div>
                                <div class="cart-item-qty">
                                    <button type="button" @click="updateQty(item.id, item.quantity - 1)">−</button>
                                    <span x-text="item.quantity"></span>
                                    <button type="button" @click="updateQty(item.id, item.quantity + 1)">+</button>
                                </div>
                                <button type="button" class="btn btn-outline btn-sm" @click="remove(item.id)">Удалить</button>
                            </div>
                        </template>
                    </div>

                    <div class="cart-actions">
                        <button type="button" class="btn btn-outline" @click="clear()">Очистить корзину</button>
                        <a href="/checkout" class="btn btn-primary">Оформить заказ</a>
                    </div>
                </div>
            </template>
        </div>
    </section>
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('cartPage', () => ({
        loading: true,
        items: [],
        async load() {
            const res = await fetch('/api/cart', { credentials: 'include' });
            const data = await res.json();
            this.items = data.data || [];
            this.loading = false;
            if (Alpine.store('cart')) Alpine.store('cart').count = data.count ?? 0;
        },
        async updateQty(id, qty) {
            if (qty < 1) return;
            const res = await fetch('/api/cart/items/' + id, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                },
                credentials: 'include',
                body: JSON.stringify({ quantity: qty })
            });
            const data = await res.json();
            this.items = data.data || [];
            if (Alpine.store('cart')) Alpine.store('cart').count = data.count ?? 0;
        },
        async remove(id) {
            const res = await fetch('/api/cart/items/' + id, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                },
                credentials: 'include'
            });
            const data = await res.json();
            this.items = data.data || [];
            if (Alpine.store('cart')) Alpine.store('cart').count = data.count ?? 0;
        },
        async clear() {
            const res = await fetch('/api/cart', {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                },
                credentials: 'include'
            });
            const data = await res.json();
            this.items = data.data || [];
            if (Alpine.store('cart')) Alpine.store('cart').count = data.count ?? 0;
        }
    }));
});
</script>
@endpush
