@extends('layouts.site')

@section('title', 'Акции — Gadget Bar')

@section('content')
    <section class="page-section">
        <div class="container-theme">
            <h1 class="section-title">Акции</h1>
            <div class="sale-list">
                <div class="sale-grid">
                    @foreach ($sales as $sale)
                        <article class="sale-card">
                            <a class="sale-card__link" href="/sales/{{ $sale['slug'] }}">
                                <div class="sale-card__image-wrapper">
                                    <span class="sale-card__image" style="background-image: url('{{ $sale['image'] }}');"></span>
                                    @if (!empty($sale['sticker']))
                                        <div class="sale-card__sticker">
                                            <span class="sale-card__sticker-value">{{ $sale['sticker'] }}</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="sale-card__text">
                                    <h2 class="sale-card__title">{{ $sale['title'] }}</h2>
                                    @if (!empty($sale['period']))
                                        <div class="sale-card__period">
                                            <span class="sale-card__period-date">{{ $sale['period'] }}</span>
                                        </div>
                                    @endif
                                </div>
                            </a>
                        </article>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endsection
