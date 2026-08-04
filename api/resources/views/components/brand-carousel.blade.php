@php
$brands = [
    ['slug' => 'apple',    'name' => 'Apple',    'image' => '/images/brands/apple.png'],
    ['slug' => 'samsung',  'name' => 'Samsung',  'image' => '/images/brands/samsung.png'],
    ['slug' => 'xiaomi',   'name' => 'Xiaomi',   'image' => '/images/brands/xiaomi.png'],
    ['slug' => 'honor',    'name' => 'Honor',    'image' => '/images/brands/honor.png'],
    ['slug' => 'huawei',   'name' => 'Huawei',   'image' => '/images/brands/huawei.png'],
    ['slug' => 'sony',     'name' => 'Sony',     'image' => '/images/brands/sony.png'],
    ['slug' => 'dyson',    'name' => 'Dyson',    'image' => '/images/brands/dyson.png'],
    ['slug' => 'smeg',     'name' => 'Smeg',     'image' => '/images/brands/smeg.png'],
    ['slug' => 'jbl',      'name' => 'JBL',      'image' => '/images/brands/jbl.png'],
    ['slug' => 'dji',      'name' => 'DJI',      'image' => '/images/brands/dji.png'],
];
@endphp

<section class="page-section brand-section">
    <div class="container-theme">
        <div class="brand-carousel" data-brand-carousel>
            <button type="button" class="brand-carousel__arrow brand-carousel__arrow--prev" aria-label="Назад">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
            </button>

            <div class="brand-carousel__viewport">
                <div class="brand-carousel__track">
                    @foreach ($brands as $brand)
                        <a href="/brands/{{ $brand['slug'] }}" class="brand-carousel__slide">
                            <span class="brand-carousel__logo">
                                <img src="{{ $brand['image'] }}" alt="{{ $brand['name'] }}" loading="lazy">
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>

            <button type="button" class="brand-carousel__arrow brand-carousel__arrow--next" aria-label="Вперёд">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
            </button>
        </div>
    </div>
</section>

<script>
(function () {
    document.querySelectorAll('[data-brand-carousel]').forEach(function (carousel) {
        var track = carousel.querySelector('.brand-carousel__track');
        var prev = carousel.querySelector('.brand-carousel__arrow--prev');
        var next = carousel.querySelector('.brand-carousel__arrow--next');
        if (!track || !prev || !next) return;

        var slideWidth = function () {
            var slide = track.querySelector('.brand-carousel__slide');
            return slide ? slide.getBoundingClientRect().width + parseFloat(getComputedStyle(slide).marginRight || 0) : 200;
        };

        prev.addEventListener('click', function () {
            track.scrollBy({ left: -slideWidth(), behavior: 'smooth' });
        });
        next.addEventListener('click', function () {
            track.scrollBy({ left: slideWidth(), behavior: 'smooth' });
        });
    });
})();
</script>
