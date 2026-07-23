@extends('layouts.site')

@section('title', $sale['title'] . ' — Gadget Bar')

@section('content')
    <section class="page-section">
        <div class="container-theme">
            <x-breadcrumbs :items="[['name' => 'Главная', 'url' => '/'], ['name' => 'Акции', 'url' => '/sales'], ['name' => $sale['title'], 'url' => '/sales/'.$sale['slug']]]" />
            <div class="sales-detail">
                <img src="{{ $sale['image'] }}" alt="{{ $sale['title'] }}">
                <h1>{{ $sale['title'] }}</h1>
                <p>{{ $sale['description'] }}</p>
                <a href="/catalog" class="btn btn-primary">Смотреть товары</a>
            </div>
        </div>
    </section>
@endsection
