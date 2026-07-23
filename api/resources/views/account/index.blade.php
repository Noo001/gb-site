@extends('layouts.site')

@section('title', 'Личный кабинет — Gadget Bar')

@section('content')
    <section class="page-section">
        <div class="container-theme">
            <h1 class="section-title">Личный кабинет</h1>
            <p>Здравствуйте, {{ Auth::user()->name }}!</p>

            <h2 style="margin-top: 2rem; font-size: 1.25rem;">Мои заказы</h2>
            @forelse ($orders as $order)
                <div class="card card-padded" style="margin-top: 1rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span>Заказ #{{ $order->id }}</span>
                        <span class="badge">{{ $order->status_label }}</span>
                    </div>
                    <p class="text-muted" style="margin: 0.5rem 0 0;">Товаров: {{ $order->items_count }} · {{ $order->created_at }}</p>
                </div>
            @empty
                <p class="text-muted">У вас пока нет заказов.</p>
            @endforelse
        </div>
    </section>
@endsection
