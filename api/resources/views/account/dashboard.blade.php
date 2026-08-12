@extends('account.layout')

@section('account_title', 'Обзор')

@section('account_content')
    <h1 class="section-title">Мой кабинет</h1>

    <div class="dashboard-tiles">
        <a href="{{ route('account.dashboard') }}" class="dashboard-tile">
            <span class="dashboard-tile-icon">🏠</span>
            <span class="dashboard-tile-title">Мой кабинет</span>
            <span class="dashboard-tile-subtitle">Обзор</span>
        </a>

        <a href="{{ route('account.profile') }}" class="dashboard-tile">
            <span class="dashboard-tile-icon">👤</span>
            <span class="dashboard-tile-title">Личные данные</span>
            <span class="dashboard-tile-subtitle">{{ Auth::user()->name }}</span>
        </a>

        <a href="{{ route('account.orders') }}" class="dashboard-tile">
            <span class="dashboard-tile-icon">📦</span>
            <span class="dashboard-tile-title">Заказы</span>
            <span class="dashboard-tile-subtitle">{{ $ordersCount }} {{ trans_choice('заказ|заказа|заказов', $ordersCount) }}</span>
        </a>

        <a href="{{ route('account.wishlist') }}" class="dashboard-tile">
            <span class="dashboard-tile-icon">❤️</span>
            <span class="dashboard-tile-title">Избранное</span>
            <span class="dashboard-tile-subtitle">{{ $wishlistCount }} {{ trans_choice('товар|товара|товаров', $wishlistCount) }}</span>
        </a>

        <a href="{{ route('page.contacts') }}" class="dashboard-tile">
            <span class="dashboard-tile-icon">🎧</span>
            <span class="dashboard-tile-title">Помощь</span>
            <span class="dashboard-tile-subtitle">Связаться с нами</span>
        </a>

        <a href="{{ route('account.bonuses') }}" class="dashboard-tile dashboard-tile-accent">
            <span class="dashboard-tile-icon">🎁</span>
            <span class="dashboard-tile-title">Баланс бонусов</span>
            <span class="dashboard-tile-subtitle">{{ number_format($bonusBalance, 0, ',', ' ') }} ₽</span>
        </a>
    </div>
@endsection
