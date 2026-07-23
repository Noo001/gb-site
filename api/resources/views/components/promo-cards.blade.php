@php
$promos = [
    ['title' => 'Бесплатная защита экрана в Gadget bar', 'subtitle' => 'При покупке смартфона', 'image' => '/images/original/promo-screen.png', 'href' => '/sales/besplatnaya-zashchita-ekrana-v-gadget-bar'],
    ['title' => 'Скидка за отзыв', 'subtitle' => 'Бессрочная акция', 'image' => '/images/original/promo-review.png', 'href' => '/sales/skidka-za-otzyv'],
    ['title' => 'Программа Trade-In', 'subtitle' => 'Выгодный обмен старого устройства', 'image' => '/images/original/promo-tradein.png', 'href' => '/sales/programma-trade-in'],
];
@endphp

<section class="page-section">
    <div class="container-theme">
        <div class="section-header">
            <h2 class="section-title">Акции</h2>
            <a href="/sales" class="section-link">Смотреть все →</a>
        </div>
        <div class="promo-cards-grid">
            @foreach ($promos as $promo)
                <a href="{{ $promo['href'] }}" class="promo-card">
                    <div class="promo-card-image">
                        <img src="{{ $promo['image'] }}" alt="{{ $promo['title'] }}" loading="lazy">
                    </div>
                    <div class="promo-card-body">
                        <h3>{{ $promo['title'] }}</h3>
                        <p>{{ $promo['subtitle'] }}</p>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>
