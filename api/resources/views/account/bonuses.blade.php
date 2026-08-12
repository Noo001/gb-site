@extends('account.layout')

@section('account_title', 'Бонусы')

@section('account_content')
    <h1 class="section-title">Бонусная программа</h1>

    <div class="bonus-balance-card">
        <div class="bonus-balance-label">Баланс бонусов</div>
        <div class="bonus-balance-value">{{ number_format($balance, 0, ',', ' ') }} ₽</div>
    </div>

    <h2 class="section-title" style="margin-top: 2rem; font-size: 1.25rem;">История операций</h2>

    @if ($operations->count())
        <div class="account-table-wrap">
            <table class="account-table">
                <thead>
                    <tr>
                        <th>Дата</th>
                        <th>Тип</th>
                        <th>Сумма</th>
                        <th>Баланс после</th>
                        <th>Описание</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($operations as $operation)
                        <tr>
                            <td>{{ $operation->created_at->format('d.m.Y H:i') }}</td>
                            <td>{{ $operation->type }}</td>
                            <td class="{{ $operation->amount >= 0 ? 'bonus-amount-positive' : 'bonus-amount-negative' }}">
                                {{ $operation->amount >= 0 ? '+' : '' }}{{ number_format($operation->amount, 0, ',', ' ') }} ₽
                            </td>
                            <td>{{ number_format($operation->balance_after, 0, ',', ' ') }} ₽</td>
                            <td>{{ $operation->description ?: '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="pagination-wrap">
            {{ $operations->links() }}
        </div>
    @else
        <div class="empty-state">
            <p>История бонусов пуста.</p>
        </div>
    @endif
@endsection
