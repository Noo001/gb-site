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
                    <div class="pc-wheel-wrap" x-ref="poleWheel" :class="{ 'spinning': spinning }">
                        <svg class="pc-wheel-svg" :view-box.camel="wheelViewBox" x-ref="poleSvg">
                            <defs>
                                <radialGradient id="pcWheelCardboard" cx="50%" cy="50%" r="50%">
                                    <stop offset="0%" stop-color="#4a3b2a"/>
                                    <stop offset="100%" stop-color="#2a1f15"/>
                                </radialGradient>
                            </defs>
                            <circle :cx="wheelSize/2" :cy="wheelSize/2" :r="wheelSize/2 - 4" fill="url(#pcWheelCardboard)" stroke="#5a4a3a" stroke-width="2"/>
                        </svg>
                        <div class="pc-wheel-hub"></div>
                    </div>
                    <div class="pc-wheel-pointer">
                        <svg viewBox="0 0 100 100" fill="none">
                            <path d="M100 50L0 0V100L100 50Z" fill="#d90429"/>
                            <path d="M85 50L15 20V80L85 50Z" fill="#ef233c"/>
                            <circle cx="75" cy="50" r="6" fill="#fff"/>
                        </svg>
                    </div>
                </div>

                <!-- Marketplace / re:premium cards -->
                <div class="rp-stage" x-show="wheelVariant === 'market'" x-cloak>
                    <div class="rp-header">
                        <div class="rp-logo">ТЕХНОДРОП</div>
                        <p class="rp-subtitle">Gadget Bar</p>
                    </div>
                    <div class="rp-cards-wrap">
                        <div class="rp-pointer"></div>
                        <div class="rp-cards-track" x-ref="marketTrack">
                            <template x-for="(sector, index) in marketCards" :key="index">
                                <div class="rp-card" :class="{ 'active': marketActiveIndex === index % sectors.length }">
                                    <div class="rp-card-visual" :style="`background:${sector.bg}`">
                                        <svg viewBox="0 0 100 100" fill="none" x-html="sector.icon"></svg>
                                    </div>
                                    <div class="rp-card-label" x-text="sector.label"></div>
                                </div>
                            </template>
                        </div>
                    </div>
                    <div class="rp-dots">
                        <template x-for="(sector, index) in sectors" :key="index">
                            <div class="rp-dot" :class="{ 'active': marketActiveIndex === index }"></div>
                        </template>
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
                    rotation: { pole: 0, neon: 0 },
                    wheelSize: 460,
                    wheelViewBox: '0 0 460 460',
                    sectorColors: [
                        '#f5f0e1', '#1a1a1a', '#f5f0e1', '#1a1a1a',
                        '#f5f0e1', '#1a1a1a', '#d90429', '#1a1a1a',
                        '#f5f0e1', '#1a1a1a', '#f5f0e1', '#1a1a1a'
                    ],
                    neonColors: [
                        '#0cc0df', '#ff7b00', '#10b981', '#ef4444',
                        '#8b5cf6', '#f59e0b', '#3b82f6', '#ec4899'
                    ],
                    marketActiveIndex: 0,
                    marketOffset: 0,
                    marketCardWidth: 240,
                    marketVisibleCenter: 0,
                    marketCardTemplates: [
                        { bg: '#dbeafe', icon: '<rect x="30" y="10" width="40" height="80" rx="8" fill="#1e3a8a"/><rect x="35" y="18" width="30" height="60" rx="3" fill="#dbeafe"/><circle cx="50" cy="85" r="4" fill="#fff"/>' },
                        { bg: '#dcfce7', icon: '<rect x="15" y="15" width="70" height="70" rx="10" fill="#1e3a8a"/><rect x="22" y="22" width="56" height="56" rx="6" fill="#dbeafe"/><circle cx="50" cy="80" r="3" fill="#fff"/>' },
                        { bg: '#fce7f3', icon: '<path d="M50 25c-16 0-29 13-29 29v16a6 6 0 0 0 6 6h6a6 6 0 0 0 6-6v-12a6 6 0 0 0-6-6h-6c0-14 11-25 25-25s25 11 25 25h-6a6 6 0 0 0-6 6v12a6 6 0 0 0 6 6h6a6 6 0 0 0 6-6v-16c0-16-13-29-29-29z" fill="#1e3a8a"/><rect x="27" y="54" width="12" height="22" rx="6" fill="#2563eb"/><rect x="61" y="54" width="12" height="22" rx="6" fill="#2563eb"/>' },
                        { bg: '#fef3c7', icon: '<rect x="35" y="20" width="30" height="60" rx="10" fill="#1e3a8a"/><rect x="40" y="28" width="20" height="44" rx="6" fill="#dbeafe"/><path d="M35 30h-5a5 5 0 0 0-5 5v30a5 5 0 0 0 5 5h5M65 30h5a5 5 0 0 1 5 5v30a5 5 0 0 1-5 5h-5" stroke="#1e3a8a" stroke-width="4"/>' },
                        { bg: '#f3e8ff', icon: '<rect x="15" y="20" width="70" height="50" rx="6" fill="#1e3a8a"/><rect x="20" y="25" width="60" height="40" rx="3" fill="#dbeafe"/><path d="M10 70h80l-5 10H15l-5-10z" fill="#1e3a8a"/>' },
                        { bg: '#e0f2fe', icon: '<rect x="20" y="30" width="60" height="40" rx="8" fill="#1e3a8a"/><circle cx="50" cy="50" r="14" fill="#dbeafe" stroke="#fff" stroke-width="3"/><circle cx="50" cy="50" r="7" fill="#1e3a8a"/><circle cx="72" cy="38" r="4" fill="#fff"/>' }
                    ],

                    get marketCards() {
                        const list = [];
                        const templates = this.marketCardTemplates;
                        for (let i = 0; i < 5 * this.sectors.length; i++) {
                            const s = this.sectors[i % this.sectors.length];
                            const t = templates[i % templates.length];
                            list.push({ label: this.truncate(s.label || s.name || '', 18), bg: t.bg, icon: t.icon });
                        }
                        return list;
                    },

                    init() {
                        this.$nextTick(() => {
                            this.measureMarket();
                            this.drawPoleWheel();
                            this.drawNeonWheel();
                        });
                    },

                    measureMarket() {
                        const track = this.$refs.marketTrack;
                        const wrap = track?.parentElement;
                        const card = track?.querySelector('.rp-card');
                        if (!track || !wrap || !card) return;
                        const gap = parseFloat(getComputedStyle(track).gap) || 20;
                        this.marketCardWidth = card.getBoundingClientRect().width + gap;
                        this.marketVisibleCenter = wrap.clientWidth / 2;
                    },

                    drawMarketWheel() {
                        this.$nextTick(() => {
                            this.measureMarket();
                            this.marketActiveIndex = 0;
                            this.marketOffset = 0;
                            const track = this.$refs.marketTrack;
                            if (track) track.style.transform = 'translateX(0px)';
                        });
                    },

                    switchVariant(variant) {
                        if (this.spinning) return;
                        this.wheelVariant = variant;
                        this.message = '';
                        if (variant === 'market') {
                            this.drawMarketWheel();
                        }
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

                        svg.querySelectorAll('.pc-wheel-sector, .pc-wheel-text').forEach(el => el.remove());

                        const size = this.wheelSize;
                        const cx = size / 2;
                        const cy = size / 2;
                        const rOut = size / 2 - 10;
                        const rIn = 45;
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
                            const color = this.sectorColors[i % this.sectorColors.length];
                            const path = document.createElementNS(ns, 'path');
                            path.setAttribute('d', makePath(i));
                            path.setAttribute('fill', color);
                            path.setAttribute('class', 'pc-wheel-sector');
                            svg.appendChild(path);

                            const mid = i * angle + angle / 2 - Math.PI / 2;
                            const tr = (rIn + rOut) / 2 + 6;
                            const tx = cx + tr * Math.cos(mid);
                            const ty = cy + tr * Math.sin(mid);

                            let textAngle = (mid * 180 / Math.PI) + 90;
                            const normMid = ((mid + Math.PI * 2.5) % (Math.PI * 2));
                            if (normMid > Math.PI / 2 && normMid < 3 * Math.PI / 2) {
                                textAngle += 180;
                            }

                            const label = (s.label || s.name || '').toString();
                            const maxLine = 12;
                            const lines = [];
                            for (let i = 0; i < label.length; i += maxLine) {
                                lines.push(label.slice(i, i + maxLine));
                            }
                            if (lines.length === 0) lines.push('');

                            const text = document.createElementNS(ns, 'text');
                            text.setAttribute('x', tx);
                            text.setAttribute('y', ty);
                            text.setAttribute('class', 'pc-wheel-text');
                            text.setAttribute('fill', color === '#f5f0e1' ? '#1a1a1a' : '#f5f0e1');
                            text.setAttribute('transform', `rotate(${textAngle}, ${tx}, ${ty})`);

                            lines.forEach((line, idx) => {
                                const tspan = document.createElementNS(ns, 'tspan');
                                tspan.setAttribute('x', tx);
                                tspan.setAttribute('dy', idx === 0 ? '0' : '1.05em');
                                tspan.textContent = line;
                                text.appendChild(tspan);
                            });

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

                        const extraSpins = 5 * 360;
                        const newRotation = this.rotation.pole + extraSpins + (270 - targetAngle - (this.rotation.pole % 360));
                        this.rotation.pole = newRotation;

                        const wheelEl = this.$refs.poleWheel;
                        wheelEl.style.transform = `translate(-50%, -50%) rotateX(28deg) rotateY(-12deg) rotateZ(8deg) rotateZ(${newRotation}deg)`;

                        setTimeout(() => this.finishSpin(data), 6500);
                    },

                    spinMarket(data) {
                        const index = this.sectors.findIndex(s => s.id === data.sector.id);
                        const loops = 3 + Math.floor(Math.random() * 2);
                        const cardWidth = this.marketCardWidth;
                        const visibleCenter = this.marketVisibleCenter || (this.$refs.marketTrack?.parentElement?.clientWidth / 2) || 360;
                        const finalOffset = -(this.sectors.length * loops + index) * cardWidth + visibleCenter - cardWidth / 2;
                        const startOffset = this.marketOffset;
                        const distance = finalOffset - startOffset;
                        const duration = 4000;
                        const start = performance.now();

                        const animate = (now) => {
                            const elapsed = now - start;
                            const t = Math.min(elapsed / duration, 1);
                            const eased = 1 - Math.pow(1 - t, 3);
                            this.marketOffset = startOffset + distance * eased;
                            const track = this.$refs.marketTrack;
                            if (track) track.style.transform = `translateX(${this.marketOffset}px)`;

                            const center = -this.marketOffset + visibleCenter;
                            const idx = Math.round((center - cardWidth / 2) / cardWidth) % this.sectors.length;
                            const normalizedIdx = (idx + this.sectors.length) % this.sectors.length;
                            if (normalizedIdx !== this.marketActiveIndex) {
                                this.marketActiveIndex = normalizedIdx;
                            }

                            if (t < 1) {
                                requestAnimationFrame(animate);
                            } else {
                                this.finishSpin(data);
                            }
                        };
                        requestAnimationFrame(animate);
                    },

                    drawMarketWheel() {
                        // cards are rendered by Alpine x-for; just reset active state
                        this.marketActiveIndex = 0;
                        this.marketOffset = 0;
                        const track = this.$refs.marketTrack;
                        if (track) track.style.transform = 'translateX(0px)';
                        this.$nextTick(() => this.measureMarket());
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
