@php
$categories = \Illuminate\Support\Facades\Cache::remember('category_tree_for_catalog_header', now()->addHour(), function () {
    return \App\Models\Category::query()
        ->whereNull('parent_id')
        ->forCatalog()
        ->orderBy('sort')
        ->orderBy('name')
        ->get();
});
$topLinks = [
    ['name' => 'Бренды', 'href' => '/brands'],
    ['name' => 'Статьи', 'href' => '/blog'],
    ['name' => 'Акции', 'href' => '/sales'],
    ['name' => 'Рассрочка', 'href' => '/installment'],
    ['name' => 'Контакты', 'href' => '/contacts'],
];
$infoLinks = [
    ['name' => 'Способы оплаты', 'href' => '/info/payment'],
    ['name' => 'Доставка', 'href' => '/info/delivery'],
    ['name' => 'Гарантия', 'href' => '/info/warranty'],
    ['name' => 'Обмен и возврат', 'href' => '/info/return'],
];
@endphp

<header class="site-header">
    <div class="header-top">
        <div class="container-theme header-top-inner">
            <a href="/" class="logo">
                <img src="/images/original/logo.png" alt="GADGET·BAR" height="32">
            </a>

            <form action="/catalog" method="GET" class="header-search">
                <input type="text" name="search" placeholder="Поиск по товарам" class="input">
                <button type="submit" class="btn btn-primary">Найти</button>
            </form>

            <div class="header-contacts">
                <a href="tel:88005051307" class="phone">8 (800) 505-13-07</a>
            </div>
        </div>
    </div>

    <div class="header-nav">
        <div class="container-theme header-nav-inner">
            <nav class="main-nav">
                <div class="nav-item has-submenu">
                    <a href="/catalog">Каталог</a>
                    <div class="submenu">
                        @foreach ($categories as $category)
                            <a href="{{ $category->url }}">{{ $category->name }}</a>
                        @endforeach
                    </div>
                </div>
                @foreach ($topLinks as $link)
                    <a href="{{ $link['href'] }}" class="nav-item">{{ $link['name'] }}</a>
                @endforeach
                <div class="nav-item has-submenu">
                    <span>Информация</span>
                    <div class="submenu">
                        @foreach ($infoLinks as $link)
                            <a href="{{ $link['href'] }}">{{ $link['name'] }}</a>
                        @endforeach
                    </div>
                </div>
            </nav>

            <div class="header-actions">
                @auth
                    <a href="/account" class="action-link">Кабинет</a>
                    <form method="POST" action="/logout" class="inline">
                        @csrf
                        <button type="submit" class="action-link">Выйти</button>
                    </form>
                @else
                    <button type="button" class="action-link" @click="window.dispatchEvent(new CustomEvent('open-auth-modal'))">
                        Войти
                    </button>
                @endauth
                <a href="/wishlist" class="action-link">
                    Избранное
                    <span x-show="$store.wishlist.count > 0" x-text="$store.wishlist.count" class="badge"></span>
                </a>
                <a href="/cart" class="action-link">
                    Корзина
                    <span x-show="$store.cart.count > 0" x-text="$store.cart.count" class="badge"></span>
                </a>
            </div>
        </div>
    </div>
</header>
