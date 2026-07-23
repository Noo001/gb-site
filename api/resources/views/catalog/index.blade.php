@extends('layouts.site')

@section('title', 'Каталог — Gadget Bar')

@section('content')
    <section class="page-section">
        <div class="container-theme">
            <x-breadcrumbs :items="$breadcrumbs" />

            <h1 class="section-title">Каталог</h1>

            @if (!empty($categories))
                <div class="subcategory-grid">
                    @foreach ($categories as $category)
                        <a href="{{ $category->url }}" class="subcategory-card">
                            @if ($category->image)
                                <img src="{{ $category->image }}" alt="{{ $category->name }}">
                            @else
                                <div class="subcategory-letter">{{ mb_substr($category->name, 0, 1) }}</div>
                            @endif
                            <span>{{ $category->name }}</span>
                        </a>
                    @endforeach
                </div>
            @endif

            <div class="catalog-toolbar">
                <p class="catalog-count">Товаров: {{ $products->total ?? count($products->data) }}</p>
            </div>

            <div class="product-grid">
                @forelse ($products->data as $product)
                    <x-product-card :product="$product" />
                @empty
                    <div class="empty-state">В каталоге пока нет товаров</div>
                @endforelse
            </div>

            @if (($products->last_page ?? 1) > 1)
                <div class="pagination-wrap">
                    @if ($products->prev_page_url)
                        <a href="{{ $products->prev_page_url }}" class="btn btn-outline">← Назад</a>
                    @endif
                    <span class="pagination-info">Страница {{ $products->current_page }} из {{ $products->last_page }}</span>
                    @if ($products->next_page_url)
                        <a href="{{ $products->next_page_url }}" class="btn btn-outline">Вперёд →</a>
                    @endif
                </div>
            @endif
        </div>
    </section>
@endsection
