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
                    <div class="pc-wheel-scene">
                        <div class="pc-wheel-cylinder" x-ref="poleCylinder">
                            <div class="pc-wheel-top" x-ref="poleTop"></div>
                            <div class="pc-wheel-bottom" x-ref="poleBottom"></div>
                        </div>
                    </div>
                    <div class="pc-wheel-pointer">
                        <svg viewBox="0 0 100 100" fill="none">
                            <path d="M0 50L100 0V100L0 50Z" fill="#d90429"/>
                            <path d="M15 50L85 20V80L15 50Z" fill="#ef233c"/>
                            <circle cx="25" cy="50" r="6" fill="#fff"/>
                        </svg>
                    </div>
                </div>

                <!-- Marketplace / re:premium cards -->
                <div class="rp-stage" x-show="wheelVariant === 'market'" x-cloak>
                    <div class="rp-butterfly b1">🦋</div>
                    <div class="rp-butterfly b2">🦋</div>
                    <div class="rp-butterfly b3">🦋</div>
                    <div class="rp-header">
                        <div class="rp-badge">Летний технодроп</div>
                        <div class="rp-title">ПОЛЕ ПРИЗОВ<br><span>от Gadget Bar</span></div>
                    </div>
                    <div class="rp-cards-wrap">
                        <div class="rp-pointer"></div>
                        <div class="rp-cards-track" x-ref="marketTrack">
                            <template x-for="(sector, index) in marketCards" :key="index">
                                <div class="rp-card" :data-index="index % sectors.length" :class="{ 'active': marketActiveIndex === index % sectors.length }">
                                    <div class="rp-card-visual" :style="`--card-light: ${sector.light}; --card-dark: ${sector.dark};`">
                                        <span x-text="sector.icon"></span>
                                    </div>
                                    <div class="rp-card-label" x-text="sector.label"></div>
                                    <div class="rp-card-sub" x-text="sector.sub"></div>
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
                    neonColors: [
                        '#0cc0df', '#ff7b00', '#10b981', '#ef4444',
                        '#8b5cf6', '#f59e0b', '#3b82f6', '#ec4899'
                    ],
                    marketActiveIndex: 0,
                    marketActiveCard: 0,
                    marketOffset: 0,
                    marketCardWidth: 240,
                    marketCardRealWidth: 220,
                    marketVisibleCenter: 0,
                    marketCardTemplates: [
                        { icon: '📱', light: '#e0f2fe', dark: '#38bdf8' },
                        { icon: '🎧', light: '#fef3c7', dark: '#fbbf24' },
                        { icon: '⌚', light: '#fce7f3', dark: '#f472b6' },
                        { icon: '💻', light: '#dcfce7', dark: '#4ade80' },
                        { icon: '📷', light: '#ede9fe', dark: '#a78bfa' },
                        { icon: '🎮', light: '#ffedd5', dark: '#fb923c' },
                        { icon: '💰', light: '#ecfccb', dark: '#a3e635' },
                        { icon: '🏆', light: '#fef9c3', dark: '#facc15' },
                    ],

                    get marketCards() {
                        const list = [];
                        const templates = this.marketCardTemplates;
                        const count = this.sectors.length || 1;
                        for (let i = 0; i < 5 * count; i++) {
                            const s = this.sectors[i % count];
                            const t = templates[i % templates.length];
                            let sub = 'Приз';
                            if (s.type === 'free_spin') sub = 'Ещё попытка';
                            else if (s.type === 'bonus') sub = 'Бонус';
                            list.push({
                                label: this.truncate(s.label || s.name || '', 18),
                                sub: sub,
                                light: t.light,
                                dark: t.dark,
                                icon: t.icon,
                                index: i % count
                            });
                        }
                        return list;
                    },

                    init() {
                        this.$nextTick(() => {
                            this.measureMarket();
                            this.drawPoleWheel();
                            this.drawNeonWheel();
                            this.updateMarketCards();
                        });

                        window.addEventListener('resize', () => {
                            this.measureMarket();
                            this.drawPoleWheel();
                            this.drawNeonWheel();
                            if (this.wheelVariant === 'market') {
                                this.updateMarketCards();
                            }
                        });
                    },

                    measureMarket() {
                        const track = this.$refs.marketTrack;
                        const wrap = track?.parentElement;
                        const card = track?.querySelector('.rp-card');
                        if (!track || !wrap || !card) return;
                        const gap = parseFloat(getComputedStyle(track).gap) || 24;
                        const realWidth = card.offsetWidth;
                        this.marketCardRealWidth = realWidth;
                        this.marketCardWidth = realWidth + gap;
                        this.marketVisibleCenter = wrap.clientWidth / 2;
                    },

                    drawMarketWheel() {
                        this.$nextTick(() => {
                            this.measureMarket();
                            this.marketActiveIndex = 0;
                            this.marketActiveCard = 0;
                            const track = this.$refs.marketTrack;
                            if (!track) return;
                            const cardWidth = this.marketCardWidth;
                            const realWidth = this.marketCardRealWidth;
                            const visibleCenter = this.marketVisibleCenter || track.parentElement.clientWidth / 2;
                            this.marketOffset = visibleCenter - realWidth / 2;
                            track.style.transform = `translateX(${this.marketOffset}px)`;
                            this.updateMarketCards();
                        });
                    },

                    updateMarketCards() {
                        const track = this.$refs.marketTrack;
                        if (!track) return;
                        const cards = track.querySelectorAll('.rp-card');
                        const center = this.marketVisibleCenter;
                        const cardWidth = this.marketCardWidth;
                        const realWidth = this.marketCardRealWidth;
                        const count = this.sectors.length || 1;

                        if (!cardWidth || !realWidth) return;

                        let activeIndex = this.marketActiveIndex;
                        cards.forEach((card, i) => {
                            const cardLeft = this.marketOffset + i * cardWidth;
                            const cardCenter = cardLeft + realWidth / 2;
                            const dist = (cardCenter - center) / cardWidth;
                            const absDist = Math.min(3, Math.abs(dist));

                            const scale = absDist < 0.5 ? 1.12 : Math.max(0.78, 1 - absDist * 0.18);
                            const opacity = absDist < 0.5 ? 1 : Math.max(0.45, 1 - absDist * 0.45);
                            const blur = absDist < 0.5 ? 0 : Math.min(4, absDist * 2.5);

                            card.style.transform = `scale(${scale})`;
                            card.style.opacity = opacity;
                            card.style.filter = `blur(${blur}px)`;
                            card.style.zIndex = absDist < 0.5 ? 2 : 1;
                            const isActive = absDist < 0.5;
                            card.classList.toggle('active', isActive);
                            if (isActive) {
                                activeIndex = i % count;
                            }
                        });
                        this.marketActiveIndex = activeIndex;
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
                        const cylinder = this.$refs.poleCylinder;
                        const topEl = this.$refs.poleTop;
                        const bottomEl = this.$refs.poleBottom;
                        if (!cylinder || !topEl || !bottomEl) return;

                        cylinder.querySelectorAll('.pc-wheel-side').forEach(el => el.remove());

                        const count = this.sectors.length || 1;
                        const R = 400;
                        const H = 320;
                        const angle = 360 / count;
                        const sideW = 2 * R * Math.tan(Math.PI / count);

                        const palette = {
                            white: '#fff8e7', black: '#222', red: '#e81c3e', green: '#228b22', gold: '#e6b800'
                        };

                        const getClass = (i) => {
                            const p = i % 12;
                            if (p === 6) return 'red';
                            if (p === 8) return 'green';
                            if (p === 11) return 'gold';
                            return p % 2 === 0 ? 'white' : 'black';
                        };

                        this.sectors.forEach((s, i) => {
                            const div = document.createElement('div');
                            div.className = 'pc-wheel-side ' + getClass(i);
                            div.style.setProperty('--w', sideW + 'px');
                            div.style.setProperty('--h', H + 'px');
                            div.style.setProperty('--r', R + 'px');
                            div.style.setProperty('--a', (i * angle) + 'deg');
                            div.textContent = this.truncate(s.label || s.name || '', 12);
                            cylinder.appendChild(div);
                        });

                        topEl.style.setProperty('--d', (R * 2) + 'px');
                        topEl.style.setProperty('--h', H + 'px');
                        bottomEl.style.setProperty('--d', (R * 2) + 'px');
                        bottomEl.style.setProperty('--h', H + 'px');

                        const d = R * 2;
                        const cx = d / 2, cy = d / 2, r = d / 2 - 2;
                        const ns = 'http://www.w3.org/2000/svg';
                        const svg = document.createElementNS(ns, 'svg');
                        svg.setAttribute('width', '100%');
                        svg.setAttribute('height', '100%');
                        svg.setAttribute('viewBox', `0 0 ${d} ${d}`);

                        const makePath = (i) => {
                            const a1 = i * (Math.PI * 2) / count - Math.PI / 2;
                            const a2 = a1 + (Math.PI * 2) / count;
                            const x1 = cx + r * Math.cos(a1), y1 = cy + r * Math.sin(a1);
                            const x2 = cx + r * Math.cos(a2), y2 = cy + r * Math.sin(a2);
                            return `M ${cx} ${cy} L ${x1} ${y1} A ${r} ${r} 0 0 1 ${x2} ${y2} Z`;
                        };

                        this.sectors.forEach((s, i) => {
                            const path = document.createElementNS(ns, 'path');
                            path.setAttribute('d', makePath(i));
                            path.setAttribute('fill', palette[getClass(i)]);
                            path.setAttribute('stroke', 'rgba(255,255,255,0.15)');
                            path.setAttribute('stroke-width', '1');
                            svg.appendChild(path);
                        });

                        const ring = document.createElementNS(ns, 'circle');
                        ring.setAttribute('cx', cx);
                        ring.setAttribute('cy', cy);
                        ring.setAttribute('r', r * 0.12);
                        ring.setAttribute('fill', 'rgba(0,0,0,0.35)');
                        ring.setAttribute('stroke', 'rgba(255,255,255,0.25)');
                        ring.setAttribute('stroke-width', '3');
                        svg.appendChild(ring);

                        topEl.innerHTML = '';
                        bottomEl.innerHTML = '';
                        topEl.appendChild(svg);
                        bottomEl.appendChild(svg.cloneNode(true));
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

                        const extraSpins = 3 * 360;
                        const newRotation = this.rotation.pole + extraSpins + (targetAngle + 90 - (this.rotation.pole % 360));
                        this.rotation.pole = newRotation;

                        const cylinder = this.$refs.poleCylinder;
                        if (cylinder) {
                            cylinder.style.transform = `rotateX(18deg) scale(1.25) translateX(80px) rotateY(${-newRotation}deg)`;
                        }

                        setTimeout(() => this.finishSpin(data), 16000);
                    },

                    spinMarket(data) {
                        const index = this.sectors.findIndex(s => s.id === data.sector.id);
                        const count = this.sectors.length;
                        const loops = 5 + Math.floor(Math.random() * 2);
                        const cardWidth = this.marketCardWidth;
                        const realWidth = this.marketCardRealWidth;
                        const visibleCenter = this.marketVisibleCenter || (this.$refs.marketTrack?.parentElement?.clientWidth / 2) || 360;

                        const finalCardIndex = count * loops + index;
                        const finalOffset = visibleCenter - (finalCardIndex * cardWidth + realWidth / 2);
                        const startOffset = this.marketOffset;
                        const distance = finalOffset - startOffset;
                        const duration = 6500;
                        const start = performance.now();

                        const animate = (now) => {
                            const elapsed = now - start;
                            const t = Math.min(elapsed / duration, 1);
                            const eased = t === 1 ? 1 : 1 - Math.pow(2, -10 * t);
                            this.marketOffset = startOffset + distance * eased;
                            const track = this.$refs.marketTrack;
                            if (track) track.style.transform = `translateX(${this.marketOffset}px)`;
                            this.updateMarketCards();

                            if (t < 1) {
                                requestAnimationFrame(animate);
                            } else {
                                this.marketOffset = finalOffset;
                                if (track) track.style.transform = `translateX(${finalOffset}px)`;
                                this.marketActiveIndex = index;
                                this.$nextTick(() => {
                                    this.updateMarketCards();
                                    this.marketActiveIndex = index;
                                });
                                setTimeout(() => this.finishSpin(data), 350);
                            }
                        };
                        requestAnimationFrame(animate);
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
