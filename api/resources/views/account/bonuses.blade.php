@extends('account.layout')

@section('account_title', 'Бонусы')

@section('account_content')
    <div
        class="bonus-page"
        x-data="bonusPage({{ $balance }}, {{ $freeSpins }}, {{ $canCollectDaily ? 'true' : 'false' }}, {{ $spinCost }}, {{ $freeSpinsEnabled ? 'true' : 'false' }}, @js($sectors), @js($needsAccept))"
        x-init="init()"
    >
        <h1 class="section-title">Бонусная программа</h1>

        <div class="bonus-balance-card" :class="{ 'pulse': balancePulse }">
            <div class="bonus-balance-label">Баланс бонусов</div>
            <div class="bonus-balance-value"><span x-text="balanceFormatted">{{ number_format($balance, 0, ',', ' ') }}</span> <span class="bonus-currency">Б</span></div>
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
                    <p class="bonus-terms-accepted">
                        ✅ Условия приняты (версия {{ $user->accepted_bonus_terms_version ?? $terms->version }}).
                        <button type="button" class="bonus-terms-link" @click="showTerms = !showTerms">
                            <span x-show="!showTerms">Показать условия</span>
                            <span x-show="showTerms" x-cloak>Скрыть условия</span>
                        </button>
                    </p>
                    <div x-show="showTerms" x-cloak class="bonus-terms-content" style="margin-top: 0.75rem;">
                        {!! nl2br(e($terms->content)) !!}
                    </div>
                @endif
            </div>
        @endif

        <div class="bonus-actions">
            <div class="bonus-action-card">
                <h3 class="bonus-action-title">Ежедневный сбор</h3>
                <p class="bonus-action-text">Заходите каждый день и получайте бонусы. Каждые 7 дней подряд — повышенная награда.</p>
                <template x-if="canCollectDaily">
                    <div>
                        <button
                            type="button"
                            class="btn btn-primary"
                            :disabled="dailyCollecting"
                            @click="collectDaily()"
                        >
                            <span x-show="!dailyCollecting">Собрать бонусы</span>
                            <span x-show="dailyCollecting" x-cloak>Собираем…</span>
                        </button>
                        <template x-if="dailyMessage">
                            <div class="roulette-message" :class="dailyMessageType" x-text="dailyMessage" style="margin-top: 0.75rem;"></div>
                        </template>
                    </div>
                </template>
                <template x-if="!canCollectDaily">
                    <button class="btn btn-outline" disabled>Собрано сегодня, возвращайтесь завтра</button>
                </template>
            </div>
        </div>

        <div class="bonus-roulette-section">
            <h2 class="section-title" style="margin-top: 2rem; font-size: 1.25rem;">Колесо фортуны</h2>

            <div class="wheel-tabs" role="tablist">
                <button
                    type="button"
                    class="wheel-tab"
                    :class="{ 'active': wheelVariant === 'pole' }"
                    @click="switchVariant('pole')"
                    :disabled="spinning"
                >Поле чудес 3D</button>
                <button
                    type="button"
                    class="wheel-tab"
                    :class="{ 'active': wheelVariant === 'market' }"
                    @click="switchVariant('market')"
                    :disabled="spinning"
                >Маркетплейс</button>
                <button
                    type="button"
                    class="wheel-tab"
                    :class="{ 'active': wheelVariant === 'neon' }"
                    @click="switchVariant('neon')"
                    :disabled="spinning"
                >Neon Fusion</button>
            </div>

            <div class="roulette-layout">
                <!-- Pole Chudes 3D -->
                <div class="pc-wheel-stage" x-show="wheelVariant === 'pole'" x-cloak>
                    <div class="pc-wheel-spotlight"></div>
                    <div class="pc-wheel-3d" x-ref="poleWheel" :class="{ 'spinning': spinning }">
                        <svg class="pc-wheel-svg" :view-box.camel="wheelViewBox" x-ref="poleSvg">
                            <defs>
                                <radialGradient id="pcWheelGold" cx="50%" cy="50%" r="50%">
                                    <stop offset="0%" stop-color="#fff8db"/>
                                    <stop offset="35%" stop-color="#e5c86c"/>
                                    <stop offset="100%" stop-color="#6b5410"/>
                                </radialGradient>
                            </defs>
                            <circle :cx="wheelSize/2" :cy="wheelSize/2" :r="wheelSize/2 - 4" fill="url(#pcWheelGold)" opacity="0.95"/>
                        </svg>
                        <div class="pc-wheel-hub">GO</div>
                        <div class="pc-wheel-pointer">
                            <svg viewBox="0 0 130 130" fill="none">
                                <path d="M130 65L15 0V130L130 65Z" fill="url(#pcWheelPointerGrad)"/>
                                <defs>
                                    <linearGradient id="pcWheelPointerGrad" x1="130" y1="65" x2="15" y2="65">
                                        <stop stop-color="#ffd700"/>
                                        <stop offset="1" stop-color="#ff3d00"/>
                                    </linearGradient>
                                </defs>
                                <circle cx="88" cy="65" r="8" fill="#fff" stroke="#ff3d00" stroke-width="2"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Marketplace -->
                <div class="market-wheel-stage" x-show="wheelVariant === 'market'" x-cloak>
                    <div class="market-wheel-rim"></div>
                    <canvas x-ref="marketWheel" width="420" height="420" class="market-wheel"></canvas>
                    <div class="market-wheel-center">GO</div>
                    <div class="market-pointer">
                        <svg viewBox="0 0 44 60" fill="none">
                            <path d="M22 0L44 48H0L22 0Z" fill="#ff3d00"/>
                            <path d="M22 48L14 60H30L22 48Z" fill="#ffd700"/>
                            <circle cx="22" cy="18" r="5" fill="#fff"/>
                        </svg>
                    </div>
                </div>

                <!-- Neon -->
                <div class="neon-wheel-stage" x-show="wheelVariant === 'neon'" x-cloak>
                    <div class="neon-bg-grid"></div>
                    <div class="neon-glow-ring"></div>
                    <div class="neon-outer-ring"></div>
                    <div class="neon-wheel-bg"></div>
                    <canvas x-ref="neonWheel" width="420" height="420" class="neon-wheel"></canvas>
                    <div class="neon-center-hub">SPIN</div>
                    <div class="neon-pointer">
                        <svg viewBox="0 0 40 56" fill="none">
                            <path d="M20 56L0 16H40L20 56Z" fill="#ff7b00"/>
                            <path d="M20 0L0 16H40L20 0Z" fill="#fff"/>
                        </svg>
                    </div>
                </div>

                <div class="roulette-controls">
                    <template x-if="freeSpinsEnabled">
                        <p class="roulette-info">
                            Бесплатных прокруток: <strong x-text="freeSpins">{{ $freeSpins }}</strong>
                        </p>
                    </template>
                    <p class="roulette-info">
                        Стоимость платной прокрутки: <strong>{{ number_format($spinCost, 0, ',', ' ') }}</strong> бонусов
                    </p>

                    <div class="roulette-buttons">
                        <template x-if="freeSpinsEnabled">
                            <button
                                type="button"
                                class="btn btn-outline roulette-btn-free"
                                :disabled="spinning || needsAccept || freeSpins <= 0"
                                @click="spin(true)"
                            >
                                Бесплатная прокрутка
                            </button>
                        </template>
                        <button
                            type="button"
                            class="btn btn-primary roulette-btn-paid"
                            :disabled="spinning || needsAccept || balance < spinCost"
                            @click="spin(false)"
                        >
                            Прокрутить за {{ number_format($spinCost, 0, ',', ' ') }} бонусов
                        </button>
                    </div>

                    <template x-if="message">
                        <div class="roulette-message" :class="messageType" x-text="message"></div>
                    </template>

                    <template x-if="freeSpinsEnabled && !needsAccept && freeSpins <= 0">
                        <p class="bonus-notice">Бесплатных прокруток пока нет. Получите их за ежедневный сбор или выиграйте в рулетке.</p>
                    </template>

                    <template x-if="!needsAccept && balance < spinCost">
                        <p class="bonus-notice">Недостаточно бонусов для платной прокрутки. Соберите ежедневный бонус или дождитесь начислений за покупки.</p>
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
                                    {{ $operation->amount >= 0 ? '+' : '' }}{{ number_format($operation->amount, 0, ',', ' ') }} бонусов
                                </td>
                                <td>{{ number_format($operation->balance_after, 0, ',', ' ') }} бонусов</td>
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
    </div>

    <canvas id="confetti-canvas" class="confetti-canvas"></canvas>

    @push('scripts')
        <script>
            function bonusPage(initialBalance, initialFreeSpins, canCollectDaily, spinCost, freeSpinsEnabled, sectors, needsAccept) {
                return {
                    // general
                    balance: initialBalance,
                    freeSpins: initialFreeSpins,
                    canCollectDaily: canCollectDaily,
                    spinCost: spinCost,
                    freeSpinsEnabled: freeSpinsEnabled,
                    showTerms: false,
                    dailyCollecting: false,
                    dailyMessage: '',
                    dailyMessageType: '',
                    balancePulse: false,
                    get balanceFormatted() {
                        return this.balance.toLocaleString('ru-RU');
                    },

                    // wheel
                    sectors,
                    needsAccept,
                    spinning: false,
                    message: '',
                    messageType: '',
                    wheelVariant: 'pole',
                    rotation: { pole: 0, market: 0, neon: 0 },
                    wheelSize: 420,
                    wheelViewBox: '0 0 420 420',
                    sectorColors: [
                        '#111111', '#f5f0e1', '#111111', '#f5f0e1',
                        '#c41e3a', '#f5f0e1', '#111111', '#f5f0e1',
                        '#111111', '#f5f0e1', '#2e8b57', '#f5f0e1'
                    ],
                    marketColors: [
                        '#0cc0df', '#ff7b00', '#10b981', '#ef4444',
                        '#8b5cf6', '#f59e0b', '#3b82f6', '#ec4899',
                        '#6366f1', '#14b8a6', '#f43f5e', '#84cc16'
                    ],
                    neonColors: [
                        '#0cc0df', '#ff7b00', '#10b981', '#ef4444',
                        '#8b5cf6', '#f59e0b', '#3b82f6', '#ec4899'
                    ],

                    init() {
                        this.$nextTick(() => {
                            this.drawPoleWheel();
                            this.drawMarketWheel();
                            this.drawNeonWheel();
                        });
                    },

                    switchVariant(variant) {
                        if (this.spinning) return;
                        this.wheelVariant = variant;
                        this.message = '';
                    },

                    async collectDaily() {
                        if (this.dailyCollecting) return;
                        this.dailyCollecting = true;
                        this.dailyMessage = '';

                        try {
                            const response = await fetch('{{ route('account.bonuses.daily') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({})
                            });

                            const data = await response.json();

                            if (!response.ok || data.error) {
                                this.dailyMessage = data.error || 'Ошибка сбора';
                                this.dailyMessageType = 'error';
                                this.dailyCollecting = false;
                                return;
                            }

                            this.balance = data.new_balance;
                            this.freeSpins = data.free_spins_available;
                            this.canCollectDaily = data.can_collect_daily;
                            this.dailyMessage = data.message;
                            this.dailyMessageType = 'success';
                            this.balancePulse = true;
                            setTimeout(() => this.balancePulse = false, 800);
                        } catch (e) {
                            this.dailyMessage = 'Не удалось связаться с сервером.';
                            this.dailyMessageType = 'error';
                        } finally {
                            this.dailyCollecting = false;
                        }
                    },

                    drawPoleWheel() {
                        const svg = this.$refs.poleSvg;
                        if (!svg) return;

                        // Clear previously generated sectors/text
                        svg.querySelectorAll('.pc-wheel-sector, .pc-wheel-text').forEach(el => el.remove());

                        const size = this.wheelSize;
                        const cx = size / 2;
                        const cy = size / 2;
                        const rOut = size / 2 - 8;
                        const rIn = 55;
                        const count = this.sectors.length || 1;
                        const angle = (Math.PI * 2) / count;

                        const makePath = (i) => {
                            const start = i * angle - Math.PI / 2;
                            const end = start + angle;
                            const x1 = cx + rIn * Math.cos(start);
                            const y1 = cy + rIn * Math.sin(start);
                            const x2 = cx + rOut * Math.cos(start);
                            const y2 = cy + rOut * Math.sin(start);
                            const x3 = cx + rOut * Math.cos(end);
                            const y3 = cy + rOut * Math.sin(end);
                            const x4 = cx + rIn * Math.cos(end);
                            const y4 = cy + rIn * Math.sin(end);
                            return `M ${x1} ${y1} L ${x2} ${y2} A ${rOut} ${rOut} 0 0 1 ${x3} ${y3} L ${x4} ${y4} A ${rIn} ${rIn} 0 0 0 ${x1} ${y1} Z`;
                        };

                        const ns = 'http://www.w3.org/2000/svg';
                        this.sectors.forEach((s, i) => {
                            const path = document.createElementNS(ns, 'path');
                            path.setAttribute('d', makePath(i));
                            path.setAttribute('fill', this.sectorColors[i % this.sectorColors.length]);
                            path.setAttribute('class', 'pc-wheel-sector');
                            svg.appendChild(path);

                            const mid = i * angle + angle / 2 - Math.PI / 2;
                            const tr = (rIn + rOut) / 2 + 2;
                            const tx = cx + tr * Math.cos(mid);
                            const ty = cy + tr * Math.sin(mid);

                            let textAngle = (mid * 180 / Math.PI) + 90;
                            const normMid = ((mid + Math.PI * 2.5) % (Math.PI * 2));
                            if (normMid > Math.PI / 2 && normMid < 3 * Math.PI / 2) {
                                textAngle += 180;
                            }

                            const text = document.createElementNS(ns, 'text');
                            text.setAttribute('x', tx);
                            text.setAttribute('y', ty);
                            text.setAttribute('class', 'pc-wheel-text');
                            text.setAttribute('fill', this.sectorColors[i % this.sectorColors.length] === '#f5f0e1' ? '#111' : '#fff');
                            text.setAttribute('transform', `rotate(${textAngle}, ${tx}, ${ty})`);
                            text.textContent = this.truncate(s.label || s.name || '', 16);
                            svg.appendChild(text);
                        });
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

                            // Баланс и оставшиеся бесплатные попытки обновляем сразу,
                            // без ожидания анимации, чтобы пользователь видел актуальное
                            // количество бонусов.
                            this.balance = data.new_balance;
                            this.freeSpins = data.free_spins_left;
                            this.balancePulse = true;
                            setTimeout(() => this.balancePulse = false, 800);

                            if (this.wheelVariant === 'pole') {
                                this.spinPole(data);
                            } else if (this.wheelVariant === 'market') {
                                this.spinMarket(data);
                            } else {
                                this.spinNeon(data);
                            }
                        } catch (e) {
                            this.message = 'Не удалось связаться с сервером.';
                            this.messageType = 'error';
                            this.spinning = false;
                        }
                    },

                    finishSpin(data) {
                        this.message = data.message;
                        const isWin = data.sector.type === 'bonus' || data.sector.type === 'free_spin';
                        this.messageType = isWin ? 'success' : 'super';
                        this.spinning = false;
                        if (isWin) {
                            launchConfetti();
                        }
                    },

                    spinPole(data) {
                        const index = this.sectors.findIndex(s => s.id === data.sector.id);
                        const count = this.sectors.length;
                        const sectorAngle = 360 / count;
                        const randomOffset = (Math.random() * (sectorAngle - 8)) - (sectorAngle - 8) / 2;
                        const targetAngle = index * sectorAngle + sectorAngle / 2 + randomOffset;

                        const extraSpins = 6 * 360;
                        const newRotation = this.rotation.pole + extraSpins + (90 - targetAngle - (this.rotation.pole % 360));
                        this.rotation.pole = newRotation;

                        const wheelEl = this.$refs.poleWheel;
                        wheelEl.style.transform = `rotateX(62deg) rotateZ(28deg) rotateY(-6deg) rotateZ(${newRotation}deg)`;

                        setTimeout(() => this.finishSpin(data), 7000);
                    },

                    spinMarket(data) {
                        const index = this.sectors.findIndex(s => s.id === data.sector.id);
                        const count = this.sectors.length;
                        const sectorAngle = 360 / count;
                        const randomOffset = (Math.random() * (sectorAngle - 8)) - (sectorAngle - 8) / 2;
                        const targetAngle = index * sectorAngle + sectorAngle / 2 + randomOffset;

                        const extraSpins = 6 * 360;
                        const newRotation = this.rotation.market + extraSpins + (270 - targetAngle - (this.rotation.market % 360));
                        this.rotation.market = newRotation;

                        const canvas = this.$refs.marketWheel;
                        canvas.style.transition = 'transform 5s cubic-bezier(0.12, 0.68, 0.18, 0.99)';
                        canvas.style.transform = `rotate(${newRotation}deg)`;

                        setTimeout(() => this.finishSpin(data), 5100);
                    },

                    spinNeon(data) {
                        const index = this.sectors.findIndex(s => s.id === data.sector.id);
                        const count = this.sectors.length;
                        const sectorAngle = 360 / count;
                        const randomOffset = (Math.random() * (sectorAngle - 8)) - (sectorAngle - 8) / 2;
                        const targetAngle = index * sectorAngle + sectorAngle / 2 + randomOffset;

                        const extraSpins = 6 * 360;
                        const newRotation = this.rotation.neon + extraSpins + (270 - targetAngle - (this.rotation.neon % 360));
                        this.rotation.neon = newRotation;

                        const canvas = this.$refs.neonWheel;
                        canvas.style.transition = 'transform 5.5s cubic-bezier(0.12, 0.75, 0.18, 1)';
                        canvas.style.transform = `rotate(${newRotation}deg)`;

                        setTimeout(() => this.finishSpin(data), 5500);
                    },

                    drawMarketWheel() {
                        const canvas = this.$refs.marketWheel;
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
                        const radius = cssSize / 2 - 16;

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

                            const grad = ctx.createRadialGradient(cx, cy, 0, cx, cy, radius);
                            const base = this.marketColors[i % this.marketColors.length];
                            grad.addColorStop(0, base);
                            grad.addColorStop(1, this.darken(base, 30));
                            ctx.fillStyle = grad;
                            ctx.fill();

                            ctx.save();
                            ctx.lineWidth = 2;
                            ctx.strokeStyle = 'rgba(255,255,255,0.45)';
                            ctx.stroke();
                            ctx.restore();

                            ctx.save();
                            ctx.translate(cx, cy);
                            ctx.rotate(start + angle / 2);
                            ctx.textAlign = 'right';
                            ctx.fillStyle = '#fff';
                            ctx.font = 'bold 13px Inter, sans-serif';
                            ctx.shadowColor = 'rgba(0,0,0,0.35)';
                            ctx.shadowBlur = 4;
                            ctx.fillText(this.truncate(this.sectors[i]?.label || '', 18), radius - 18, 5);
                            ctx.restore();
                        }
                    },

                    drawNeonWheel() {
                        const canvas = this.$refs.neonWheel;
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
                        const radius = cssSize / 2 - 16;

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

                            const grad = ctx.createRadialGradient(cx, cy, 0, cx, cy, radius);
                            const base = this.neonColors[i % this.neonColors.length];
                            grad.addColorStop(0, base + '55');
                            grad.addColorStop(0.6, base + 'aa');
                            grad.addColorStop(1, base);
                            ctx.fillStyle = grad;
                            ctx.fill();

                            ctx.save();
                            ctx.strokeStyle = 'rgba(255,255,255,0.35)';
                            ctx.lineWidth = 2;
                            ctx.beginPath();
                            ctx.moveTo(cx, cy);
                            ctx.arc(cx, cy, radius, start, end);
                            ctx.stroke();
                            ctx.restore();

                            ctx.save();
                            ctx.translate(cx, cy);
                            ctx.rotate(start + angle / 2);
                            ctx.textAlign = 'right';
                            ctx.fillStyle = '#fff';
                            ctx.font = 'bold 15px Inter, sans-serif';
                            ctx.shadowColor = base;
                            ctx.shadowBlur = 12;
                            ctx.fillText(this.truncate(this.sectors[i]?.label || '', 16), radius - 22, 5);
                            ctx.restore();
                        }
                    },

                    darken(hex, amount) {
                        const num = parseInt(hex.slice(1), 16);
                        let r = Math.max(0, (num >> 16) - amount);
                        let g = Math.max(0, ((num >> 8) & 255) - amount);
                        let b = Math.max(0, (num & 255) - amount);
                        return `rgb(${r},${g},${b})`;
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

                const colors = ['#0cc0df', '#ff7b00', '#10b981', '#ef4444', '#8b5cf6', '#f59e0b', '#ec4899', '#ffffff', '#ffd700'];
                const particles = [];
                const count = 250;
                const centerX = canvas.width / 2;
                const centerY = canvas.height / 2;

                function randomBurstAngle() {
                    const base = Math.PI / 2;
                    return base + (Math.random() - 0.5) * 2.5;
                }

                for (let i = 0; i < count; i++) {
                    const angle = randomBurstAngle();
                    const speed = Math.random() * 18 + 6;
                    const shapeType = Math.random() > 0.5 ? 'star' : 'circle';
                    particles.push({
                        x: centerX,
                        y: centerY,
                        vx: Math.cos(angle) * speed,
                        vy: Math.sin(angle) * speed,
                        size: Math.random() * 7 + 3,
                        color: colors[Math.floor(Math.random() * colors.length)],
                        rotation: Math.random() * 360,
                        rotationSpeed: (Math.random() - 0.5) * 14,
                        gravity: 0.45,
                        drag: 0.965,
                        life: 1,
                        decay: 0.008 + Math.random() * 0.01,
                        shapeType,
                        twinkle: Math.random() > 0.7
                    });
                }

                let raf;
                function drawParticle(p) {
                    ctx.save();
                    ctx.translate(p.x, p.y);
                    ctx.rotate((p.rotation * Math.PI) / 180);
                    ctx.globalAlpha = Math.max(0, p.life);
                    ctx.fillStyle = p.color;

                    if (p.shapeType === 'star') {
                        const spikes = 5;
                        const outer = p.size;
                        const inner = p.size * 0.45;
                        ctx.beginPath();
                        for (let i = 0; i < spikes * 2; i++) {
                            const r = i % 2 === 0 ? outer : inner;
                            const a = (Math.PI / spikes) * i;
                            if (i === 0) ctx.moveTo(Math.cos(a) * r, Math.sin(a) * r);
                            else ctx.lineTo(Math.cos(a) * r, Math.sin(a) * r);
                        }
                        ctx.closePath();
                        ctx.fill();
                    } else if (p.twinkle) {
                        ctx.beginPath();
                        ctx.arc(0, 0, p.size * 1.3, 0, Math.PI * 2);
                        ctx.fillStyle = 'rgba(255,255,255,' + Math.max(0, p.life * 0.6) + ')';
                        ctx.fill();
                        ctx.beginPath();
                        ctx.arc(0, 0, p.size, 0, Math.PI * 2);
                        ctx.fillStyle = p.color;
                        ctx.globalAlpha = Math.max(0, p.life);
                        ctx.fill();
                    } else {
                        ctx.beginPath();
                        ctx.arc(0, 0, p.size, 0, Math.PI * 2);
                        ctx.fill();
                    }

                    ctx.restore();
                }

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
                        p.life -= p.decay;
                        drawParticle(p);
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
