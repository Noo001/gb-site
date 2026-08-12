<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Gadget Bar')</title>
    @hasSection('meta_description')
        <meta name="description" content="@yield('meta_description')">
    @endif
    @hasSection('meta_keywords')
        <meta name="keywords" content="@yield('meta_keywords')">
    @endif
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="stylesheet" href="{{ asset('css/site.css') }}?v={{ filemtime(public_path('css/site.css')) }}">
    @stack('styles')
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('cart', {
                count: 0,
                async load() {
                    try {
                        const res = await fetch('/api/cart', { credentials: 'include' });
                        if (!res.ok) return;
                        const data = await res.json();
                        this.count = data.count ?? 0;
                    } catch (e) {}
                }
            });
            Alpine.store('wishlist', {
                count: 0,
                async load() {
                    try {
                        const res = await fetch('/api/wishlist', { credentials: 'include' });
                        if (!res.ok) return;
                        const data = await res.json();
                        this.count = data.count ?? 0;
                    } catch (e) {}
                }
            });
        });
    </script>
</head>
<body x-data x-init="$store.cart.load(); $store.wishlist.load()">
    @include('components.header')

    <main>
        @yield('content')
    </main>

    @include('components.footer')

    @include('components.auth-modal')

    @stack('scripts')
</body>
</html>
