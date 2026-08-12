@extends('account.layout')

@section('account_title', 'Избранное')

@section('account_content')
    <h1 class="section-title">Избранное</h1>

    @if ($items->count())
        <div class="product-grid">
            @foreach ($items as $item)
                @php
                    $product = $item->product;
                    $price = $product?->minPrice();
                @endphp
                <div class="card product-card">
                    <a href="{{ $product->url ?? route('product.show', $product->slug) }}" class="product-card-link">
                        <div class="product-card-image">
                            <img src="{{ $product->getFirstMediaUrl('images') ?: '/images/placeholder-product.svg' }}" alt="{{ $product->name }}">
                        </div>
                        @if ($product->brand)
                            <div class="product-card-brand">{{ $product->brand }}</div>
                        @endif
                        <div class="product-card-price">
                            {{ $price !== null ? number_format($price, 0, ',', ' ') . ' ₽' : 'Цена по запросу' }}
                        </div>
                        <div class="product-card-name">{{ $product->name }}</div>
                    </a>
                    <div class="product-card-actions">
                        <button type="button" class="btn btn-outline btn-sm">В корзину</button>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="empty-state">
            <p>В избранном пока ничего нет.</p>
            <a href="{{ route('catalog.show') }}" class="btn btn-primary" style="margin-top: 1rem;">Перейти в каталог</a>
        </div>
    @endif
@endsection
