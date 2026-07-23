@extends('layouts.site')

@section('title', 'Бренды — Gadget Bar')

@section('content')
    <section class="page-section">
        <div class="container-theme">
            <x-breadcrumbs :items="[['name' => 'Главная', 'url' => '/'], ['name' => 'Бренды', 'url' => '/brands']]" />
            <h1 class="section-title">Бренды</h1>

            <div class="brand-grid">
                @forelse ($brands as $brand)
                    <a href="{{ $brand->url }}" class="brand-item">{{ $brand->name }}</a>
                @empty
                    <div class="empty-state">Бренды пока не добавлены</div>
                @endforelse
            </div>
        </div>
    </section>
@endsection
