@extends('layouts.site')

@section('title', 'Избранное — Gadget Bar')

@section('content')
    <section class="page-section" x-data="wishlistPage" x-init="load()">
        <div class="container-theme">
            <h1 class="section-title">Избранное</h1>

            <template x-if="loading">
                <div class="empty-state">Загрузка...</div>
            </template>

            <template x-if="!loading && items.length === 0">
                <div class="empty-state">В избранном пока ничего нет.</div>
            </template>

            <template x-if="!loading && items.length > 0">
                <div class="product-grid">
                    <template x-for="item in items" :key="item.id">
                        <div class="card product-card">
                            <a :href="item.product?.url" class="product-card-link">
                                <div class="product-card-image">
                                    <img :src="item.product?.images?.[0] || '/images/placeholder-product.svg'" :alt="item.product?.name">
                                </div>
                                <div class="product-card-name" x-text="item.product?.name"></div>
                            </a>
                            <div class="product-card-actions">
                                <button type="button" class="btn btn-outline" @click="remove(item.id)">Удалить</button>
                            </div>
                        </div>
                    </template>
                </div>
            </template>
        </div>
    </section>
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('wishlistPage', () => ({
        loading: true,
        items: [],
        async load() {
            const res = await fetch('/api/wishlist', { credentials: 'include' });
            const data = await res.json();
            this.items = data.data || [];
            this.loading = false;
            if (Alpine.store('wishlist')) Alpine.store('wishlist').count = data.count ?? 0;
        },
        async remove(id) {
            const res = await fetch('/api/wishlist/' + id, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                },
                credentials: 'include'
            });
            const data = await res.json();
            this.items = data.data || [];
            if (Alpine.store('wishlist')) Alpine.store('wishlist').count = data.count ?? 0;
        }
    }));
});
</script>
@endpush
