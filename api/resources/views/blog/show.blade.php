@extends('layouts.site')

@section('title', $article['title'] . ' — Gadget Bar')

@section('content')
    <section class="page-section">
        <div class="container-theme page-content">
            <x-breadcrumbs :items="[['name' => 'Главная', 'url' => '/'], ['name' => 'Статьи', 'url' => '/blog'], ['name' => $article['title'], 'url' => '/blog/'.$article['slug']]]" />
            <img src="{{ $article['image'] }}" alt="{{ $article['title'] }}" style="width: 100%; max-height: 400px; object-fit: cover; border-radius: 1rem; margin-bottom: 1.5rem;">
            <h1 class="section-title">{{ $article['title'] }}</h1>
            <p>{{ $article['content'] }}</p>
        </div>
    </section>
@endsection
