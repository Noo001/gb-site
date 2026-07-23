@extends('layouts.site')

@section('title', $brand->name . ' — Gadget Bar')

@section('content')
    <section class="page-section">
        <div class="container-theme">
            <x-breadcrumbs :items="$breadcrumbs" />
            <h1 class="section-title">{{ $brand->name }}</h1>

            <div class="product-grid">
                @forelse ($products->data as $product)
                    <x-product-card :product="$product" />
                @empty
                    <div class="empty-state">Товары бренда скоро появятся</div>
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
