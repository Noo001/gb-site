@extends('layouts.site')

@section('title', 'Акции — Gadget Bar')

@section('content')
    <section class="page-section">
        <div class="container-theme">
            <h1 class="section-title">Акции</h1>
            <div class="sales-grid">
                @foreach ($sales as $sale)
                    <div class="sales-card">
                        <img src="{{ $sale['image'] }}" alt="{{ $sale['title'] }}">
                        <div class="sales-card-body">
                            <h2>{{ $sale['title'] }}</h2>
                            <p>{{ $sale['description'] }}</p>
                            <a href="/sales/{{ $sale['slug'] }}" class="btn btn-primary">Подробнее</a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endsection
