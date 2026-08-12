@extends('layouts.site')

@section('title')
    @hasSection('account_title')
        @yield('account_title') — Личный кабинет — Gadget Bar
    @else
        Личный кабинет — Gadget Bar
    @endif
@endsection

@section('content')
    <section class="page-section account-section">
        <div class="container-theme">
            <div class="account-layout">
                <aside class="account-sidebar">
                    <div class="account-user">
                        <div class="account-user-avatar">
                            {{ mb_substr(Auth::user()->name, 0, 1) }}
                        </div>
                        <div class="account-user-info">
                            <div class="account-user-name">{{ Auth::user()->name }}</div>
                            <div class="account-user-email">{{ Auth::user()->email }}</div>
                        </div>
                    </div>

                    <nav class="account-menu">
                        <a href="{{ route('account.dashboard') }}" class="account-menu-link @if(request()->routeIs('account.dashboard')) active @endif">
                            <span class="account-menu-icon">🏠</span>
                            Обзор
                        </a>
                        <a href="{{ route('account.profile') }}" class="account-menu-link @if(request()->routeIs('account.profile*')) active @endif">
                            <span class="account-menu-icon">👤</span>
                            Профиль
                        </a>
                        <a href="{{ route('account.orders') }}" class="account-menu-link @if(request()->routeIs('account.orders')) active @endif">
                            <span class="account-menu-icon">📦</span>
                            Заказы
                        </a>
                        <a href="{{ route('account.wishlist') }}" class="account-menu-link @if(request()->routeIs('account.wishlist')) active @endif">
                            <span class="account-menu-icon">❤️</span>
                            Избранное
                        </a>
                        <a href="{{ route('account.bonuses') }}" class="account-menu-link @if(request()->routeIs('account.bonuses')) active @endif">
                            <span class="account-menu-icon">🎁</span>
                            Бонусы
                        </a>
                        <form method="POST" action="{{ route('logout') }}" class="account-menu-link account-menu-logout">
                            @csrf
                            <button type="submit">
                                <span class="account-menu-icon">🚪</span>
                                Выйти
                            </button>
                        </form>
                    </nav>
                </aside>

                <div class="account-main">
                    @if (session('success'))
                        <div class="account-alert account-alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @yield('account_content')
                </div>
            </div>
        </div>
    </section>
@endsection
