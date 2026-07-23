@extends('layouts.site')

@section('title', 'Статьи — Gadget Bar')

@section('content')
    <section class="page-section">
        <div class="container-theme">
            <h1 class="section-title">Статьи</h1>
            <div class="blog-grid">
                @foreach ($articles as $article)
                    <article class="blog-card">
                        <img src="{{ $article['image'] }}" alt="{{ $article['title'] }}">
                        <div class="blog-card-body">
                            <h2><a href="/blog/{{ $article['slug'] }}">{{ $article['title'] }}</a></h2>
                            <p>{{ $article['excerpt'] }}</p>
                            <a href="/blog/{{ $article['slug'] }}" class="section-link">Читать дальше →</a>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
@endsection
