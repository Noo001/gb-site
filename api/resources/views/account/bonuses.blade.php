@extends('account.layout')

@section('account_title', 'Бонусы')

@section('account_content')
    <h1 class="section-title">Бонусная программа</h1>

    <div class="bonus-balance-card">
        <div class="bonus-balance-label">Баланс бонусов</div>
        <div class="bonus-balance-value">{{ number_format($balance, 0, ',', ' ') }} ₽</div>
    </div>

    @if ($terms)
        <div class="bonus-terms-card">
            <h2 class="bonus-terms-title">Условия бонусной программы</h2>
            @if ($needsAccept)
                <div class="bonus-terms-content">
                    {!! nl2br(e($terms->content)) !!}
                </div>
                <form method="POST" action="{{ route('account.bonuses.terms') }}">
                    @csrf
                    <button type="submit" class="btn btn-primary">Принять условия</button>
                </form>
            @else
                <p class="bonus-terms-accepted">✅ Условия приняты (версия {{ $user->accepted_bonus_terms_version ?? $terms->version }}).</p>
            @endif
        </div>
    @endif

    <div class="bonus-actions">
        <div class="bonus-action-card">
            <h3 class="bonus-action-title">Ежедневный сбор</h3>
            <p class="bonus-action-text">Заходите каждый день и получайте бонусы. Каждые 7 дней подряд — повышенная награда.</p>
            @if ($canCollectDaily)
                <form method="POST" action="{{ route('account.bonuses.daily') }}">
                    @csrf
                    <button type="submit" class="btn btn-primary">Собрать бонусы</button>
                </form>
            @else
                <button class="btn btn-outline" disabled>Собрано сегодня, возвращайтесь завтра</button>
            @endif
        </div>
    </div>

    <div class="bonus-roulette-section" x-data="bonusWheel(@js($sectors), @js($needsAccept), {{ $freeSpins }}, {{ $spinCost }}, {{ $balance }})" x-init="initWheel()">
        <h2 class="section-title" style="margin-top: 2rem; font-size: 1.25rem;">Колесо фортуны</h2>

        <div class="roulette-layout">
            <div class="roulette-wheel-wrap">
                <canvas x-ref="wheel" width="360" height="360" class="roulette-wheel"></canvas>
                <div class="roulette-pointer"></div>
            </div>

            <div class="roulette-controls">
                <p class="roulette-info">
                    Бесплатных прокруток: <strong x-text="freeSpins">{{ $freeSpins }}</strong>
                </p>
                <p class="roulette-info">
                    Стоимость платной прокрутки: <strong>{{ number_format($spinCost, 0, ',', ' ') }} ₽</strong> бонусов
                </p>

                <div class="roulette-buttons">
                    <button
                        type="button"
                        class="btn btn-outline"
                        :disabled="spinning || needsAccept || freeSpins <= 0"
                        @click="spin(true)"
                    >
                        Бесплатная прокрутка
                    </button>
                    <button
                        type="button"
                        class="btn btn-primary"
                        :disabled="spinning || needsAccept || balance < spinCost"
                        @click="spin(false)"
                    >
                        Прокрутить за {{ number_format($spinCost, 0, ',', ' ') }} ₽
                    </button>
                </div>

                <template x-if="message">
                    <div class="roulette-message" :class="messageType" x-text="message"></div>
                </template>

                @if ($needsAccept)
                    <p class="bonus-notice">Чтобы крутить рулетку, нужно принять условия бонусной программы.</p>
                @endif
            </div>
        </div>
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

    @push('scripts')
        <script>
            function bonusWheel(sectors, needsAccept, freeSpins, spinCost, balance) {
                return {
                    sectors,
                    needsAccept,
                    freeSpins,
                    spinCost,
                    balance,
                    spinning: false,
                    message: '',
                    messageType: '',
                    rotation: 0,
                    colors: [
                        '#0cc0df', '#ff9f43', '#10b981', '#ef4444',
                        '#8b5cf6', '#f59e0b', '#3b82f6', '#ec4899',
                        '#6366f1', '#14b8a6'
                    ],
                    initWheel() {
                        this.drawWheel();
                        window.addEventListener('resize', () => this.drawWheel());
                    },
                    drawWheel() {
                        const canvas = this.$refs.wheel;
                        if (!canvas) return;
                        const ctx = canvas.getContext('2d');
                        const dpr = window.devicePixelRatio || 1;
                        const size = Math.min(360, canvas.clientWidth || 360);
                        canvas.width = size * dpr;
                        canvas.height = size * dpr;
                        canvas.style.width = size + 'px';
                        canvas.style.height = size + 'px';
                        ctx.scale(dpr, dpr);

                        const cx = size / 2;
                        const cy = size / 2;
                        const radius = size / 2 - 8;

                        ctx.clearRect(0, 0, size, size);

                        const count = this.sectors.length || 1;
                        const angle = (Math.PI * 2) / count;

                        for (let i = 0; i < count; i++) {
                            const start = i * angle;
                            const end = start + angle;
                            ctx.beginPath();
                            ctx.moveTo(cx, cy);
                            ctx.arc(cx, cy, radius, start, end);
                            ctx.closePath();
                            ctx.fillStyle = this.colors[i % this.colors.length];
                            ctx.fill();
                            ctx.stroke();

                            ctx.save();
                            ctx.translate(cx, cy);
                            ctx.rotate(start + angle / 2);
                            ctx.textAlign = 'right';
                            ctx.fillStyle = '#fff';
                            ctx.font = 'bold 12px Inter, sans-serif';
                            ctx.fillText(this.truncate(this.sectors[i]?.label || '', 16), radius - 14, 5);
                            ctx.restore();
                        }

                        ctx.beginPath();
                        ctx.arc(cx, cy, 28, 0, Math.PI * 2);
                        ctx.fillStyle = '#fff';
                        ctx.fill();
                        ctx.stroke();
                    },
                    truncate(text, max) {
                        if (!text) return '';
                        return text.length > max ? text.slice(0, max - 1) + '…' : text;
                    },
                    async spin(useFree) {
                        if (this.spinning) return;
                        this.spinning = true;
                        this.message = '';

                        try {
                            const response = await fetch('{{ route('account.bonuses.spin') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({ free: useFree })
                            });

                            const data = await response.json();

                            if (!response.ok || data.error) {
                                this.message = data.error || 'Ошибка прокрутки';
                                this.messageType = 'error';
                                this.spinning = false;
                                return;
                            }

                            const index = this.sectors.findIndex(s => s.id === data.sector.id);
                            const count = this.sectors.length;
                            const sectorAngle = 360 / count;
                            const randomOffset = Math.random() * (sectorAngle - 6) + 3;
                            const targetAngle = index * sectorAngle + sectorAngle / 2 + randomOffset;

                            const extraSpins = 5 * 360;
                            const newRotation = this.rotation + extraSpins + (270 - targetAngle - (this.rotation % 360));
                            this.rotation = newRotation;

                            const canvas = this.$refs.wheel;
                            canvas.style.transition = 'transform 4s cubic-bezier(0.17, 0.67, 0.12, 0.99)';
                            canvas.style.transform = `rotate(${newRotation}deg)`;

                            setTimeout(() => {
                                this.balance = data.new_balance;
                                this.freeSpins = data.free_spins_left;
                                this.message = data.message;
                                this.messageType = data.sector.type === 'bonus' || data.sector.type === 'free_spin' ? 'success' : 'super';
                                this.spinning = false;
                            }, 4100);
                        } catch (e) {
                            this.message = 'Не удалось связаться с сервером.';
                            this.messageType = 'error';
                            this.spinning = false;
                        }
                    }
                };
            }
        </script>
    @endpush
@endsection
