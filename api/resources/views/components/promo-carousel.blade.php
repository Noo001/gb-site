@php
$slides = [
    ['image' => '/images/original/banner-1.png', 'href' => '/sales/aktsiya-kodovyy-zamok-udachi', 'alt' => 'Кодовый замок удачи'],
    ['image' => '/images/original/banner-2.png', 'href' => '/catalog/smartfony', 'alt' => 'iPhone'],
    ['image' => '/images/original/banner-3.png', 'href' => '/installment', 'alt' => 'Рассрочка'],
    ['image' => '/images/original/banner-4.png', 'href' => '/brands/samsung', 'alt' => 'Samsung'],
    ['image' => '/images/original/banner-5.png', 'href' => '/catalog/smartfony', 'alt' => 'iPhone'],
    ['image' => '/images/original/banner-6.png', 'href' => '/brands/dyson', 'alt' => 'Dyson'],
    ['image' => '/images/original/banner-7.png', 'href' => '/catalog/igrovye-konsoli', 'alt' => 'Игровые консоли'],
    ['image' => '/images/original/banner-8.png', 'href' => '/catalog/nausniki-i-audio/kolonki', 'alt' => 'Яндекс Станции'],
    ['image' => '/images/original/banner-9.png', 'href' => '/sales/programma-trade-in', 'alt' => 'Trade-In'],
    ['image' => '/images/original/banner-10.png', 'href' => '/brands/smeg', 'alt' => 'Smeg'],
];
@endphp

<div class="promo-carousel" x-data="{ current: 0, total: {{ count($slides) }} }" x-init="setInterval(() => current = (current + 1) % total, 5000)">
    <div class="promo-slides">
        @foreach ($slides as $index => $slide)
            <a href="{{ $slide['href'] }}" class="promo-slide" x-show="current === {{ $index }}" x-transition.opacity>
                <img src="{{ $slide['image'] }}" alt="{{ $slide['alt'] }}">
            </a>
        @endforeach
    </div>

    <button type="button" class="promo-nav promo-prev" @click="current = (current - 1 + total) % total" aria-label="Назад">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
    </button>
    <button type="button" class="promo-nav promo-next" @click="current = (current + 1) % total" aria-label="Вперёд">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
    </button>

    <div class="promo-dots">
        @foreach ($slides as $index => $slide)
            <button type="button" @click="current = {{ $index }}" :class="current === {{ $index }} ? 'active' : ''" aria-label="Слайд {{ $index + 1 }}"></button>
        @endforeach
    </div>
</div>
