<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Франшиза Gadget Bar')</title>
    @hasSection('meta_description')
        <meta name="description" content="@yield('meta_description')">
    @endif
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="stylesheet" href="{{ asset('css/site.css') }}?v={{ filemtime(public_path('css/site.css')) }}">
    @stack('styles')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('franchiseForm', () => ({
                type: 'franchise',
                name: '',
                phone: '',
                email: '',
                city: '',
                budget: '',
                message: '',
                loading: false,
                success: '',
                error: '',

                async submit() {
                    this.loading = true;
                    this.success = '';
                    this.error = '';
                    try {
                        const submitUrl = window.location.origin + (window.location.host === 'fr.gbsale.ru' ? '/submit' : '/franchise/submit');
                        const res = await fetch(submitUrl, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: JSON.stringify({
                                type: this.type,
                                name: this.name,
                                phone: this.phone,
                                email: this.email,
                                city: this.city,
                                budget: this.budget,
                                message: this.message,
                            }),
                        });
                        const data = await res.json();
                        if (!res.ok || !data.success) {
                            throw new Error(data.message || 'Ошибка отправки');
                        }
                        this.success = data.message;
                        this.name = '';
                        this.phone = '';
                        this.email = '';
                        this.city = '';
                        this.budget = '';
                        this.message = '';
                    } catch (e) {
                        this.error = e.message || 'Произошла ошибка. Попробуйте позже.';
                    } finally {
                        this.loading = false;
                    }
                }
            }));
        });
    </script>
</head>
<body>
    <main>
        @yield('content')
    </main>
    @stack('scripts')
</body>
</html>
