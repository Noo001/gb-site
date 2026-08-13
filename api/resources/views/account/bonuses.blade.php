@extends('account.layout')

@section('account_title', 'Бонусы')

@section('account_content')
    <h1 class="section-title">Бонусная программа</h1>

    <div class="bonus-balance-card" :class="{ 'pulse': $store?.bonusPulse }">
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

    <div
        class="bonus-roulette-section"
        x-data="bonusWheel(@js($sectors), @js($needsAccept), {{ $freeSpins }}, {{ $spinCost }}, {{ $balance }}, @js($freeSpinsEnabled))"
        x-init="initWheel()"
    >
        <h2 class="section-title" style="margin-top: 2rem; font-size: 1.25rem;">Колесо фортуны</h2>

        <div class="roulette-layout">
            <div class="roulette-wheel-wrap" :class="{ 'spinning': spinning }">
                <div class="roulette-wheel-glow"></div>
                <canvas x-ref="wheel" width="420" height="420" class="roulette-wheel"></canvas>
                <div class="roulette-pointer"></div>
            </div>

            <div class="roulette-controls">
                <p class="roulette-info">
                    Бесплатных прокруток: <strong x-text="freeSpins">{{ $freeSpins }}</strong>
                    @if (! $freeSpinsEnabled)
                        <span class="bonus-badge-muted">(отключены в админке)</span>
                    @endif
                </p>
                <p class="roulette-info">
                    Стоимость платной прокрутки: <strong>{{ number_format($spinCost, 0, ',', ' ') }} ₽</strong> бонусов
                </p>

                <div class="roulette-buttons">
                    @if ($freeSpinsEnabled)
                        <button
                            type="button"
                            class="btn btn-outline roulette-btn-free"
                            :disabled="spinning || needsAccept || freeSpins <= 0"
                            @click="spin(true)"
                        >
                            Бесплатная прокрутка
                        </button>
                    @endif
                    <button
                        type="button"
                        class="btn btn-primary roulette-btn-paid"
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

    <canvas id="confetti-canvas" class="confetti-canvas"></canvas>

    @push('scripts')
        <script>
            function bonusWheel(sectors, needsAccept, freeSpins, spinCost, balance, freeSpinsEnabled) {
                return {
                    sectors,
                    needsAccept,
                    freeSpins,
                    spinCost,
                    balance,
                    freeSpinsEnabled,
                    spinning: false,
                    message: '',
                    messageType: '',
                    rotation: 0,
                    colors: [
                        '#0cc0df', '#ff7b00', '#10b981', '#ef4444',
                        '#8b5cf6', '#f59e0b', '#3b82f6', '#ec4899',
                        '#6366f1', '#14b8a6', '#f43f5e', '#84cc16'
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
                        const cssSize = Math.min(420, canvas.clientWidth || 420);
                        canvas.width = cssSize * dpr;
                        canvas.height = cssSize * dpr;
                        canvas.style.width = cssSize + 'px';
                        canvas.style.height = cssSize + 'px';
                        ctx.scale(dpr, dpr);

                        const cx = cssSize / 2;
                        const cy = cssSize / 2;
                        const radius = cssSize / 2 - 12;

                        ctx.clearRect(0, 0, cssSize, cssSize);

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

                            ctx.save();
                            ctx.lineWidth = 2;
                            ctx.strokeStyle = 'rgba(255,255,255,0.5)';
                            ctx.stroke();
                            ctx.restore();

                            ctx.save();
                            ctx.translate(cx, cy);
                            ctx.rotate(start + angle / 2);
                            ctx.textAlign = 'right';
                            ctx.fillStyle = '#fff';
                            ctx.font = 'bold 13px Inter, sans-serif';
                            ctx.shadowColor = 'rgba(0,0,0,0.3)';
                            ctx.shadowBlur = 3;
                            ctx.fillText(this.truncate(this.sectors[i]?.label || '', 18), radius - 18, 5);
                            ctx.restore();
                        }

                        // Центральный круг
                        ctx.beginPath();
                        ctx.arc(cx, cy, 34, 0, Math.PI * 2);
                        ctx.fillStyle = '#fff';
                        ctx.fill();
                        ctx.lineWidth = 3;
                        ctx.strokeStyle = 'rgba(12, 192, 223, 0.4)';
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
                            const randomOffset = (Math.random() * (sectorAngle - 8)) - (sectorAngle - 8) / 2;
                            const targetAngle = index * sectorAngle + sectorAngle / 2 + randomOffset;

                            const extraSpins = 6 * 360;
                            const newRotation = this.rotation + extraSpins + (270 - targetAngle - (this.rotation % 360));
                            this.rotation = newRotation;

                            const canvas = this.$refs.wheel;
                            canvas.style.transition = 'transform 5s cubic-bezier(0.12, 0.68, 0.18, 0.99)';
                            canvas.style.transform = `rotate(${newRotation}deg)`;

                            setTimeout(() => {
                                this.balance = data.new_balance;
                                this.freeSpins = data.free_spins_left;
                                this.message = data.message;
                                const isWin = data.sector.type === 'bonus' || data.sector.type === 'free_spin';
                                this.messageType = isWin ? 'success' : 'super';
                                this.spinning = false;
                                if (isWin) {
                                    launchConfetti();
                                }
                            }, 5100);
                        } catch (e) {
                            this.message = 'Не удалось связаться с сервером.';
                            this.messageType = 'error';
                            this.spinning = false;
                        }
                    }
                };
            }

            function launchConfetti() {
                const canvas = document.getElementById('confetti-canvas');
                if (!canvas) return;
                const ctx = canvas.getContext('2d');
                canvas.width = window.innerWidth;
                canvas.height = window.innerHeight;
                canvas.style.opacity = '1';

                const colors = ['#0cc0df', '#ff7b00', '#10b981', '#ef4444', '#8b5cf6', '#f59e0b', '#ec4899'];
                const particles = [];
                const count = 160;

                for (let i = 0; i < count; i++) {
                    particles.push({
                        x: canvas.width / 2,
                        y: canvas.height / 2,
                        vx: (Math.random() - 0.5) * 24,
                        vy: (Math.random() - 1.2) * 24,
                        size: Math.random() * 6 + 3,
                        color: colors[Math.floor(Math.random() * colors.length)],
                        rotation: Math.random() * 360,
                        rotationSpeed: (Math.random() - 0.5) * 10,
                        gravity: 0.35,
                        drag: 0.96,
                        life: 1
                    });
                }

                let raf;
                function render() {
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                    let alive = false;
                    particles.forEach(p => {
                        if (p.life <= 0) return;
                        alive = true;
                        p.x += p.vx;
                        p.y += p.vy;
                        p.vy += p.gravity;
                        p.vx *= p.drag;
                        p.vy *= p.drag;
                        p.rotation += p.rotationSpeed;
                        p.life -= 0.012;

                        ctx.save();
                        ctx.translate(p.x, p.y);
                        ctx.rotate((p.rotation * Math.PI) / 180);
                        ctx.globalAlpha = Math.max(0, p.life);
                        ctx.fillStyle = p.color;
                        ctx.fillRect(-p.size / 2, -p.size / 2, p.size, p.size);
                        ctx.restore();
                    });

                    if (alive) {
                        raf = requestAnimationFrame(render);
                    } else {
                        ctx.clearRect(0, 0, canvas.width, canvas.height);
                        canvas.style.opacity = '0';
                    }
                }

                cancelAnimationFrame(raf);
                render();
            }

            window.addEventListener('resize', () => {
                const canvas = document.getElementById('confetti-canvas');
                if (canvas) {
                    canvas.width = window.innerWidth;
                    canvas.height = window.innerHeight;
                }
            });
        </script>
    @endpush
@endsection
