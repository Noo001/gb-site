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

                                <template x-if="activeSlot === slot.id && !slot.empty">
                                <div class="pc-step-body">
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
                                </template>
                            </div>
                        </template>
                    </div>

                    {{-- Схематичный корпус настоящим 3D-кубом --}}
                    <div class="pc-case-panel">
                        <div class="pc-case-scene">
                            <div class="pc-case-3d" :style="caseStyle()">
                                <div class="pc-case-face pc-case-face--front">
                                    <svg class="pc-case-svg" viewBox="0 0 520 720" xmlns="http://www.w3.org/2000/svg" aria-label="Схема системного блока">
                                        <defs>
                                            <linearGradient id="pcCaseBody" x1="0" y1="0" x2="0" y2="1">
                                                <stop offset="0" stop-color="#111827" />
                                                <stop offset="1" stop-color="#1f2937" />
                                            </linearGradient>
                                            <linearGradient id="pcGlassGrad" x1="0" y1="0" x2="1" y2="1">
                                                <stop offset="0" stop-color="rgba(255,255,255,0.10)" />
                                                <stop offset="0.5" stop-color="rgba(255,255,255,0.03)" />
                                                <stop offset="1" stop-color="rgba(255,255,255,0.07)" />
                                            </linearGradient>
                                            <linearGradient id="pcMb" x1="0" y1="0" x2="1" y2="1">
                                                <stop offset="0" stop-color="#0f1f0f" />
                                                <stop offset="1" stop-color="#162816" />
                                            </linearGradient>
                                            <linearGradient id="pcGpuGrad" x1="0" y1="0" x2="0" y2="1">
                                                <stop offset="0" stop-color="#27272a" />
                                                <stop offset="1" stop-color="#18181b" />
                                            </linearGradient>
                                            <linearGradient id="pcRamGrad" x1="0" y1="0" x2="0" y2="1">
                                                <stop offset="0" stop-color="#3f3f46" />
                                                <stop offset="1" stop-color="#27272a" />
                                            </linearGradient>
                                            <linearGradient id="pcPsuGrad" x1="0" y1="0" x2="0" y2="1">
                                                <stop offset="0" stop-color="#374151" />
                                                <stop offset="1" stop-color="#1f2937" />
                                            </linearGradient>
                                            <linearGradient id="pcRgb" x1="0" y1="0" x2="1" y2="0">
                                                <stop offset="0" stop-color="#0cc0df" />
                                                <stop offset="0.5" stop-color="#e879f9" />
                                                <stop offset="1" stop-color="#0cc0df" />
                                            </linearGradient>
                                            <filter id="pcGlow" x="-50%" y="-50%" width="200%" height="200%">
                                                <feGaussianBlur stdDeviation="4" result="coloredBlur" />
                                                <feMerge><feMergeNode in="coloredBlur" /><feMergeNode in="SourceGraphic" /></feMerge>
                                            </filter>
                                            <filter id="pcShadow" x="-20%" y="-20%" width="140%" height="140%">
                                                <feDropShadow dx="0" dy="2" stdDeviation="2" flood-color="rgba(0,0,0,0.5)" />
                                            </filter>
                                        </defs>

                                        {{-- Корпус: внешняя рамка + ножки --}}
                                        <g class="pc-slot pc-slot-case" data-slot="case" :class="slotClass('case')"
                                           @mouseenter="hoveredSlot = 'case'" @mouseleave="hoveredSlot = null" @click="activateSlot('case')">
                                            <rect class="pc-shape pc-case-body" x="10" y="10" width="500" height="700" rx="14" fill="url(#pcCaseBody)" stroke="#374151" stroke-width="2" />
                                            <rect x="35" y="710" width="40" height="8" rx="2" fill="#111827" />
                                            <rect x="445" y="710" width="40" height="8" rx="2" fill="#111827" />
                                            <rect x="35" y="2" width="40" height="8" rx="2" fill="#374151" />
                                            <rect x="445" y="2" width="40" height="8" rx="2" fill="#374151" />
                                            <text class="pc-slot-label" x="260" y="695" x-show="build['case']" x-text="shortName('case')"></text>
                                        </g>

                                        {{-- Передняя панель с вентиляторами, USB и кнопкой --}}
                                        <rect x="10" y="10" width="70" height="700" rx="14" fill="#1f2937" stroke="#374151" stroke-width="1" />
                                        <g transform="translate(45,140)">
                                            <circle r="26" fill="#111827" stroke="#4b5563" stroke-width="2" />
                                            <g class="pc-fan-blades"><path d="M0 0 q0 -22 0 -22" /><path d="M0 0 q0 -22 0 -22" transform="rotate(45)" /><path d="M0 0 q0 -22 0 -22" transform="rotate(90)" /><path d="M0 0 q0 -22 0 -22" transform="rotate(135)" /><path d="M0 0 q0 -22 0 -22" transform="rotate(180)" /><path d="M0 0 q0 -22 0 -22" transform="rotate(225)" /><path d="M0 0 q0 -22 0 -22" transform="rotate(270)" /><path d="M0 0 q0 -22 0 -22" transform="rotate(315)" /></g>
                                            <circle r="8" fill="#0cc0df" opacity="0.6" filter="url(#pcGlow)" />
                                        </g>
                                        <g transform="translate(45,280)">
                                            <circle r="26" fill="#111827" stroke="#4b5563" stroke-width="2" />
                                            <g class="pc-fan-blades"><path d="M0 0 q0 -22 0 -22" /><path d="M0 0 q0 -22 0 -22" transform="rotate(45)" /><path d="M0 0 q0 -22 0 -22" transform="rotate(90)" /><path d="M0 0 q0 -22 0 -22" transform="rotate(135)" /><path d="M0 0 q0 -22 0 -22" transform="rotate(180)" /><path d="M0 0 q0 -22 0 -22" transform="rotate(225)" /><path d="M0 0 q0 -22 0 -22" transform="rotate(270)" /><path d="M0 0 q0 -22 0 -22" transform="rotate(315)" /></g>
                                        </g>
                                        <g transform="translate(45,420)">
                                            <circle r="26" fill="#111827" stroke="#4b5563" stroke-width="2" />
                                            <g class="pc-fan-blades"><path d="M0 0 q0 -22 0 -22" /><path d="M0 0 q0 -22 0 -22" transform="rotate(45)" /><path d="M0 0 q0 -22 0 -22" transform="rotate(90)" /><path d="M0 0 q0 -22 0 -22" transform="rotate(135)" /><path d="M0 0 q0 -22 0 -22" transform="rotate(180)" /><path d="M0 0 q0 -22 0 -22" transform="rotate(225)" /><path d="M0 0 q0 -22 0 -22" transform="rotate(270)" /><path d="M0 0 q0 -22 0 -22" transform="rotate(315)" /></g>
                                        </g>
                                        <rect x="30" y="55" width="32" height="8" rx="2" fill="#111827" stroke="#4b5563" />
                                        <circle class="pc-led" cx="46" cy="90" r="6" fill="#0cc0df" filter="url(#pcGlow)" />
                                        <rect x="34" y="600" width="24" height="6" rx="1" fill="#111827" stroke="#4b5563" />
                                        <rect x="34" y="615" width="24" height="6" rx="1" fill="#111827" stroke="#4b5563" />

                                        {{-- Внутренняя камера --}}
                                        <rect x="24" y="24" width="472" height="672" rx="8" fill="#0b0f19" />

                                        {{-- Материнская плата --}}
                                        <g class="pc-slot" data-slot="motherboard" :class="slotClass('motherboard')"
                                           @mouseenter="hoveredSlot = 'motherboard'" @mouseleave="hoveredSlot = null" @click="activateSlot('motherboard')">
                                            <rect class="pc-shape pc-mb" x="100" y="80" width="320" height="420" rx="6" fill="url(#pcMb)" stroke="#1f5128" stroke-width="2" filter="url(#pcShadow)" />
                                            <g stroke="#1f5128" stroke-width="1.5" fill="none" opacity="0.6">
                                                <path d="M120 120 H400 M120 160 H400 M120 200 H400 M120 240 H400 M120 280 H400" />
                                                <path d="M140 120 V380 M200 120 V380 M260 120 V380 M320 120 V380" />
                                            </g>
                                            <text class="pc-slot-label" x="260" y="485" x-show="build['motherboard']" x-text="shortName('motherboard')"></text>
                                        </g>

                                        {{-- CPU + кулер --}}
                                        <g class="pc-slot" data-slot="cpu" :class="slotClass('cpu')"
                                           @mouseenter="hoveredSlot = 'cpu'" @mouseleave="hoveredSlot = null" @click="activateSlot('cpu')">
                                            <rect class="pc-shape" x="146" y="116" width="68" height="68" rx="4" fill="#18181b" stroke="#52525b" stroke-width="2" filter="url(#pcShadow)" />
                                            <rect x="154" y="124" width="52" height="52" rx="2" fill="#27272a" stroke="#3f3f46" />
                                            <circle cx="180" cy="150" r="18" fill="#0cc0df" opacity="0.4" filter="url(#pcGlow)" />
                                            <g class="pc-fan-blades" transform="translate(180,150)"><path d="M0 0 q0 -16 0 -16" /><path d="M0 0 q0 -16 0 -16" transform="rotate(45)" /><path d="M0 0 q0 -16 0 -16" transform="rotate(90)" /><path d="M0 0 q0 -16 0 -16" transform="rotate(135)" /><path d="M0 0 q0 -16 0 -16" transform="rotate(180)" /><path d="M0 0 q0 -16 0 -16" transform="rotate(225)" /><path d="M0 0 q0 -16 0 -16" transform="rotate(270)" /><path d="M0 0 q0 -16 0 -16" transform="rotate(315)" /></g>
                                            <text class="pc-slot-label" x="180" y="200" x-show="build['cpu']" x-text="shortName('cpu')"></text>
                                        </g>

                                        {{-- Кулер (башенный) --}}
                                        <g class="pc-slot" data-slot="cooler" :class="slotClass('cooler')"
                                           @mouseenter="hoveredSlot = 'cooler'" @mouseleave="hoveredSlot = null" @click="activateSlot('cooler')">
                                            <rect class="pc-shape pc-cooler" x="138" y="108" width="84" height="84" rx="4" fill="#27272a" stroke="#3f3f46" stroke-width="2" filter="url(#pcShadow)" />
                                            <g stroke="#4b5563" stroke-width="2" opacity="0.6">
                                                <line x1="142" y1="124" x2="218" y2="124" /><line x1="142" y1="140" x2="218" y2="140" />
                                                <line x1="142" y1="156" x2="218" y2="156" /><line x1="142" y1="172" x2="218" y2="172" />
                                            </g>
                                            <text class="pc-slot-label" x="222" y="110" style="text-anchor: end" x-show="build['cooler']" x-text="shortName('cooler')"></text>
                                        </g>

                                        {{-- RAM --}}
                                        <g class="pc-slot" data-slot="ram" :class="slotClass('ram')"
                                           @mouseenter="hoveredSlot = 'ram'" @mouseleave="hoveredSlot = null" @click="activateSlot('ram')">
                                            <g transform="translate(300,100)">
                                                <rect class="pc-shape" x="0" y="0" width="18" height="120" rx="2" fill="url(#pcRamGrad)" stroke="#52525b" stroke-width="1.5" />
                                                <rect x="0" y="18" width="18" height="22" fill="#0cc0df" opacity="0.5" /><rect x="0" y="46" width="18" height="22" fill="#0cc0df" opacity="0.5" /><rect x="0" y="74" width="18" height="22" fill="#0cc0df" opacity="0.5" />
                                                <rect class="pc-shape" x="26" y="0" width="18" height="120" rx="2" fill="url(#pcRamGrad)" stroke="#52525b" stroke-width="1.5" />
                                                <rect x="26" y="18" width="18" height="22" fill="#e879f9" opacity="0.5" /><rect x="26" y="46" width="18" height="22" fill="#e879f9" opacity="0.5" /><rect x="26" y="74" width="18" height="22" fill="#e879f9" opacity="0.5" />
                                            </g>
                                            <g transform="translate(340,100)">
                                                <rect class="pc-shape" x="0" y="0" width="18" height="120" rx="2" fill="url(#pcRamGrad)" stroke="#52525b" stroke-width="1.5" />
                                                <rect x="0" y="18" width="18" height="22" fill="#0cc0df" opacity="0.5" /><rect x="0" y="46" width="18" height="22" fill="#0cc0df" opacity="0.5" /><rect x="0" y="74" width="18" height="22" fill="#0cc0df" opacity="0.5" />
                                                <rect class="pc-shape" x="26" y="0" width="18" height="120" rx="2" fill="url(#pcRamGrad)" stroke="#52525b" stroke-width="1.5" />
                                                <rect x="26" y="18" width="18" height="22" fill="#e879f9" opacity="0.5" /><rect x="26" y="46" width="18" height="22" fill="#e879f9" opacity="0.5" /><rect x="26" y="74" width="18" height="22" fill="#e879f9" opacity="0.5" />
                                            </g>
                                            <text class="pc-slot-label" x="338" y="95" style="text-anchor: start" x-show="build['ram']" x-text="shortName('ram')"></text>
                                        </g>

                                        {{-- GPU --}}
                                        <g class="pc-slot" data-slot="gpu" :class="slotClass('gpu')"
                                           @mouseenter="hoveredSlot = 'gpu'" @mouseleave="hoveredSlot = null" @click="activateSlot('gpu')">
                                            <rect class="pc-shape pc-gpu" x="120" y="260" width="280" height="70" rx="4" fill="url(#pcGpuGrad)" stroke="#3f3f46" stroke-width="2" filter="url(#pcShadow)" />
                                            <rect x="120" y="318" width="280" height="12" fill="#18181b" />
                                            <g transform="translate(190,295)"><circle r="22" fill="#111827" stroke="#52525b" stroke-width="2" /><g class="pc-fan-blades"><path d="M0 0 q0 -19 0 -19" /><path d="M0 0 q0 -19 0 -19" transform="rotate(45)" /><path d="M0 0 q0 -19 0 -19" transform="rotate(90)" /><path d="M0 0 q0 -19 0 -19" transform="rotate(135)" /><path d="M0 0 q0 -19 0 -19" transform="rotate(180)" /><path d="M0 0 q0 -19 0 -19" transform="rotate(225)" /><path d="M0 0 q0 -19 0 -19" transform="rotate(270)" /><path d="M0 0 q0 -19 0 -19" transform="rotate(315)" /></g></g>
                                            <g transform="translate(310,295)"><circle r="22" fill="#111827" stroke="#52525b" stroke-width="2" /><g class="pc-fan-blades"><path d="M0 0 q0 -19 0 -19" /><path d="M0 0 q0 -19 0 -19" transform="rotate(45)" /><path d="M0 0 q0 -19 0 -19" transform="rotate(90)" /><path d="M0 0 q0 -19 0 -19" transform="rotate(135)" /><path d="M0 0 q0 -19 0 -19" transform="rotate(180)" /><path d="M0 0 q0 -19 0 -19" transform="rotate(225)" /><path d="M0 0 q0 -19 0 -19" transform="rotate(270)" /><path d="M0 0 q0 -19 0 -19" transform="rotate(315)" /></g></g>
                                            <text x="260" y="280" fill="#71717a" font-size="12" text-anchor="middle" font-family="Arial">GPU</text>
                                            <text class="pc-slot-label" x="260" y="360" x-show="build['gpu']" x-text="shortName('gpu')"></text>
                                        </g>

                                        {{-- SSD M.2 --}}
                                        <g class="pc-slot" data-slot="storage" :class="slotClass('storage')"
                                           @mouseenter="hoveredSlot = 'storage'" @mouseleave="hoveredSlot = null" @click="activateSlot('storage')">
                                            <rect class="pc-shape" x="130" y="360" width="80" height="14" rx="2" fill="#27272a" stroke="#3f3f46" stroke-width="1.5" />
                                            <rect x="138" y="362" width="30" height="10" fill="#0cc0df" opacity="0.4" />
                                            <text class="pc-slot-label" x="170" y="395" x-show="build['storage']" x-text="shortName('storage')"></text>
                                        </g>

                                        {{-- Блок питания --}}
                                        <g class="pc-slot" data-slot="psu" :class="slotClass('psu')"
                                           @mouseenter="hoveredSlot = 'psu'" @mouseleave="hoveredSlot = null" @click="activateSlot('psu')">
                                            <rect class="pc-shape pc-psu" x="110" y="520" width="220" height="90" rx="6" fill="url(#pcPsuGrad)" stroke="#4b5563" stroke-width="2" filter="url(#pcShadow)" />
                                            <g transform="translate(170,565)"><circle r="28" fill="#111827" stroke="#4b5563" stroke-width="2" /><g stroke="#6b7280" stroke-width="1.5"><line x1="0" y1="-24" x2="0" y2="24" /><line x1="-24" y1="0" x2="24" y2="0" /><line x1="-17" y1="-17" x2="17" y2="17" /><line x1="-17" y1="17" x2="17" y2="-17" /></g></g>
                                            <text x="250" y="560" fill="#9ca3af" font-size="12" text-anchor="middle" font-family="Arial">PSU</text>
                                            <text class="pc-slot-label" x="270" y="600" x-show="build['psu']" x-text="shortName('psu')"></text>
                                        </g>

                                        {{-- Доп. вентилятор (extra) --}}
                                        <g class="pc-slot" data-slot="extra" :class="slotClass('extra')"
                                           @mouseenter="hoveredSlot = 'extra'" @mouseleave="hoveredSlot = null" @click="activateSlot('extra')">
                                            <circle class="pc-shape" cx="390" cy="540" r="28" fill="#111827" stroke="#4b5563" stroke-width="2" />
                                            <g class="pc-fan-blades" transform="translate(390,540)"><path d="M0 0 q0 -24 0 -24" /><path d="M0 0 q0 -24 0 -24" transform="rotate(45)" /><path d="M0 0 q0 -24 0 -24" transform="rotate(90)" /><path d="M0 0 q0 -24 0 -24" transform="rotate(135)" /><path d="M0 0 q0 -24 0 -24" transform="rotate(180)" /><path d="M0 0 q0 -24 0 -24" transform="rotate(225)" /><path d="M0 0 q0 -24 0 -24" transform="rotate(270)" /><path d="M0 0 q0 -24 0 -24" transform="rotate(315)" /></g>
                                            <text class="pc-slot-label" x="390" y="585" x-show="build['extra']" x-text="shortName('extra')"></text>
                                        </g>

                                        {{-- RGB-подсветка --}}
                                        <rect class="pc-rgb" x="24" y="690" width="472" height="6" rx="3" fill="url(#pcRgb)" opacity="0.8" filter="url(#pcGlow)" />
                                        <rect class="pc-rgb" x="24" y="24" width="6" height="672" rx="3" fill="url(#pcRgb)" opacity="0.6" filter="url(#pcGlow)" />

                                        {{-- Стеклянная боковая панель --}}
                                        <rect class="pc-glass" x="24" y="24" width="472" height="672" rx="8" fill="url(#pcGlassGrad)" stroke="rgba(255,255,255,0.12)" stroke-width="2" />
                                        <path class="pc-glass-glare" d="M24 24 L180 24 L24 300 Z" />
                                    </svg>
                                </div>
                                <div class="pc-case-face pc-case-face--back"></div>
                                <div class="pc-case-face pc-case-face--top"></div>
                                <div class="pc-case-face pc-case-face--bottom"></div>
                                <div class="pc-case-face pc-case-face--left"></div>
                                <div class="pc-case-face pc-case-face--right"></div>
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
                    case:        { t: 'rotateY(-26deg) rotateX(10deg)', o: '50% 50%' },
                    cpu:         { t: 'rotateY(-36deg) rotateX(14deg) scale(1.06)', o: '32% 26%' },
                    cooler:      { t: 'rotateY(-36deg) rotateX(14deg) scale(1.06)', o: '30% 24%' },
                    motherboard: { t: 'rotateY(-30deg) rotateX(12deg) scale(1.04)', o: '38% 42%' },
                    gpu:         { t: 'rotateY(28deg) rotateX(-12deg) scale(1.06)', o: '42% 48%' },
                    ram:         { t: 'rotateY(-38deg) rotateX(12deg) scale(1.06)', o: '52% 28%' },
                    storage:     { t: 'rotateY(22deg) rotateX(-14deg) scale(1.05)', o: '28% 60%' },
                    psu:         { t: 'rotateY(18deg) rotateX(-22deg) scale(1.04)', o: '40% 78%' },
                    extra:       { t: 'rotateY(-22deg) rotateX(-18deg) scale(1.05)', o: '50% 62%' },
                };
                const base = { t: 'rotateY(-26deg) rotateX(10deg)', o: '50% 50%' };
                const pose = (this.activeSlot && poses[this.activeSlot]) || base;
                return 'transform: ' + pose.t + '; transform-origin: ' + pose.o + ';';
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
