@extends('account.layout')

@section('account_title', 'Заказы')

@section('account_content')
    <h1 class="section-title">Мои заказы</h1>

    <form method="GET" action="{{ route('account.orders') }}" class="orders-filter">
        <div class="orders-filter-group">
            <label for="status" class="orders-filter-label">Статус</label>
            <select name="status" id="status" class="input orders-filter-select" onchange="this.form.submit()">
                <option value="">Все статусы</option>
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="orders-filter-group">
            <label for="payment_status" class="orders-filter-label">Оплата</label>
            <select name="payment_status" id="payment_status" class="input orders-filter-select" onchange="this.form.submit()">
                <option value="">Все</option>
                @foreach ($paymentStatuses as $value => $label)
                    <option value="{{ $value }}" {{ request('payment_status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="orders-filter-group">
            <label for="period" class="orders-filter-label">Период</label>
            <select name="period" id="period" class="input orders-filter-select" onchange="this.form.submit()">
                <option value="">За всё время</option>
                <option value="week" {{ request('period') === 'week' ? 'selected' : '' }}>Неделя</option>
                <option value="month" {{ request('period') === 'month' ? 'selected' : '' }}>Месяц</option>
                <option value="year" {{ request('period') === 'year' ? 'selected' : '' }}>Год</option>
            </select>
        </div>
    </form>

    @if ($orders->count())
        <div class="orders-list">
            @foreach ($orders as $order)
                <div class="order-card">
                    <div class="order-card-header">
                        <div class="order-card-number">Заказ №{{ $order->id }}</div>
                        <div class="order-card-badges">
                            <span class="order-card-status order-card-status-{{ $order->status }}">{{ $order->statusLabel() }}</span>
                            <span class="order-card-payment order-card-payment-{{ $order->payment_status }}">{{ $order->paymentStatusLabel() }}</span>
                        </div>
                    </div>
                    <div class="order-card-body">
                        <div class="order-card-meta">
                            <span>{{ $order->created_at->format('d.m.Y') }}</span>
                            <span>{{ $order->items_count }} {{ trans_choice('товар|товара|товаров', $order->items_count) }}</span>
                        </div>
                        <div class="order-card-total">{{ number_format($order->total ?? 0, 0, ',', ' ') }} ₽</div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="pagination-wrap">
            {{ $orders->links() }}
        </div>
    @else
        <div class="empty-state">
            <p>Нет заказов</p>
        </div>
    @endif
@endsection
