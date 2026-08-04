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

            {{-- Переключатель режимов --}}
            <div class='pc-mode-tabs' x-show='!orderId'>
                <button type='button' class='pc-mode-tab' :class='{ "pc-mode-tab--active": mode === "manual" }' @click='mode = "manual"'>Собрать самому</button>
                <button type='button' class='pc-mode-tab' :class='{ "pc-mode-tab--active": mode === "auto" }' @click='mode = "auto"'>Автоподбор по бюджету</button>
            </div>

            {{-- Экран успеха --}}
            <div class="pc-success" x-show="orderId" x-cloak>
                <div class="pc-success-icon">✓</div>
                <h2>Заявка №<span x-text="orderId"></span> принята</h2>
                <p>Менеджер свяжется с вами для подтверждения сборки.</p>
                <button type="button" class="pc-success-btn" @click="orderId = null">Собрать новую конфигурацию</button>
            </div>

            <div x-show="mode === 'manual' && !orderId">
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

                                <div class="pc-step-body" x-show="activeSlot === slot.id && !slot.empty" x-collapse.duration.1000ms>
                                    <div class="pc-parts-loading" x-show="loadingParts">Подбираем совместимые варианты…</div>

                                    <div class="pc-parts-empty" x-show="!loadingParts && partsEmpty">
                                        Нет совместимых вариантов — попробуйте изменить другие комплектующие.
                                    </div>

                                    <div class="pc-parts" x-show="!loadingParts && !partsEmpty">
                                        <template x-for="part in parts" :key="part.id">
                                            <button type="button" class="pc-part"
                                                    :class="{ 'pc-part--selected': isSelected(slot, part), 'pc-part--multi': isMulti(slot.id) }"
                                                    @click="choose(slot, part)">
                                                <span class="pc-part-multi-badge" x-show="isMulti(slot.id)">можно несколько</span>
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
                                                <template x-if="isMulti(slot.id)">
                                                    <span class="pc-part-qty" @click.stop>
                                                        <button type="button" class="pc-qty-btn" @click="changeQty(slot, part, -1)">−</button>
                                                        <span class="pc-qty-value" x-text="qtyOf(slot, part)"></span>
                                                        <button type="button" class="pc-qty-btn" @click="changeQty(slot, part, 1)">+</button>
                                                    </span>
                                                </template>
                                            </button>
                                        </template>
                                    </div>

                                    <div class="pc-step-actions" x-show="!loadingParts && !partsEmpty">
                                        <button type="button" class="pc-skip-btn" x-show="!slot.required && !hasSlotItems(slot.id)"
                                                @click="skipStep(slot)">Пропустить шаг</button>
                                        <button type="button" class="pc-next-btn" x-show="isMulti(slot.id) && hasSlotItems(slot.id)"
                                                @click="advanceSlot(slot)">Перейти к следующему шагу</button>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- 3D-корпус системного блока --}}
                    <div class="pc-case-panel">
                        <div class="pc-case-scene">
                            <div class="pc-case-3d" :style="caseStyle()">
                                <div class="pc-case-face pc-case-face--back"></div>
                                <div class="pc-case-face pc-case-face--left"></div>
                                <div class="pc-case-face pc-case-face--right">
                                    <div class="pc-case-io" aria-hidden="true">
                                        <div class="pc-case-power"></div>
                                        <div class="pc-case-usb"></div>
                                        <div class="pc-case-usb"></div>
                                        <div class="pc-case-usb"></div>
                                    </div>
                                </div>
                                <div class="pc-case-face pc-case-face--top"></div>
                                <div class="pc-case-face pc-case-face--bottom"></div>
                                <div class="pc-case-foot pc-case-foot--fl" aria-hidden="true"></div>
                                <div class="pc-case-foot pc-case-foot--fr" aria-hidden="true"></div>
                                <div class="pc-case-foot pc-case-foot--bl" aria-hidden="true"></div>
                                <div class="pc-case-foot pc-case-foot--br" aria-hidden="true"></div>
                                <div class="pc-case-face pc-case-face--front">
                                    <svg class="pc-case-svg" viewBox="0 0 560 420" xmlns="http://www.w3.org/2000/svg" aria-label="Схема системного блока" preserveAspectRatio="xMidYMid meet">
                                        <defs>
                                            <linearGradient id="pcBodyFrontGrad" x1="0" y1="0" x2="0" y2="1">
                                                <stop offset="0%" stop-color="#1e293b" />
                                                <stop offset="100%" stop-color="#0f172a" />
                                            </linearGradient>
                                            <linearGradient id="pcInteriorGrad" x1="0" y1="0" x2="0" y2="1">
                                                <stop offset="0%" stop-color="#334155" />
                                                <stop offset="50%" stop-color="#1e293b" />
                                                <stop offset="100%" stop-color="#0f172a" />
                                            </linearGradient>
                                            <linearGradient id="pcGlassGrad" x1="0" y1="0" x2="1" y2="1">
                                                <stop offset="0%" stop-color="rgba(255,255,255,0.14)" />
                                                <stop offset="50%" stop-color="rgba(255,255,255,0.04)" />
                                                <stop offset="100%" stop-color="rgba(255,255,255,0.10)" />
                                            </linearGradient>
                                            <filter id="pcCaseShadow" x="-50%" y="-50%" width="200%" height="200%">
                                                <feGaussianBlur in="SourceAlpha" stdDeviation="8" />
                                                <feOffset dx="0" dy="10" result="offsetblur" />
                                                <feComponentTransfer>
                                                    <feFuncA type="linear" slope="0.45" />
                                                </feComponentTransfer>
                                                <feMerge>
                                                    <feMergeNode />
                                                    <feMergeNode in="SourceGraphic" />
                                                </feMerge>
                                            </filter>
                                        </defs>

                                        {{-- Тень под корпусом --}}
                                        <ellipse cx="280" cy="430" rx="250" ry="18" fill="rgba(0,0,0,0.35)" filter="url(#pcCaseShadow)" />

                                        {{-- Корпус (лицевая грань) --}}
                                        <g class="pc-slot pc-slot-case" data-slot="case" :class="slotClass('case')"
                                           @mouseenter="hoveredSlot = 'case'" @mouseleave="hoveredSlot = null" @click="activateSlot('case')">
                                            <rect class="pc-shape pc-case-body" x="0" y="0" width="560" height="420" rx="8" fill="url(#pcBodyFrontGrad)" stroke="#334155" stroke-width="2.5" />
                                        </g>

                                        {{-- Интерьер за стеклом --}}
                                        <rect class="pc-case-interior" x="40" y="40" width="360" height="320" rx="4" fill="url(#pcInteriorGrad)" />

                                        {{-- RGB-подсветка по периметру окна --}}
                                        <rect class="pc-led" x="37" y="37" width="366" height="6" rx="3" />
                                        <rect class="pc-led" x="37" y="357" width="366" height="6" rx="3" />
                                        <rect class="pc-led" x="37" y="37" width="6" height="320" rx="3" />
                                        <rect class="pc-led" x="397" y="37" width="6" height="320" rx="3" />

                                        {{-- Материнская плата (вертикально, слева) --}}
                                        <g class="pc-slot" data-slot="motherboard" :class="slotClass('motherboard')"
                                           @mouseenter="hoveredSlot = 'motherboard'" @mouseleave="hoveredSlot = null" @click="activateSlot('motherboard')">
                                            <rect class="pc-shape pc-mb" x="80" y="80" width="220" height="260" rx="5" />
                                            {{-- крепёжные отверстия --}}
                                            <circle cx="93" cy="93" r="3.5" fill="#64748b" />
                                            <circle cx="287" cy="93" r="3.5" fill="#64748b" />
                                            <circle cx="93" cy="328" r="3.5" fill="#64748b" />
                                            <circle cx="287" cy="328" r="3.5" fill="#64748b" />
                                            {{-- дорожки --}}
                                            <line class="pc-trace" x1="105" y1="115" x2="275" y2="115" />
                                            <line class="pc-trace" x1="105" y1="150" x2="275" y2="150" />
                                            <line class="pc-trace" x1="105" y1="235" x2="275" y2="235" />
                                            <line class="pc-trace" x1="105" y1="275" x2="275" y2="275" />
                                            <line class="pc-trace" x1="145" y1="115" x2="145" y2="305" />
                                            <line class="pc-trace" x1="200" y1="115" x2="200" y2="305" />
                                            {{-- чипсет --}}
                                            <rect x="165" y="205" width="40" height="28" rx="3" fill="#1e293b" stroke="#38bdf8" stroke-width="1.5" />
                                            <text class="pc-slot-label" x="190" y="350" x-show="build['motherboard']" x-text="shortName('motherboard')" style="font-size: 13px;"></text>
                                        </g>

                                        {{-- Процессор --}}
                                        <g class="pc-slot" data-slot="cpu" :class="slotClass('cpu')"
                                           @mouseenter="hoveredSlot = 'cpu'" @mouseleave="hoveredSlot = null" @click="activateSlot('cpu')">
                                            <rect class="pc-shape" x="145" y="120" width="55" height="55" rx="4" />
                                            <rect class="pc-detail" x="155" y="130" width="35" height="35" rx="2" />
                                            <circle class="pc-detail" cx="172" cy="147" r="9" />
                                            <text class="pc-slot-label" x="172" y="185" x-show="build['cpu']" x-text="shortName('cpu')" style="font-size: 13px;"></text>
                                        </g>

                                        {{-- Кулер --}}
                                        <g class="pc-slot" data-slot="cooler" :class="slotClass('cooler')"
                                           @mouseenter="hoveredSlot = 'cooler'" @mouseleave="hoveredSlot = null" @click="activateSlot('cooler')">
                                            <rect class="pc-shape pc-cooler" x="130" y="105" width="85" height="85" rx="4" />
                                            {{-- рёбра башни --}}
                                            <line class="pc-detail-line" x1="138" y1="117" x2="207" y2="117" />
                                            <line class="pc-detail-line" x1="138" y1="130" x2="207" y2="130" />
                                            <line class="pc-detail-line" x1="138" y1="143" x2="207" y2="143" />
                                            <line class="pc-detail-line" x1="138" y1="156" x2="207" y2="156" />
                                            <line class="pc-detail-line" x1="138" y1="169" x2="207" y2="169" />
                                            {{-- вентилятор --}}
                                            <circle class="pc-detail pc-fan-ring" cx="172" cy="147" r="28" />
                                            <g class="pc-fan-blades">
                                                <path d="M172 147 q10 -8 4 -18" />
                                                <path d="M172 147 q10 -8 4 -18" transform="rotate(60 172 147)" />
                                                <path d="M172 147 q10 -8 4 -18" transform="rotate(120 172 147)" />
                                                <path d="M172 147 q10 -8 4 -18" transform="rotate(180 172 147)" />
                                                <path d="M172 147 q10 -8 4 -18" transform="rotate(240 172 147)" />
                                                <path d="M172 147 q10 -8 4 -18" transform="rotate(300 172 147)" />
                                            </g>
                                            <circle class="pc-detail" cx="172" cy="147" r="5" />
                                            <text class="pc-slot-label" x="215" y="100" style="text-anchor:end; font-size: 13px;" x-show="build['cooler']" x-text="shortName('cooler')"></text>
                                        </g>

                                        {{-- Память: 2 планки DDR --}}
                                        <g class="pc-slot" data-slot="ram" :class="slotClass('ram')"
                                           @mouseenter="hoveredSlot = 'ram'" @mouseleave="hoveredSlot = null" @click="activateSlot('ram')">
                                            <rect class="pc-shape" x="245" y="120" width="13" height="85" rx="2" />
                                            <rect class="pc-shape" x="263" y="120" width="13" height="85" rx="2" />
                                            {{-- чипы --}}
                                            <rect class="pc-detail" x="247" y="130" width="9" height="17" rx="1" fill="#0ea5e9" />
                                            <rect class="pc-detail" x="247" y="153" width="9" height="17" rx="1" fill="#0ea5e9" />
                                            <rect class="pc-detail" x="247" y="176" width="9" height="17" rx="1" fill="#0ea5e9" />
                                            <rect class="pc-detail" x="265" y="130" width="9" height="17" rx="1" fill="#0ea5e9" />
                                            <rect class="pc-detail" x="265" y="153" width="9" height="17" rx="1" fill="#0ea5e9" />
                                            <rect class="pc-detail" x="265" y="176" width="9" height="17" rx="1" fill="#0ea5e9" />
                                            <text class="pc-slot-label" x="260" y="110" style="text-anchor:start; font-size: 13px;" x-show="build['ram']" x-text="shortName('ram')"></text>
                                        </g>

                                        {{-- Видеокарта: 2 вентилятора --}}
                                        <g class="pc-slot" data-slot="gpu" :class="slotClass('gpu')"
                                           @mouseenter="hoveredSlot = 'gpu'" @mouseleave="hoveredSlot = null" @click="activateSlot('gpu')">
                                            <rect class="pc-shape pc-gpu" x="90" y="260" width="230" height="50" rx="4" />
                                            <rect class="pc-detail" x="90" y="307" width="230" height="5" />
                                            {{-- вентилятор 1 --}}
                                            <circle class="pc-detail pc-fan-ring" cx="145" cy="285" r="17" />
                                            <line class="pc-detail-line" x1="145" y1="276" x2="145" y2="294" />
                                            <line class="pc-detail-line" x1="136" y1="285" x2="154" y2="285" />
                                            <line class="pc-detail-line" x1="139" y1="278" x2="151" y2="292" />
                                            <line class="pc-detail-line" x1="151" y1="278" x2="139" y2="292" />
                                            {{-- вентилятор 2 --}}
                                            <circle class="pc-detail pc-fan-ring" cx="205" cy="285" r="17" />
                                            <line class="pc-detail-line" x1="205" y1="276" x2="205" y2="294" />
                                            <line class="pc-detail-line" x1="196" y1="285" x2="214" y2="285" />
                                            <line class="pc-detail-line" x1="199" y1="278" x2="211" y2="292" />
                                            <line class="pc-detail-line" x1="211" y1="278" x2="199" y2="292" />
                                            {{-- RGB-полоса на GPU --}}
                                            <rect class="pc-led" x="95" y="265" width="220" height="3" rx="1.5" />
                                            <text class="pc-slot-label" x="205" y="325" x-show="build['gpu']" x-text="shortName('gpu')" style="font-size: 13px;"></text>
                                        </g>

                                        {{-- Накопитель: M.2 --}}
                                        <g class="pc-slot" data-slot="storage" :class="slotClass('storage')"
                                           @mouseenter="hoveredSlot = 'storage'" @mouseleave="hoveredSlot = null" @click="activateSlot('storage')">
                                            <rect class="pc-shape" x="90" y="340" width="75" height="14" rx="2" />
                                            <rect class="pc-detail" x="97" y="343" width="25" height="8" rx="1" />
                                            <rect class="pc-detail" x="127" y="343" width="20" height="8" rx="1" />
                                            <text class="pc-slot-label" x="127" y="370" x-show="build['storage']" x-text="shortName('storage')" style="font-size: 13px;"></text>
                                        </g>

                                        {{-- Дополнительный вентилятор --}}
                                        <g class="pc-slot" data-slot="extra" :class="slotClass('extra')"
                                           @mouseenter="hoveredSlot = 'extra'" @mouseleave="hoveredSlot = null" @click="activateSlot('extra')">
                                            <circle class="pc-shape" cx="260" cy="320" r="28" />
                                            <g class="pc-fan-blades">
                                                <path d="M260 320 q12 -10 5 -22" />
                                                <path d="M260 320 q12 -10 5 -22" transform="rotate(60 260 320)" />
                                                <path d="M260 320 q12 -10 5 -22" transform="rotate(120 260 320)" />
                                                <path d="M260 320 q12 -10 5 -22" transform="rotate(180 260 320)" />
                                                <path d="M260 320 q12 -10 5 -22" transform="rotate(240 260 320)" />
                                                <path d="M260 320 q12 -10 5 -22" transform="rotate(300 260 320)" />
                                            </g>
                                            <circle class="pc-detail" cx="260" cy="320" r="5" />
                                            <text class="pc-slot-label" x="260" y="360" x-show="build['extra']" x-text="shortName('extra')" style="font-size: 13px;"></text>
                                        </g>

                                        {{-- Блок питания --}}
                                        <g class="pc-slot" data-slot="psu" :class="slotClass('psu')"
                                           @mouseenter="hoveredSlot = 'psu'" @mouseleave="hoveredSlot = null" @click="activateSlot('psu')">
                                            <rect class="pc-shape pc-psu" x="100" y="355" width="180" height="55" rx="4" />
                                            {{-- решётка вентилятора --}}
                                            <circle class="pc-detail-line" cx="160" cy="382" r="20" />
                                            <circle class="pc-detail-line" cx="160" cy="382" r="13" />
                                            <circle class="pc-detail-line" cx="160" cy="382" r="6" />
                                            {{-- кабели --}}
                                            <path d="M 280 370 Q 310 360 325 370" fill="none" stroke="#94a3b8" stroke-width="2.5" stroke-linecap="round" />
                                            <path d="M 280 385 Q 310 375 325 385" fill="none" stroke="#94a3b8" stroke-width="2.5" stroke-linecap="round" />
                                            <text class="pc-slot-label" x="190" y="425" x-show="build['psu']" x-text="shortName('psu')" style="font-size: 13px;"></text>
                                        </g>

                                        {{-- Передняя панель (SVG-вариант для мобильной версии) --}}
                                        <g class="pc-slot pc-svg-front-panel" data-slot="case" :class="slotClass('case')"
                                           @mouseenter="hoveredSlot = 'case'" @mouseleave="hoveredSlot = null" @click="activateSlot('case')">
                                            <rect x="410" y="40" width="110" height="320" rx="4" fill="#1e293b" stroke="#334155" stroke-width="2" />
                                            {{-- Кнопка питания --}}
                                            <circle cx="465" cy="80" r="11" fill="#334155" stroke="#e2e8f0" stroke-width="2" />
                                            <circle class="pc-led" cx="465" cy="80" r="5" />
                                            {{-- USB-порты --}}
                                            <rect x="453" y="105" width="10" height="10" rx="2" fill="#0f172a" stroke="#e2e8f0" stroke-width="1.5" />
                                            <rect x="469" y="105" width="10" height="10" rx="2" fill="#0f172a" stroke="#e2e8f0" stroke-width="1.5" />
                                            <rect x="453" y="123" width="10" height="10" rx="2" fill="#0f172a" stroke="#e2e8f0" stroke-width="1.5" />
                                            {{-- Вентиляция передней панели --}}
                                            <g fill="#64748b">
                                                <circle cx="465" cy="200" r="5" />
                                                <circle cx="465" cy="235" r="5" />
                                                <circle cx="465" cy="270" r="5" />
                                                <circle cx="465" cy="305" r="5" />
                                                <circle cx="465" cy="340" r="5" />
                                            </g>
                                            {{-- RGB-лента на передней панели --}}
                                            <rect class="pc-led" x="417" y="50" width="5" height="300" rx="2" />
                                        </g>

                                        {{-- Ножки (SVG-вариант для мобильной версии) --}}
                                        <g class="pc-svg-foot">
                                            <rect class="pc-foot" x="70" y="420" width="60" height="12" rx="3" />
                                            <rect class="pc-foot" x="430" y="420" width="60" height="12" rx="3" />
                                        </g>

                                        {{-- Стеклянная панель поверх --}}
                                        <rect class="pc-glass" x="40" y="40" width="360" height="320" rx="4" fill="url(#pcGlassGrad)" />
                                        <polygon class="pc-glass-glare" points="40,260 160,40 260,40 40,340" />
                                        <polygon class="pc-glass-glare" points="40,340 110,340 40,410" opacity="0.5" />
                                    </svg>
                                </div>
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
                            <input class="pc-input" id="pc-phone" type="tel" x-model="form.phone" required maxlength="18"
                                   placeholder="+7 (___) ___-__-__"
                                   @focus="phoneFocus()" @input="phoneMask($event)">
                        </div>
                        <button type="submit" class="pc-submit-btn" :disabled="!canSubmit || submitting"
                                x-text="submitting ? 'Отправляем…' : 'Оформить заявку'"></button>
                    </form>

                    <div class="pc-form-error" x-show="error" x-text="error" x-cloak></div>
                </div>
            </div>

            {{-- Автоподбор по бюджету --}}
            <div class='pc-auto-build' x-show='mode === "auto" && !orderId' x-cloak>
                <div class='pc-auto-intro'>
                    <h2 class='pc-auto-title'>Подберём конфигурацию под бюджет</h2>
                    <p class='pc-auto-subtitle'>Укажите сумму и цель — мы сами соберём совместимые комплектующие. Если не получится, заявку получит менеджер.</p>
                </div>

                <form class='pc-auto-form' @submit.prevent='runAutoBuild()'>
                    <div class='pc-field'>
                        <label class='pc-label' for='pc-budget'>Бюджет, ₽</label>
                        <input class='pc-input' id='pc-budget' type='number' min='1000' step='100' x-model='autoForm.budget' required placeholder='Например, 100000'>
                    </div>
                    <div class='pc-field'>
                        <label class='pc-label' for='pc-purpose'>Для чего ПК</label>
                        <select class='pc-input pc-select' id='pc-purpose' x-model='autoForm.purpose'>
                            <option value=''>Не важно</option>
                            <option value='games'>Игры</option>
                            <option value='work'>Работа / учёба</option>
                            <option value='office'>Офис</option>
                        </select>
                    </div>
                    <div class='pc-field'>
                        <label class='pc-label' for='pc-wishes'>Пожелания</label>
                        <textarea class='pc-input pc-textarea' id='pc-wishes' rows='3' x-model='autoForm.wishes' maxlength='2000' placeholder='Например, хочу тихий корпус, SSD 1 ТБ'></textarea>
                    </div>
                    <button type='submit' class='pc-submit-btn' :disabled='autoLoading || !autoForm.budget' x-text='autoLoading ? "Подбираем…" : "Подобрать конфигурацию"'></button>
                </form>

                <div class='pc-parts-loading' x-show='autoLoading'>Подбираем конфигурацию…</div>

                {{-- Результат автоподбора --}}
                <div class='pc-auto-result' x-show='!autoLoading && autoBuild'>
                    <h3 class='pc-auto-result-title'>Подобранная конфигурация</h3>
                    <div class='pc-auto-items'>
                        <template x-for='(item, slot) in autoBuild?.items' :key='slot'>
                            <div class='pc-auto-item'>
                                <span class='pc-auto-item-slot' x-text='slotTitle(slot)'></span>
                                <span class='pc-auto-item-name' x-text='item.name'></span>
                                <span class='pc-auto-item-price' x-text='fmtPrice(item.price)'></span>
                            </div>
                        </template>
                    </div>
                    <div class='pc-auto-total'>
                        <span>Итого</span>
                        <span x-text='fmtPrice(autoBuild?.total)'></span>
                    </div>
                    <form class='pc-order-form' @submit.prevent='submitAutoBuildOrder()'>
                        <div class='pc-field'>
                            <label class='pc-label' for='pc-auto-name'>Имя</label>
                            <input class='pc-input' id='pc-auto-name' type='text' x-model='form.name' required maxlength='255' placeholder='Как к вам обращаться'>
                        </div>
                        <div class='pc-field'>
                            <label class='pc-label' for='pc-auto-phone'>Телефон</label>
                            <input class='pc-input' id='pc-auto-phone' type='tel' x-model='form.phone' required maxlength='18'
                                   placeholder='+7 (___) ___-__-__' @focus='phoneFocus()' @input='phoneMask($event)'>
                        </div>
                        <button type='submit' class='pc-submit-btn' :disabled='!canAutoSubmit || submitting'
                                x-text='submitting ? "Отправляем…" : "Оформить заявку на эту сборку"'></button>
                    </form>
                    <button type='button' class='pc-reset-btn' @click='resetAutoBuild()'>Подобрать заново</button>
                </div>

                {{-- Не удалось подобрать — заявка менеджеру --}}
                <div class='pc-auto-fallback' x-show='!autoLoading && autoError'>
                    <div class='pc-auto-fallback-icon'>🛠</div>
                    <h3>Не удалось подобрать в автоматическом режиме</h3>
                    <p x-text='autoError'></p>
                    <p>Оставьте заявку — менеджер подберёт комплектующие вручную.</p>

                    <form class='pc-order-form' @submit.prevent='submitManagerRequest()'>
                        <div class='pc-field'>
                            <label class='pc-label' for='pc-fallback-name'>Имя</label>
                            <input class='pc-input' id='pc-fallback-name' type='text' x-model='form.name' required maxlength='255' placeholder='Как к вам обращаться'>
                        </div>
                        <div class='pc-field'>
                            <label class='pc-label' for='pc-fallback-phone'>Телефон</label>
                            <input class='pc-input' id='pc-fallback-phone' type='tel' x-model='form.phone' required maxlength='18'
                                   placeholder='+7 (___) ___-__-__' @focus='phoneFocus()' @input='phoneMask($event)'>
                        </div>
                        <div class='pc-field'>
                            <label class='pc-label' for='pc-fallback-budget'>Бюджет, ₽</label>
                            <input class='pc-input' id='pc-fallback-budget' type='number' x-model='autoForm.budget' readonly>
                        </div>
                        <div class='pc-field'>
                            <label class='pc-label' for='pc-fallback-purpose'>Цель</label>
                            <input class='pc-input' id='pc-fallback-purpose' type='text' :value='purposeLabel(autoForm.purpose)' readonly>
                        </div>
                        <div class='pc-field'>
                            <label class='pc-label' for='pc-fallback-wishes'>Пожелания</label>
                            <textarea class='pc-input pc-textarea' id='pc-fallback-wishes' rows='3' x-model='autoForm.wishes' maxlength='2000' readonly></textarea>
                        </div>
                        <button type='submit' class='pc-submit-btn' :disabled='!canFallbackSubmit || submitting'
                                x-text='submitting ? "Отправляем…" : "Отправить заявку менеджеру"'></button>
                    </form>
                    <button type='button' class='pc-reset-btn' @click='resetAutoBuild()'>Попробовать другой бюджет</button>
                </div>

                <div class='pc-form-error' x-show='error' x-text='error' x-cloak></div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('pcConfigurator', (city) => ({
            city: city || null,
            mode: 'manual',
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
            autoForm: { budget: '', purpose: '', wishes: '' },
            autoBuild: null,
            autoError: '',
            autoLoading: false,

            init() {
                const mq = window.matchMedia('(max-width: 767px)');
                this.isMobile = mq.matches;
                mq.addEventListener('change', (e) => { this.isMobile = e.matches; });

                try {
                    const saved = JSON.parse(localStorage.getItem('pc_build') || '{}');
                    if (saved && typeof saved === 'object') this.build = this.migrateBuild(saved);
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
                const chosen = this.build[slot.id];
                if (!chosen) return slot.title;
                if (Array.isArray(chosen)) {
                    if (!chosen.length) return slot.title;
                    return slot.title + ': ' + chosen.map(p => {
                        const qty = p.qty || 1;
                        return qty > 1 ? `${p.name} ×${qty}` : p.name;
                    }).join(', ');
                }
                return slot.title + ': ' + chosen.name;
            },

            isMulti(slotId) {
                return ['ram', 'storage', 'extra'].includes(slotId);
            },

            hasSlotItems(slotId) {
                const val = this.build[slotId];
                if (!val) return false;
                if (Array.isArray(val)) return val.length > 0;
                return true;
            },

            isSelected(slot, part) {
                const val = this.build[slot.id];
                if (!val) return false;
                if (Array.isArray(val)) return val.some(p => p.id === part.id);
                return val.id === part.id;
            },

            migrateBuild(saved) {
                const out = {};
                for (const [slot, val] of Object.entries(saved)) {
                    if (!val) continue;
                    if (this.isMulti(slot)) {
                        if (Array.isArray(val)) {
                            out[slot] = val.filter(p => p && p.id).map(p => ({ ...p, qty: p.qty || 1 }));
                        } else if (val.id) {
                            out[slot] = [{ ...val, qty: val.qty || 1 }];
                        }
                    } else {
                        if (Array.isArray(val)) out[slot] = val[0] || null;
                        else out[slot] = val;
                    }
                }
                return out;
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
                for (const [slot, val] of Object.entries(this.build)) {
                    if (!val) continue;
                    if (Array.isArray(val)) ids[slot] = val.map(p => p.id);
                    else ids[slot] = val.id;
                }
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
                if (this.isMulti(slot.id)) {
                    const list = this.build[slot.id] || [];
                    const idx = list.findIndex(p => p.id === part.id);
                    const max = this.maxMultiQty(slot, part, idx >= 0 ? part.id : null);
                    if (idx < 0) {
                        if (max >= 1) {
                            list.push({ id: part.id, name: part.name, price: part.price, qty: 1 });
                        }
                    } else if (list[idx].qty < max) {
                        list[idx].qty += 1;
                    }
                    if (list.length) this.build[slot.id] = list;
                    else delete this.build[slot.id];
                } else {
                    const saved = { id: part.id, name: part.name, price: part.price };
                    if (slot.id === 'motherboard' && part.attributes && part.attributes.memory_slots) {
                        saved.memory_slots = parseInt(part.attributes.memory_slots, 10) || null;
                    }
                    this.build[slot.id] = saved;
                }
                this.saveBuild();

                if (this.isMulti(slot.id)) {
                    this.parts = this.parts.map(p => ({ ...p }));
                    return;
                }

                this.advanceSlot(slot);
            },

            maxMultiQty(slot, part, excludePartId = null) {
                const stock = Math.max(1, Math.floor(part.stock || 1));
                if (slot.id !== 'ram') return stock;
                const mb = this.build['motherboard'];
                const slots = mb && mb.memory_slots ? parseInt(mb.memory_slots, 10) : null;
                if (!slots || isNaN(slots)) return stock;
                const list = this.build['ram'] || [];
                const used = list.reduce((sum, p) => sum + (p.id === excludePartId ? 0 : (p.qty || 1)), 0);
                return Math.max(0, Math.min(stock, slots - used));
            },

            qtyOf(slot, part) {
                if (!this.isMulti(slot.id)) return 1;
                const list = this.build[slot.id] || [];
                const item = list.find(p => p.id === part.id);
                return item ? item.qty : 0;
            },

            changeQty(slot, part, delta) {
                if (!this.isMulti(slot.id)) return;
                const list = this.build[slot.id] || [];
                const idx = list.findIndex(p => p.id === part.id);
                const max = this.maxMultiQty(slot, part, idx >= 0 ? part.id : null);
                if (idx < 0) {
                    if (delta > 0 && max >= 1) {
                        list.push({ id: part.id, name: part.name, price: part.price, qty: 1 });
                        if (list.length) this.build[slot.id] = list;
                        this.saveBuild();
                        this.parts = this.parts.map(p => ({ ...p }));
                    }
                    return;
                }
                const next = list[idx].qty + delta;
                if (next <= 0) {
                    list.splice(idx, 1);
                    if (!list.length) delete this.build[slot.id];
                } else if (next <= max) {
                    list[idx].qty = next;
                }
                this.saveBuild();
                this.parts = this.parts.map(p => ({ ...p }));
            },

            advanceSlot(slot) {
                const idx = this.slots.indexOf(slot);
                const next = this.slots.slice(idx + 1).find(s => !s.empty && !this.hasSlotItems(s.id))
                    || this.slots.find(s => !s.empty && !this.hasSlotItems(s.id));
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
                return Object.values(this.build).reduce((sum, val) => {
                    if (Array.isArray(val)) return sum + val.reduce((s, p) => s + (p.price || 0) * (p.qty || 1), 0);
                    return sum + (val.price || 0);
                }, 0);
            },

            get canSubmit() {
                if (!this.form.name.trim() || !this.form.phone.trim()) return false;
                if (!this.hasBuild) return false;
                return this.slots
                    .filter(s => s.required && !s.empty)
                    .every(s => this.hasSlotItems(s.id));
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
                if (a.height_mm) out.push(a.height_mm + ' мм');
                if (a.cooler_clearance_mm) out.push('кулер до ' + a.cooler_clearance_mm + ' мм');
                return out.slice(0, 3);
            },

            fmtPrice(price) {
                if (price === null || price === undefined) return 'Цена по запросу';
                return new Intl.NumberFormat('ru-RU').format(Math.round(price)) + ' ₽';
            },

            shortName(slotId) {
                const val = this.build[slotId];
                if (!val) return '';
                if (Array.isArray(val)) {
                    if (!val.length) return '';
                    const totalQty = val.reduce((s, p) => s + (p.qty || 1), 0);
                    const first = val[0].name;
                    const suffix = totalQty > 1 ? ` ×${totalQty}` : '';
                    const extra = val.length > 1 ? ' + ещё ' + (val.length - 1) : '';
                    const base = first.length > 12 ? first.slice(0, 11) + '…' : first;
                    return base + suffix + extra;
                }
                return val.name.length > 12 ? val.name.slice(0, 11) + '…' : val.name;
            },

            slotClass(slotId) {
                const slot = this.slots.find(s => s.id === slotId);
                return {
                    'pc-slot--selected': this.hasSlotItems(slotId),
                    'pc-slot--active': this.activeSlot === slotId,
                    'pc-slot--soon': slot ? slot.empty : false,
                };
            },

            caseStyle() {
                if (this.isMobile) return '';
                const poses = {
                    case:        { t: 'rotateY(-22deg) rotateX(10deg)' },
                    cpu:         { t: 'rotateY(-30deg) rotateX(12deg)' },
                    cooler:      { t: 'rotateY(-30deg) rotateX(12deg)' },
                    motherboard: { t: 'rotateY(-22deg) rotateX(10deg)' },
                    gpu:         { t: 'rotateY(30deg) rotateX(-10deg)' },
                    ram:         { t: 'rotateY(-30deg) rotateX(10deg)' },
                    storage:     { t: 'rotateY(22deg) rotateX(-10deg)' },
                    psu:         { t: 'rotateY(20deg) rotateX(-16deg)' },
                    extra:       { t: 'rotateY(-20deg) rotateX(-12deg)' },
                };
                const base = { t: 'rotateY(-20deg) rotateX(8deg)' };
                const pose = (this.activeSlot && poses[this.activeSlot]) || base;
                return 'transform: ' + pose.t + '; transform-origin: 50% 50%;';
            },

            phoneFocus() {
                if (!this.form.phone) this.form.phone = '+7 (';
            },

            // Маска +7 (XXX) XXX-XX-XX: 7/8 в начале заменяются на +7,
            // остальные цифры (макс. 10) встают по формату плейсхолдера.
            phoneMask(event) {
                let digits = event.target.value.replace(/\D/g, '');
                if (digits.startsWith('8') || digits.startsWith('7')) digits = digits.slice(1);
                digits = digits.slice(0, 10);
                if (!digits.length) { this.form.phone = ''; return; }
                let out = '+7 (' + digits.slice(0, 3);
                if (digits.length >= 3) out += ') ';
                if (digits.length > 3) out += digits.slice(3, 6);
                if (digits.length > 6) out += '-' + digits.slice(6, 8);
                if (digits.length > 8) out += '-' + digits.slice(8, 10);
                this.form.phone = out;
            },

            slotTitle(slotId) {
                const slot = this.slots.find(s => s.id === slotId);
                return slot ? slot.title : slotId;
            },

            purposeLabel(purpose) {
                const map = { games: 'Игры', work: 'Работа / учёба', office: 'Офис' };
                return map[purpose] || 'Не важно';
            },

            get canAutoSubmit() {
                return this.form.name.trim() && this.form.phone.trim() && this.autoBuild;
            },

            get canFallbackSubmit() {
                return this.form.name.trim() && this.form.phone.trim();
            },

            resetAutoBuild() {
                this.autoBuild = null;
                this.autoError = '';
                this.error = '';
            },

            async runAutoBuild() {
                if (this.autoLoading) return;
                this.autoLoading = true;
                this.autoBuild = null;
                this.autoError = '';
                this.error = '';

                try {
                    const res = await fetch('/api/pc/auto-build', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            budget: Number(this.autoForm.budget),
                            purpose: this.autoForm.purpose || null,
                            wishes: this.autoForm.wishes.trim() || null,
                        }),
                    });
                    const data = await res.json();

                    if (res.status === 200 && data.success && data.data) {
                        this.autoBuild = data.data;
                        this.build = {};
                        for (const [slot, item] of Object.entries(data.data.items)) {
                            const base = { id: item.id, name: item.name, price: item.price };
                            this.build[slot] = this.isMulti(slot) ? [base] : base;
                        }
                        this.saveBuild();
                    } else if (res.status === 422) {
                        const details = data.errors ? Object.values(data.errors).flat().join(' ') : '';
                        this.error = (data.message || 'Проверьте данные формы.') + (details ? ' ' + details : '');
                    } else {
                        this.autoError = data.reason || 'Не удалось подобрать конфигурацию в указанный бюджет.';
                    }
                } catch (e) {
                    this.error = 'Ошибка сети. Проверьте подключение и попробуйте ещё раз.';
                } finally {
                    this.autoLoading = false;
                }
            },

            buildItems() {
                const items = [];
                for (const val of Object.values(this.build)) {
                    if (Array.isArray(val)) {
                        for (const p of val) items.push({ product_id: p.id, quantity: p.qty || 1 });
                    } else if (val) {
                        items.push({ product_id: val.id, quantity: 1 });
                    }
                }
                return items;
            },

            async submitAutoBuildOrder() {
                if (!this.canAutoSubmit || this.submitting) return;
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
                            items: this.buildItems(),
                        }),
                    });
                    const data = await res.json();
                    if (res.status === 201 && data.success) {
                        this.orderId = data.order_id;
                        this.build = {};
                        this.autoBuild = null;
                        this.autoForm = { budget: '', purpose: '', wishes: '' };
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

            async submitManagerRequest() {
                if (!this.canFallbackSubmit || this.submitting) return;
                this.submitting = true;
                this.error = '';
                try {
                    const res = await fetch('/pc/manager-request', {
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
                            budget: this.autoForm.budget ? Number(this.autoForm.budget) : null,
                            purpose: this.purposeLabel(this.autoForm.purpose),
                            wishes: this.autoForm.wishes.trim() || null,
                        }),
                    });
                    const data = await res.json();
                    if (res.status === 201 && data.success) {
                        this.orderId = data.order_id;
                        this.build = {};
                        this.autoBuild = null;
                        this.autoError = '';
                        this.autoForm = { budget: '', purpose: '', wishes: '' };
                        localStorage.removeItem('pc_build');
                        this.form = { name: '', phone: '' };
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
                            items: this.buildItems(),
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
