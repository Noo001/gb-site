@extends('account.layout')

@section('account_title', 'Бонусы')

@section('account_content')
    <div
        class="bonus-page"
        x-data="bonusPage({{ $balance }}, {{ $freeSpins }}, {{ $canCollectDaily ? 'true' : 'false' }}, {{ $spinCost }}, {{ $freeSpinsEnabled ? 'true' : 'false' }}, @js($sectors), @js($needsAccept), @js($weekCollectData))"
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

                <div class="daily-week">
                    <template x-for="(day, index) in weekCollectData" :key="day.date">
                        <div class="daily-day" :class="{
                            'collected': day.is_collected,
                            'today': day.is_today,
                            'can-collect': day.can_collect,
                            'past-missed': day.is_past && !day.is_collected,
                            'future': !day.is_past && !day.is_today
                        }">
                            <div class="daily-day-number" x-text="'День ' + day.day_number"></div>
                            <div class="daily-day-dot">
                                <svg x-show="day.is_collected" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                <span x-show="!day.is_collected" x-text="day.day_number"></span>
                            </div>
                            <div class="daily-day-label" x-text="day.weekday + ', ' + day.day"></div>
                            <div class="daily-day-reward" :class="{ 'streak': day.is_streak_day }" x-text="'+' + day.reward + ' Б'"></div>
                            <div class="daily-day-badge" x-show="day.is_today">сегодня</div>
                        </div>
                    </template>
                </div>

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

            <div class="roulette-layout">
                <!-- Marketplace / re:premium cards -->
                <div class="rp-stage">
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
                                <div class="rp-card" :data-index="sector.sectorIndex" :class="{ 'active': marketActiveIndex === sector.sectorIndex }">
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
            function bonusPage(initialBalance, initialFreeSpins, canCollectDaily, spinCost, freeSpinsEnabled, sectors, needsAccept, weekCollectData) {
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
                    weekCollectData: weekCollectData,
                    get balanceFormatted() {
                        return this.balance.toLocaleString('ru-RU');
                    },

                    // wheel
                    sectors,
                    needsAccept,
                    spinning: false,
                    message: '',
                    messageType: '',
                    marketActiveIndex: 0,
                    marketActiveCard: 0,
                    marketOffset: 0,
                    marketCardWidth: 240,
                    marketCardRealWidth: 220,
                    marketVisibleCenter: 0,
                    marketLeadingCycles: 4,
                    marketTrailingCycles: 50,
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
                        const start = -this.marketLeadingCycles * count;
                        const end = this.marketTrailingCycles * count;
                        for (let i = start; i < end; i++) {
                            const sectorIndex = ((i % count) + count) % count;
                            const s = this.sectors[sectorIndex];
                            const t = templates[sectorIndex % templates.length];
                            let sub = 'Приз';
                            if (s.type === 'free_spin') sub = 'Ещё попытка';
                            else if (s.type === 'bonus') sub = 'Бонус';
                            list.push({
                                label: this.truncate(s.label || s.name || '', 18),
                                sub: sub,
                                light: t.light,
                                dark: t.dark,
                                icon: t.icon,
                                sectorIndex: sectorIndex
                            });
                        }
                        return list;
                    },

                    init() {
                        this.$nextTick(() => {
                            this.measureMarket();
                            this.drawMarketWheel();
                        });

                        window.addEventListener('resize', () => {
                            this.measureMarket();
                            this.drawMarketWheel();
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

                    drawMarketWheel(animate = false) {
                        this.$nextTick(() => {
                            this.measureMarket();
                            const track = this.$refs.marketTrack;
                            if (!track) return;
                            const cardWidth = this.marketCardWidth;
                            const realWidth = this.marketCardRealWidth;
                            const visibleCenter = this.marketVisibleCenter || (track.parentElement?.clientWidth / 2) || 360;
                            const activeCard = this.marketActiveIndex;
                            this.marketOffset = visibleCenter - realWidth / 2 - activeCard * cardWidth;
                            track.style.transition = animate ? 'transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1)' : 'none';
                            track.style.transform = `translate3d(${this.marketOffset}px, 0, 0)`;
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

                        if (!cardWidth || !realWidth || !center) return;

                        let activeIndex = this.marketActiveIndex;
                        const nearestIndex = Math.round((center - realWidth / 2 - this.marketOffset) / cardWidth);
                        const start = Math.max(0, nearestIndex - 8);
                        const end = Math.min(cards.length, nearestIndex + 9);

                        for (let i = start; i < end; i++) {
                            const card = cards[i];
                            const cardLeft = this.marketOffset + i * cardWidth;
                            const cardCenter = cardLeft + realWidth / 2;
                            const dist = (cardCenter - center) / cardWidth;
                            const absDist = Math.min(4, Math.abs(dist));

                            const isActive = absDist < 0.5;
                            const scale = isActive ? 1.16 : Math.max(0.78, 1 - absDist * 0.11);
                            const opacity = isActive ? 1 : Math.max(0.45, 1 - absDist * 0.28);

                            card.style.transform = `scale(${scale})`;
                            card.style.opacity = opacity;
                            card.style.filter = 'none';
                            card.style.zIndex = isActive ? 2 : 1;
                            card.classList.toggle('active', isActive);
                            if (isActive) {
                                activeIndex = parseInt(card.dataset.index || (i % count), 10);
                            }
                        }
                        this.marketActiveIndex = activeIndex;
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
                            if (data.week_collect_data) {
                                this.weekCollectData = data.week_collect_data;
                            }
                            setTimeout(() => this.balancePulse = false, 800);
                        } catch (e) {
                            this.dailyMessage = 'Не удалось связаться с сервером.';
                            this.dailyMessageType = 'error';
                        } finally {
                            this.dailyCollecting = false;
                        }
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

                            this.spinMarket(data);
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

                    spinMarket(data) {
                        const index = this.sectors.findIndex(s => s.id === data.sector.id);
                        const count = this.sectors.length || 1;
                        const loops = 6;
                        const cardWidth = this.marketCardWidth;
                        const realWidth = this.marketCardRealWidth;
                        const visibleCenter = this.marketVisibleCenter || (this.$refs.marketTrack?.parentElement?.clientWidth / 2) || 360;

                        if (!cardWidth || !realWidth || index < 0) {
                            this.finishSpin(data);
                            return;
                        }

                        const track = this.$refs.marketTrack;
                        if (!track) {
                            this.finishSpin(data);
                            return;
                        }

                        const currentSectorIndex = this.marketActiveIndex % count;
                        const sectorDelta = (index - currentSectorIndex + count) % count;
                        const currentCard = this.marketActiveCard;
                        const finalCardIndex = currentCard + count * loops + sectorDelta;
                        const finalOffset = visibleCenter - (finalCardIndex * cardWidth + realWidth / 2);
                        const startOffset = this.marketOffset;
                        const distance = finalOffset - startOffset;
                        const duration = 2800;

                        track.querySelectorAll('.rp-card').forEach(card => {
                            card.style.transition = 'none';
                            card.classList.remove('active');
                        });
                        track.style.transition = 'none';

                        const startTime = performance.now();
                        const easeOutQuart = (t) => 1 - Math.pow(1 - t, 4);

                        const animate = (now) => {
                            const elapsed = now - startTime;
                            const t = Math.min(1, elapsed / duration);
                            const eased = easeOutQuart(t);
                            const currentOffset = startOffset + distance * eased;
                            this.marketOffset = currentOffset;
                            track.style.transform = `translate3d(${currentOffset}px, 0, 0)`;
                            this.updateMarketCards();

                            if (t < 1) {
                                requestAnimationFrame(animate);
                            } else {
                                this.marketOffset = finalOffset;
                                this.marketActiveCard = finalCardIndex;
                                this.marketActiveIndex = index;
                                track.style.transform = `translate3d(${finalOffset}px, 0, 0)`;
                                this.updateMarketCards();

                                // Бесшовный сброс к базовому циклу: карточки одного сектора
                                // визуально одинаковы, поэтому прыжка не видно, а лента
                                // остаётся «бесконечной» для следующих прокруток.
                                const resetCard = index;
                                this.marketActiveCard = resetCard;
                                this.marketOffset = visibleCenter - (resetCard * cardWidth + realWidth / 2);
                                track.style.transition = 'none';
                                track.style.transform = `translate3d(${this.marketOffset}px, 0, 0)`;
                                this.updateMarketCards();

                                setTimeout(() => this.finishSpin(data), 400);
                            }
                        };

                        requestAnimationFrame(animate);
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
