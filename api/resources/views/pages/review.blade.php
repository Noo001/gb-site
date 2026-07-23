@extends('layouts.site')

@section('title', 'Оставить отзыв — Gadget Bar')

@section('content')
    <section class="page-section">
        <div class="container-theme">
            <h1 class="section-title">Оставить отзыв</h1>
            <div class="card card-padded" style="max-width: 640px;">
                <p>Расскажите о вашем опыте покупки. Ваш отзыв поможет нам стать лучше.</p>
                <textarea class="input" rows="5" placeholder="Ваш отзыв"></textarea>
                <button type="button" class="btn btn-primary" style="margin-top: 1rem;">Отправить</button>
            </div>
        </div>
    </section>
@endsection
