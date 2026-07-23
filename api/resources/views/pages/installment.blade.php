@extends('layouts.site')

@section('title', 'Рассрочка — Gadget Bar')

@section('content')
    <section class="page-section">
        <div class="container-theme">
            <div class="installment-hero">
                <div class="installment-hero-label">ХАЛВА | GADGET-BAR</div>
                <h1>Рассрочка до 36 месяцев</h1>
                <p>Покупайте iPhone, смартфоны, ноутбуки и технику для дома в рассрочку 0% от Халва. Быстрое оформление прямо на сайте.</p>
                <a href="/catalog" class="btn btn-primary">Смотреть товары</a>
            </div>

            <div class="installment-banner">
                <img src="/images/original/promos/rassrochka.PNG" alt="Рассрочка 0%">
            </div>

            <div class="features-grid">
                <div class="card card-padded">
                    <h3>0% переплат</h3>
                    <p>Никаких скрытых комиссий и процентов на весь срок рассрочки.</p>
                </div>
                <div class="card card-padded">
                    <h3>До 36 месяцев</h3>
                    <p>Выбирайте удобный срок: 3, 6, 10, 12, 24 или 36 месяцев.</p>
                </div>
                <div class="card card-padded">
                    <h3>Онлайн-оформление</h3>
                    <p>Решение за несколько минут без визита в офис.</p>
                </div>
            </div>
        </div>
    </section>
@endsection
