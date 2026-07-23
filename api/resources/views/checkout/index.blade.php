@extends('layouts.site')

@section('title', 'Оформление заказа — Gadget Bar')

@section('content')
    <section class="page-section" x-data="checkoutPage" x-init="loadCart()">
        <div class="container-theme">
            <h1 class="section-title">Оформление заказа</h1>

            <div class="checkout-grid">
                <form method="POST" action="/checkout" class="checkout-form">
                    @csrf

                    @if (session('error'))
                        <div class="alert alert-error">{{ session('error') }}</div>
                    @endif

                    <div class="form-group">
                        <label>Имя *</label>
                        <input type="text" name="customer_name" class="input" value="{{ old('customer_name', Auth::user()->name ?? '') }}" required>
                    </div>
                    <div class="form-group">
                        <label>Телефон *</label>
                        <input type="tel" name="customer_phone" class="input" value="{{ old('customer_phone', Auth::user()->phone ?? '') }}" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="customer_email" class="input" value="{{ old('customer_email', Auth::user()->email ?? '') }}">
                    </div>
                    <div class="form-group">
                        <label>Город</label>
                        <input type="text" name="customer_city" class="input" value="{{ old('customer_city') }}">
                    </div>
                    <div class="form-group">
                        <label>Комментарий</label>
                        <textarea name="customer_comment" class="input" rows="3">{{ old('customer_comment') }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">Подтвердить заказ</button>
                </form>

                <div class="checkout-summary">
                    <h2>Ваш заказ</h2>
                    <template x-if="loading">
                        <p class="text-muted">Загрузка...</p>
                    </template>
                    <template x-if="!loading">
                        <div>
                            <template x-for="item in items" :key="item.id">
                                <div class="checkout-item">
                                    <span x-text="item.product?.name"></span>
                                    <span x-text="'× ' + item.quantity"></span>
                                </div>
                            </template>
                            <p class="text-muted" x-show="items.length === 0">Корзина пуста</p>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('checkoutPage', () => ({
        loading: true,
        items: [],
        async loadCart() {
            const res = await fetch('/api/cart', { credentials: 'include' });
            const data = await res.json();
            this.items = data.data || [];
            this.loading = false;
        }
    }));
});
</script>
@endpush
