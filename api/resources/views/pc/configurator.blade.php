@extends('layouts.site')

@section('title', 'Сборка ПК — конфигуратор | Gadget Bar')

@section('meta_description', 'Соберите компьютер онлайн: подбор совместимых комплектующих по шагам — корпус, процессор, материнская плата, видеокарта, память, накопитель, блок питания.')

@section('content')
    <section class="pc-section" x-data="pcConfigurator(@js($city))" x-init="init()">
        <div class="container-theme">
            <div class="pc-hero">
                <h1 class="pc-title">Конфигуратор ПК</h1>
                <p class="pc-subtitle">Соберите систему по шагам — покажем только совместимые комплектующие</p>
            </div>

            @if($demo ?? false)
                <div class="pc-demo-banner">ДЕМО-РЕЖИМ: тестовые данные</div>
            @endif

            {{-- Экран успеха --}}
            <div class="pc-success" x-show="orderId" x-cloak>
                <div class="pc-success-icon">✓</div>
                <h2>Заявка №<span x-text="orderId"></span> принята</h2>
                <p>Менеджер свяжется с вами для подтверждения сборки.</p>
                <button type="button" class="pc-success-btn" @click="orderId = null">Собрать новую конфигурацию</button>
            </div>

            <div x-show="!orderId">
                <div class="pc-layout">
                    {{-- Визард: шаги-аккордеон --}}
                    <div class="pc-wizard">
                        <div class="pc-parts-loading" x-show="loadingSlots">Загружаем конфигуратор…</div>

                        <template x-for="(slot, index) in slots" :key="slot.id">
                            <div class="pc-step"
                                 :class="{
                                     'pc-step--active': activeSlot === slot.id,
                                     'pc-step--done': !!build[slot.id],
                                     'pc-step--soon': slot.empty
                                 }">
                                <button type="button" class="pc-step-head" @click="selectStep(slot)">
                                    <span class="pc-step-num" x-text="index + 1"></span>
                                    <span class="pc-step-title" x-text="slot.title"></span>
                                    <span class="pc-step-chosen" x-show="build[slot.id]" x-text="shortName(slot.id)"></span>
                                    <span class="pc-step-state">
                                        <span x-show="slot.empty" class="pc-badge pc-badge--soon">Скоро</span>
                                        <span x-show="!slot.empty && build[slot.id]" class="pc-badge pc-badge--done">✓</span>
                                        <span x-show="!slot.empty && !build[slot.id] && !slot.required" class="pc-badge pc-badge--opt">опц.</span>
                                    </span>
                                </button>

                                <div class="pc-step-body" x-show="activeSlot === slot.id && !slot.empty">
                                    <div class="pc-parts-loading" x-show="loadingParts">Подбираем совместимые варианты…</div>

                                    <div class="pc-parts-empty" x-show="!loadingParts && partsEmpty">
                                        Нет совместимых вариантов — попробуйте изменить другие комплектующие.
                                    </div>

                                    <div class="pc-parts" x-show="!loadingParts && !partsEmpty">
                                        <template x-for="part in parts" :key="part.id">
                                            <button type="button" class="pc-part"
                                                    :class="{ 'pc-part--selected': build[slot.id] && build[slot.id].id === part.id }"
                                                    @click="choose(slot, part)">
                                                <span class="pc-part-name" x-text="part.name"></span>
                                                <span class="pc-part-meta">
                                                    <template x-for="chip in chips(part)" :key="chip">
                                                        <span class="pc-chip" x-text="chip"></span>
                                                    </template>
                                                </span>
                                                <span class="pc-part-footer">
                                                    <span class="pc-part-price" x-text="fmtPrice(part.price)"></span>
                                                    <span class="pc-part-stock" x-text="'в наличии: ' + Math.round(part.stock)"></span>
                                                </span>
                                            </button>
                                        </template>
                                    </div>

                                    <button type="button" class="pc-skip-btn" x-show="!slot.required && !loadingParts"
                                            @click="skipStep(slot)">Пропустить шаг</button>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Схематичный корпус с 3D --}}
                    <div class="pc-case-panel">
                        <div class="pc-case-scene">
                            <div class="pc-case-3d" :style="caseStyle()">
                                <svg class="pc-case-svg" viewBox="0 0 440 600" xmlns="http://www.w3.org/2000/svg" aria-label="Схема системного блока">
                                    <defs>
                                        <linearGradient id="pcCaseGrad" x1="0" y1="0" x2="1" y2="1">
                                            <stop offset="0" stop-color="#1d2635" />
                                            <stop offset="0.5" stop-color="#121a27" />
                                            <stop offset="1" stop-color="#0a0e16" />
                                        </linearGradient>
                                        <linearGradient id="pcPcbGrad" x1="0" y1="0" x2="0" y2="1">
                                            <stop offset="0" stop-color="#132130" />
                                            <stop offset="1" stop-color="#0c141d" />
                                        </linearGradient>
                                        <linearGradient id="pcPsuGrad" x1="0" y1="0" x2="0" y2="1">
                                            <stop offset="0" stop-color="#171f2c" />
                                            <stop offset="1" stop-color="#0d1219" />
                                        </linearGradient>
                                        <linearGradient id="pcLedGrad" x1="0" y1="0" x2="0" y2="1">
                                            <stop offset="0" stop-color="#22d3ee" />
                                            <stop offset="1" stop-color="#e879f9" />
                                        </linearGradient>
                                        <linearGradient id="pcGlassGrad" x1="0" y1="0" x2="1" y2="1">
                                            <stop offset="0" stop-color="#7dd3fc" stop-opacity="0.09" />
                                            <stop offset="1" stop-color="#7dd3fc" stop-opacity="0.02" />
                                        </linearGradient>
                                    </defs>

                                    {{-- Корпус: металл + LED-полоса + ножки --}}
                                    <g class="pc-slot pc-slot-case" data-slot="case" :class="slotClass('case')"
                                       @mouseenter="hoveredSlot = 'case'" @mouseleave="hoveredSlot = null" @click="activateSlot('case')">
                                        <rect class="pc-shape pc-case-body" x="60" y="30" width="280" height="520" rx="12" />
                                        <rect class="pc-led" x="84" y="58" width="4" height="464" rx="2" />
                                        <rect class="pc-foot" x="88" y="550" width="36" height="12" rx="3" />
                                        <rect class="pc-foot" x="276" y="550" width="36" height="12" rx="3" />
                                        <text class="pc-slot-label" x="200" y="584" x-show="build['case']" x-text="shortName('case')"></text>
                                    </g>

                                    {{-- Материнская плата: текстура дорожек + IO-панель --}}
                                    <g class="pc-slot" data-slot="motherboard" :class="slotClass('motherboard')"
                                       @mouseenter="hoveredSlot = 'motherboard'" @mouseleave="hoveredSlot = null" @click="activateSlot('motherboard')">
                                        <rect class="pc-shape pc-mb" x="100" y="70" width="190" height="330" rx="4" />
                                        <rect class="pc-detail pc-io" x="106" y="76" width="26" height="56" rx="2" />
                                        <line class="pc-trace" x1="142" y1="88" x2="282" y2="88" />
                                        <line class="pc-trace" x1="142" y1="190" x2="286" y2="190" />
                                        <line class="pc-trace" x1="120" y1="240" x2="120" y2="380" />
                                        <line class="pc-trace" x1="180" y1="360" x2="282" y2="360" />
                                        <text class="pc-slot-label" x="195" y="418" x-show="build['motherboard']" x-text="shortName('motherboard')"></text>
                                    </g>

                                    {{-- Процессор: heatspreader + насечка --}}
                                    <g class="pc-slot" data-slot="cpu" :class="slotClass('cpu')"
                                       @mouseenter="hoveredSlot = 'cpu'" @mouseleave="hoveredSlot = null" @click="activateSlot('cpu')">
                                        <rect class="pc-shape" x="140" y="100" width="64" height="64" rx="4" />
                                        <rect class="pc-detail" x="149" y="109" width="46" height="46" rx="2" />
                                        <polygon class="pc-notch" points="142,102 149,102 142,109" />
                                        <text class="pc-slot-label" x="172" y="182" x-show="build['cpu']" x-text="shortName('cpu')"></text>
                                    </g>

                                    {{-- Память: 2 планки с чипами --}}
                                    <g class="pc-slot" data-slot="ram" :class="slotClass('ram')"
                                       @mouseenter="hoveredSlot = 'ram'" @mouseleave="hoveredSlot = null" @click="activateSlot('ram')">
                                        <rect class="pc-shape" x="232" y="95" width="12" height="110" rx="2" />
                                        <rect class="pc-shape" x="250" y="95" width="12" height="110" rx="2" />
                                        <rect class="pc-detail" x="234" y="100" width="8" height="26" />
                                        <rect class="pc-detail" x="234" y="132" width="8" height="26" />
                                        <rect class="pc-detail" x="234" y="164" width="8" height="26" />
                                        <rect class="pc-detail" x="252" y="100" width="8" height="26" />
                                        <rect class="pc-detail" x="252" y="132" width="8" height="26" />
                                        <rect class="pc-detail" x="252" y="164" width="8" height="26" />
                                        <text class="pc-slot-label" x="247" y="88" x-show="build['ram']" x-text="shortName('ram')"></text>
                                    </g>

                                    {{-- Видеокарта: 2 вентилятора + backplate --}}
                                    <g class="pc-slot" data-slot="gpu" :class="slotClass('gpu')"
                                       @mouseenter="hoveredSlot = 'gpu'" @mouseleave="hoveredSlot = null" @click="activateSlot('gpu')">
                                        <rect class="pc-shape pc-gpu" x="110" y="240" width="200" height="42" rx="4" />
                                        <rect class="pc-detail" x="110" y="282" width="200" height="5" />
                                        <circle class="pc-detail pc-fan-ring" cx="155" cy="261" r="13" />
                                        <line class="pc-detail-line" x1="155" y1="251" x2="155" y2="271" />
                                        <line class="pc-detail-line" x1="145" y1="261" x2="165" y2="261" />
                                        <line class="pc-detail-line" x1="148" y1="254" x2="162" y2="268" />
                                        <line class="pc-detail-line" x1="162" y1="254" x2="148" y2="268" />
                                        <circle class="pc-detail pc-fan-ring" cx="200" cy="261" r="13" />
                                        <line class="pc-detail-line" x1="200" y1="251" x2="200" y2="271" />
                                        <line class="pc-detail-line" x1="190" y1="261" x2="210" y2="261" />
                                        <line class="pc-detail-line" x1="193" y1="254" x2="207" y2="268" />
                                        <line class="pc-detail-line" x1="207" y1="254" x2="193" y2="268" />
                                        <text class="pc-slot-label" x="210" y="304" x-show="build['gpu']" x-text="shortName('gpu')"></text>
                                    </g>

                                    {{-- Накопитель: M.2 планка с чипами --}}
                                    <g class="pc-slot" data-slot="storage" :class="slotClass('storage')"
                                       @mouseenter="hoveredSlot = 'storage'" @mouseleave="hoveredSlot = null" @click="activateSlot('storage')">
                                        <rect class="pc-shape" x="110" y="316" width="64" height="16" rx="2" />
                                        <rect class="pc-detail" x="116" y="319" width="22" height="10" />
                                        <rect class="pc-detail" x="142" y="319" width="14" height="10" />
                                        <text class="pc-slot-label" x="142" y="348" x-show="build['storage']" x-text="shortName('storage')"></text>
                                    </g>

                                    {{-- Охлаждение (extra): вентилятор с вращающимися лопастями --}}
                                    <g class="pc-slot" data-slot="extra" :class="slotClass('extra')"
                                       @mouseenter="hoveredSlot = 'extra'" @mouseleave="hoveredSlot = null" @click="activateSlot('extra')">
                                        <circle class="pc-shape" cx="255" cy="345" r="24" />
                                        <g class="pc-fan-blades">
                                            <path d="M255 345 q10 -9 3 -21" />
                                            <path d="M255 345 q10 -9 3 -21" transform="rotate(60 255 345)" />
                                            <path d="M255 345 q10 -9 3 -21" transform="rotate(120 255 345)" />
                                            <path d="M255 345 q10 -9 3 -21" transform="rotate(180 255 345)" />
                                            <path d="M255 345 q10 -9 3 -21" transform="rotate(240 255 345)" />
                                            <path d="M255 345 q10 -9 3 -21" transform="rotate(300 255 345)" />
                                        </g>
                                        <circle class="pc-detail" cx="255" cy="345" r="6" />
                                        <text class="pc-slot-label" x="255" y="384" x-show="build['extra']" x-text="shortName('extra')"></text>
                                    </g>

                                    {{-- Блок питания: решётка вентилятора --}}
                                    <g class="pc-slot" data-slot="psu" :class="slotClass('psu')"
                                       @mouseenter="hoveredSlot = 'psu'" @mouseleave="hoveredSlot = null" @click="activateSlot('psu')">
                                        <rect class="pc-shape pc-psu" x="100" y="430" width="190" height="84" rx="6" />
                                        <circle class="pc-detail-line" cx="150" cy="472" r="20" />
                                        <circle class="pc-detail-line" cx="150" cy="472" r="12" />
                                        <circle class="pc-detail-line" cx="150" cy="472" r="4" />
                                        <text class="pc-slot-label" x="195" y="530" x-show="build['psu']" x-text="shortName('psu')"></text>
                                    </g>

                                    {{-- Стеклянная панель поверх (не перехватывает события) --}}
                                    <rect class="pc-glass" x="76" y="46" width="248" height="488" rx="8" />
                                    <polygon class="pc-glass-glare" points="76,180 150,46 200,46 76,320" />
                                </svg>
                            </div>
                        </div>
                        <div class="pc-case-hint" x-text="hint"></div>
                    </div>
                </div>

                {{-- Итог и форма заявки --}}
                <div class="pc-summary">
                    <div class="pc-total">
                        <span class="pc-total-label">Итого</span>
                        <span class="pc-total-value" x-text="fmtPrice(total)"></span>
                        <button type="button" class="pc-reset-btn" @click="reset()" x-show="hasBuild">Сбросить сборку</button>
                    </div>

                    <form class="pc-order-form" @submit.prevent="submit()">
                        <div class="pc-field">
                            <label class="pc-label" for="pc-name">Имя</label>
                            <input class="pc-input" id="pc-name" type="text" x-model="form.name" required maxlength="255" placeholder="Как к вам обращаться">
                        </div>
                        <div class="pc-field">
                            <label class="pc-label" for="pc-phone">Телефон</label>
                            <input class="pc-input" id="pc-phone" type="tel" x-model="form.phone" required maxlength="50" placeholder="+7 (___) ___-__-__">
                        </div>
                        <button type="submit" class="pc-submit-btn" :disabled="!canSubmit || submitting"
                                x-text="submitting ? 'Отправляем…' : 'Оформить заявку'"></button>
                    </form>

                    <div class="pc-form-error" x-show="error" x-text="error" x-cloak></div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('pcConfigurator', (city) => ({
            city: city || null,
            slots: [],
            activeSlot: null,
            build: {},
            parts: [],
            partsEmpty: false,
            loadingSlots: true,
            loadingParts: false,
            form: { name: '', phone: '' },
            submitting: false,
            error: '',
            orderId: null,
            isMobile: false,
            hoveredSlot: null,

            init() {
                const mq = window.matchMedia('(max-width: 767px)');
                this.isMobile = mq.matches;
                mq.addEventListener('change', (e) => { this.isMobile = e.matches; });

                try {
                    const saved = JSON.parse(localStorage.getItem('pc_build') || '{}');
                    if (saved && typeof saved === 'object') this.build = saved;
                } catch (e) { this.build = {}; }

                this.loadSlots();
            },

            async loadSlots() {
                this.loadingSlots = true;
                try {
                    const res = await fetch('/api/pc/slots', { headers: { 'Accept': 'application/json' } });
                    const data = await res.json();
                    this.slots = data.data || [];
                } catch (e) {
                    this.error = 'Не удалось загрузить конфигуратор. Обновите страницу.';
                } finally {
                    this.loadingSlots = false;
                }

                if (this.slots.length && !this.activeSlot) {
                    const next = this.slots.find(s => !s.empty && !this.build[s.id]) || this.slots.find(s => !s.empty);
                    if (next) {
                        this.activeSlot = next.id;
                        this.loadParts();
                    }
                }
            },

            selectStep(slot) {
                if (slot.empty) return;
                if (this.activeSlot === slot.id) {
                    this.activeSlot = null;
                    this.parts = [];
                    return;
                }
                this.activeSlot = slot.id;
                this.loadParts();
            },

            activateSlot(slotId) {
                const slot = this.slots.find(s => s.id === slotId);
                if (slot) this.selectStep(slot);
            },

            get hint() {
                if (!this.hoveredSlot) return '';
                const slot = this.slots.find(s => s.id === this.hoveredSlot);
                if (!slot) return '';
                if (slot.empty) return slot.title + ' — скоро в продаже';
                const chosen = this.shortName(slot.id);
                return slot.title + (chosen ? ': ' + chosen : '');
            },

            skipStep(slot) {
                const next = this.slots.find(s => !s.empty && !this.build[s.id] && s.id !== slot.id
                    && this.slots.indexOf(s) > this.slots.indexOf(slot));
                if (next) {
                    this.activeSlot = next.id;
                    this.loadParts();
                } else {
                    this.activeSlot = null;
                    this.parts = [];
                }
            },

            buildIds() {
                const ids = {};
                for (const [slot, part] of Object.entries(this.build)) ids[slot] = part.id;
                return ids;
            },

            async loadParts() {
                if (!this.activeSlot) return;
                this.loadingParts = true;
                this.parts = [];
                this.partsEmpty = false;
                try {
                    const params = new URLSearchParams({
                        slot: this.activeSlot,
                        build: JSON.stringify(this.buildIds()),
                    });
                    const res = await fetch('/api/pc/parts?' + params.toString(), {
                        headers: { 'Accept': 'application/json' },
                    });
                    const data = await res.json();
                    this.parts = data.data || [];
                    this.partsEmpty = !data.empty && this.parts.length === 0;
                } catch (e) {
                    this.error = 'Не удалось загрузить комплектующие.';
                } finally {
                    this.loadingParts = false;
                }
            },

            choose(slot, part) {
                this.build[slot.id] = { id: part.id, name: part.name, price: part.price };
                this.saveBuild();

                const idx = this.slots.indexOf(slot);
                const next = this.slots.slice(idx + 1).find(s => !s.empty && !this.build[s.id])
                    || this.slots.find(s => !s.empty && !this.build[s.id]);
                if (next) {
                    this.activeSlot = next.id;
                    this.loadParts();
                } else {
                    this.activeSlot = null;
                    this.parts = [];
                }
            },

            saveBuild() {
                localStorage.setItem('pc_build', JSON.stringify(this.build));
            },

            reset() {
                this.build = {};
                localStorage.removeItem('pc_build');
                this.error = '';
                const first = this.slots.find(s => !s.empty);
                this.activeSlot = first ? first.id : null;
                this.parts = [];
                if (this.activeSlot) this.loadParts();
            },

            get hasBuild() {
                return Object.keys(this.build).length > 0;
            },

            get total() {
                return Object.values(this.build).reduce((sum, p) => sum + (p.price || 0), 0);
            },

            get canSubmit() {
                if (!this.form.name.trim() || !this.form.phone.trim()) return false;
                if (!this.hasBuild) return false;
                return this.slots
                    .filter(s => s.required && !s.empty)
                    .every(s => !!this.build[s.id]);
            },

            chips(part) {
                const a = part.attributes || {};
                const out = [];
                if (a.socket) out.push(a.socket);
                if (a.memory_type) out.push(a.memory_type);
                if (a.form_factor) out.push(a.form_factor);
                if (a.gpu_chip) out.push(a.gpu_chip);
                if (a.vram_gb) out.push(a.vram_gb + ' ГБ');
                if (a.module_gb) out.push(a.module_gb + ' ГБ');
                if (a.tdp_w) out.push(a.tdp_w + ' Вт');
                if (a.wattage) out.push(a.wattage + ' Вт');
                if (a.psu_w) out.push('БП от ' + a.psu_w + ' Вт');
                return out.slice(0, 3);
            },

            fmtPrice(price) {
                if (price === null || price === undefined) return 'Цена по запросу';
                return new Intl.NumberFormat('ru-RU').format(Math.round(price)) + ' ₽';
            },

            shortName(slotId) {
                const part = this.build[slotId];
                if (!part) return '';
                return part.name.length > 18 ? part.name.slice(0, 17) + '…' : part.name;
            },

            slotClass(slotId) {
                const slot = this.slots.find(s => s.id === slotId);
                return {
                    'pc-slot--selected': !!this.build[slotId],
                    'pc-slot--active': this.activeSlot === slotId,
                    'pc-slot--soon': slot ? slot.empty : false,
                };
            },

            caseStyle() {
                if (this.isMobile) return '';
                const poses = {
                    case:        { t: 'rotateY(-4deg) rotateX(2deg)', o: '50% 50%' },
                    cpu:         { t: 'rotateY(-12deg) rotateX(6deg) scale(1.08)', o: '36% 22%' },
                    motherboard: { t: 'rotateY(-8deg) rotateX(3deg) scale(1.06)', o: '44% 40%' },
                    gpu:         { t: 'rotateY(10deg) rotateX(-4deg) scale(1.08)', o: '47% 42%' },
                    ram:         { t: 'rotateY(-14deg) rotateX(4deg) scale(1.08)', o: '54% 24%' },
                    storage:     { t: 'rotateY(8deg) rotateX(-6deg) scale(1.07)', o: '36% 56%' },
                    psu:         { t: 'rotateY(6deg) rotateX(-10deg) scale(1.06)', o: '44% 82%' },
                    extra:       { t: 'rotateY(-6deg) rotateX(-8deg) scale(1.07)', o: '57% 56%' },
                };
                const base = { t: 'rotateY(-6deg) rotateX(3deg)', o: '50% 50%' };
                const pose = (this.activeSlot && poses[this.activeSlot]) || base;
                return 'transform: ' + pose.t + '; transform-origin: ' + pose.o + ';';
            },

            async submit() {
                if (!this.canSubmit || this.submitting) return;
                this.submitting = true;
                this.error = '';
                try {
                    const res = await fetch('/pc/build', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify({
                            customer_name: this.form.name.trim(),
                            customer_phone: this.form.phone.trim(),
                            customer_city: this.city,
                            items: Object.values(this.build).map(p => ({ product_id: p.id, quantity: 1 })),
                        }),
                    });
                    const data = await res.json();
                    if (res.status === 201 && data.success) {
                        this.orderId = data.order_id;
                        this.build = {};
                        localStorage.removeItem('pc_build');
                        this.form = { name: '', phone: '' };
                        this.activeSlot = null;
                        this.parts = [];
                    } else if (res.status === 422) {
                        const details = data.errors ? Object.values(data.errors).flat().join(' ') : '';
                        this.error = (data.message || 'Проверьте данные формы.') + (details ? ' ' + details : '');
                    } else {
                        this.error = 'Не удалось отправить заявку. Попробуйте позже.';
                    }
                } catch (e) {
                    this.error = 'Ошибка сети. Проверьте подключение и попробуйте ещё раз.';
                } finally {
                    this.submitting = false;
                }
            },
        }));
    });
</script>
@endpush
