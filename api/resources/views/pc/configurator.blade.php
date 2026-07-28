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
                                <svg class="pc-case-svg" viewBox="0 0 400 560" xmlns="http://www.w3.org/2000/svg" aria-label="Схема системного блока">
                                    {{-- Корпус --}}
                                    <g class="pc-slot pc-slot-case" data-slot="case" :class="slotClass('case')">
                                        <polygon class="pc-shape" points="70,20 90,4 330,4 310,20" />
                                        <polygon class="pc-shape" points="310,20 330,4 330,484 310,520" />
                                        <rect class="pc-shape" x="70" y="20" width="240" height="500" rx="10" />
                                        <text class="pc-slot-label" x="190" y="545" x-show="build['case']" x-text="shortName('case')"></text>
                                    </g>

                                    {{-- Материнская плата --}}
                                    <g class="pc-slot" data-slot="motherboard" :class="slotClass('motherboard')">
                                        <rect class="pc-shape" x="95" y="55" width="165" height="310" rx="4" />
                                        <text class="pc-slot-label" x="177" y="380" x-show="build['motherboard']" x-text="shortName('motherboard')"></text>
                                    </g>

                                    {{-- Процессор --}}
                                    <g class="pc-slot" data-slot="cpu" :class="slotClass('cpu')">
                                        <rect class="pc-shape" x="115" y="85" width="62" height="62" rx="4" />
                                        <rect class="pc-shape pc-shape-inner" x="127" y="97" width="38" height="38" rx="2" />
                                        <text class="pc-slot-label" x="146" y="163" x-show="build['cpu']" x-text="shortName('cpu')"></text>
                                    </g>

                                    {{-- Память (2 планки) --}}
                                    <g class="pc-slot" data-slot="ram" :class="slotClass('ram')">
                                        <rect class="pc-shape" x="200" y="80" width="11" height="95" rx="2" />
                                        <rect class="pc-shape" x="218" y="80" width="11" height="95" rx="2" />
                                        <text class="pc-slot-label" x="214" y="70" x-show="build['ram']" x-text="shortName('ram')"></text>
                                    </g>

                                    {{-- Видеокарта --}}
                                    <g class="pc-slot" data-slot="gpu" :class="slotClass('gpu')">
                                        <rect class="pc-shape" x="110" y="210" width="155" height="36" rx="4" />
                                        <rect class="pc-shape pc-shape-inner" x="110" y="228" width="155" height="6" />
                                        <text class="pc-slot-label" x="187" y="262" x-show="build['gpu']" x-text="shortName('gpu')"></text>
                                    </g>

                                    {{-- Накопитель --}}
                                    <g class="pc-slot" data-slot="storage" :class="slotClass('storage')">
                                        <rect class="pc-shape" x="115" y="285" width="64" height="34" rx="3" />
                                        <text class="pc-slot-label" x="147" y="335" x-show="build['storage']" x-text="shortName('storage')"></text>
                                    </g>

                                    {{-- Охлаждение (extra) --}}
                                    <g class="pc-slot" data-slot="extra" :class="slotClass('extra')">
                                        <circle class="pc-shape" cx="228" cy="310" r="26" />
                                        <circle class="pc-shape pc-shape-inner" cx="228" cy="310" r="8" />
                                        <text class="pc-slot-label" x="228" y="352" x-show="build['extra']" x-text="shortName('extra')"></text>
                                    </g>

                                    {{-- Блок питания --}}
                                    <g class="pc-slot" data-slot="psu" :class="slotClass('psu')">
                                        <rect class="pc-shape" x="95" y="410" width="165" height="90" rx="6" />
                                        <circle class="pc-shape pc-shape-inner" cx="140" cy="455" r="22" />
                                        <text class="pc-slot-label" x="177" y="515" x-show="build['psu']" x-text="shortName('psu')"></text>
                                    </g>
                                </svg>
                            </div>
                        </div>
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
                return part.name.length > 22 ? part.name.slice(0, 21) + '…' : part.name;
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
