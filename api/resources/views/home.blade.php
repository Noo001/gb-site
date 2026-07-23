@extends('layouts.site')

@section('title', 'Gadget Bar — интернет-магазин гаджетов')

@section('content')
    <section class="page-section">
        <div class="container-theme">
            <x-promo-carousel />
        </div>
    </section>

    <x-brand-carousel />

    <section class="page-section">
        <div class="container-theme">
            <div class="section-header">
                <h2 class="section-title">Лучшие предложения</h2>
                <a href="/catalog" class="section-link">Смотреть все →</a>
            </div>
            <div class="product-grid">
                @forelse ($products as $product)
                    <x-product-card :product="$product" />
                @empty
                    <div class="empty-state">Пока нет товаров</div>
                @endforelse
            </div>
        </div>
    </section>

    <section class="page-section">
        <div class="container-theme">
            <h2 class="section-title">Каталог</h2>
            <div class="category-grid">
                @foreach ($categories as $category)
                    @if (!empty($category->children))
                        <a href="{{ $category->url }}" class="category-card">
                            @if ($category->image)
                                <img src="{{ $category->image }}" alt="{{ $category->name }}">
                            @else
                                <div class="category-card-letter">{{ mb_substr($category->name, 0, 1) }}</div>
                            @endif
                            <span>{{ $category->name }}</span>
                        </a>
                    @endif
                @endforeach
            </div>
        </div>
    </section>

    <x-promo-cards />
    <x-about-block />
    <x-vk-posts />
    <x-features />
@endsection
