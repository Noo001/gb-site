@extends('layouts.site')

@section('title', $category->name . ' — Gadget Bar')

@section('content')
    <section class="page-section">
        <div class="container-theme">
            <x-breadcrumbs :items="$breadcrumbs" />

            <h1 class="section-title">{{ $category->name }}</h1>

            @if (!empty($category->children))
                <div class="subcategory-grid">
                    @foreach ($category->children as $child)
                        <a href="{{ $child->url }}" class="subcategory-card">
                            @if ($child->image)
                                <img src="{{ $child->image }}" alt="{{ $child->name }}">
                            @else
                                <div class="subcategory-letter">{{ mb_substr($child->name, 0, 1) }}</div>
                            @endif
                            <span>{{ $child->name }}</span>
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
                    <div class="empty-state">В этой категории пока нет товаров</div>
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
