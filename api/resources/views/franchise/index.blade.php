@extends('layouts.franchise')

@section('title', 'Франшиза Gadget Bar — откройте магазин техники')
@section('meta_description', 'Франшиза Gadget Bar: паушальный взнос 100 000 ₽, роялти 1,6%, прибыль до 89 000 ₽/мес. Оставьте заявку на франшизу или закажите обратный звонок.')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
:root {
    --fr-accent: #5ce7ff;
    --fr-black: #000000;
    --fr-gray: #545152;
    --fr-light: #f5f5f5;
    --fr-white: #ffffff;
    --fr-border: #d9d9d9;
    --fr-radius: 24px;
    --fr-radius-sm: 16px;
}

.franchise-page {
    font-family: 'Inter', 'Segoe UI', system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
    color: var(--fr-black);
    line-height: 1.6;
    overflow-x: hidden;
}

.franchise-page * {
    box-sizing: border-box;
}

.fr-container {
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

/* Hero */
.fr-hero {
    position: relative;
    background: var(--fr-black);
    color: var(--fr-white);
    padding: 120px 0 140px;
    overflow: hidden;
}

.fr-hero::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 800px;
    height: 800px;
    background: radial-gradient(circle, rgba(92, 231, 255, 0.25) 0%, transparent 70%);
    pointer-events: none;
}

.fr-hero-content {
    position: relative;
    z-index: 2;
    max-width: 700px;
}

.fr-hero h1 {
    font-size: clamp(36px, 6vw, 68px);
    font-weight: 800;
    line-height: 1.05;
    margin: 0 0 24px;
}

.fr-hero h1 span {
    color: var(--fr-accent);
}

.fr-hero p {
    font-size: clamp(18px, 2.2vw, 22px);
    color: rgba(255,255,255,0.85);
    margin: 0 0 40px;
    max-width: 560px;
}

.fr-hero-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
    margin-bottom: 40px;
}

.fr-badge {
    background: rgba(255,255,255,0.08);
    border: 1px solid rgba(255,255,255,0.15);
    border-radius: 999px;
    padding: 10px 20px;
    font-size: 15px;
    font-weight: 600;
}

.fr-hero-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
}

.fr-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 18px 32px;
    border-radius: 999px;
    font-size: 17px;
    font-weight: 700;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s, background 0.2s;
}

.fr-btn:hover {
    transform: translateY(-3px);
}

.fr-btn-primary {
    background: var(--fr-accent);
    color: var(--fr-black);
    box-shadow: 0 12px 32px rgba(92, 231, 255, 0.35);
}

.fr-btn-primary:hover {
    box-shadow: 0 16px 40px rgba(92, 231, 255, 0.5);
}

.fr-btn-outline {
    background: transparent;
    color: var(--fr-white);
    border: 2px solid rgba(255,255,255,0.3);
}

.fr-btn-outline:hover {
    border-color: var(--fr-accent);
    color: var(--fr-accent);
}

/* Section base */
.fr-section {
    padding: 100px 0;
}

.fr-section-title {
    font-size: clamp(28px, 4vw, 44px);
    font-weight: 800;
    margin: 0 0 16px;
    line-height: 1.15;
}

.fr-section-subtitle {
    font-size: 18px;
    color: var(--fr-gray);
    margin: 0 0 56px;
    max-width: 700px;
}

/* Brand story */
.fr-story {
    background: var(--fr-light);
}

.fr-story-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 64px;
    align-items: center;
}

.fr-story-image {
    position: relative;
    border-radius: var(--fr-radius);
    overflow: hidden;
    background: var(--fr-white);
    border: 1px solid var(--fr-border);
    min-height: 420px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.fr-story-image .brand-logo {
    width: auto;
    height: auto;
    max-width: 85%;
    max-height: 160px;
    object-fit: contain;
    position: relative;
    z-index: 2;
}

.fr-story-image::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(92,231,255,0.06), transparent);
    pointer-events: none;
}

.fr-story-text p {
    font-size: 17px;
    color: var(--fr-gray);
    margin: 0 0 20px;
}

.fr-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px;
    margin-top: 40px;
}

.fr-stat {
    background: var(--fr-white);
    border-radius: var(--fr-radius-sm);
    padding: 24px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.05);
}

.fr-stat-number {
    font-size: 32px;
    font-weight: 800;
    color: var(--fr-black);
    white-space: nowrap;
}

.fr-stat-number span {
    color: var(--fr-accent);
    white-space: nowrap;
}

.fr-stat-label {
    font-size: 14px;
    color: var(--fr-gray);
    margin-top: 6px;
}

/* Advantages */
.fr-advantages {
    background: var(--fr-white);
}

.fr-advantages-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 24px;
}

.fr-advantage {
    background: var(--fr-light);
    border-radius: var(--fr-radius-sm);
    padding: 32px;
    transition: transform 0.2s, box-shadow 0.2s;
}

.fr-advantage:hover {
    transform: translateY(-6px);
    box-shadow: 0 16px 40px rgba(0,0,0,0.08);
}

.fr-advantage-icon {
    width: 56px;
    height: 56px;
    border-radius: 16px;
    background: var(--fr-black);
    color: var(--fr-accent);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    margin-bottom: 20px;
}

.fr-advantage h3 {
    font-size: 20px;
    font-weight: 700;
    margin: 0 0 10px;
}

.fr-advantage p {
    font-size: 15px;
    color: var(--fr-gray);
    margin: 0;
}

/* Conditions */
.fr-conditions {
    background: var(--fr-black);
    color: var(--fr-white);
}

.fr-conditions .fr-section-subtitle {
    color: rgba(255,255,255,0.7);
}

.fr-conditions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 24px;
}

.fr-condition-card {
    background: rgba(255,255,255,0.06);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: var(--fr-radius-sm);
    padding: 32px;
}

.fr-condition-card h3 {
    font-size: 22px;
    font-weight: 700;
    margin: 0 0 12px;
    color: var(--fr-accent);
}

.fr-condition-card .price {
    font-size: 32px;
    font-weight: 800;
    margin: 0 0 16px;
}

.fr-condition-card ul {
    margin: 0;
    padding-left: 20px;
    color: rgba(255,255,255,0.85);
}

.fr-condition-card li {
    margin-bottom: 8px;
}

/* Calculator */
.fr-calculator {
    background: var(--fr-light);
}

.fr-calc-wrapper {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 40px;
    align-items: start;
}

.fr-calc-card {
    background: var(--fr-white);
    border-radius: var(--fr-radius);
    padding: 40px;
    box-shadow: 0 12px 40px rgba(0,0,0,0.06);
}

.fr-field {
    margin-bottom: 24px;
}

.fr-field label {
    display: block;
    font-weight: 600;
    margin-bottom: 10px;
    font-size: 15px;
}

.fr-field input[type="range"] {
    width: 100%;
    accent-color: var(--fr-accent);
}

.fr-field .value {
    display: inline-block;
    margin-top: 8px;
    font-weight: 700;
    color: var(--fr-black);
}

.fr-calc-result {
    background: var(--fr-black);
    color: var(--fr-white);
    border-radius: var(--fr-radius);
    padding: 40px;
    position: sticky;
    top: 24px;
}

.fr-calc-result h3 {
    font-size: 20px;
    font-weight: 700;
    margin: 0 0 24px;
    color: var(--fr-accent);
}

.fr-result-row {
    display: flex;
    justify-content: space-between;
    padding: 14px 0;
    border-bottom: 1px solid rgba(255,255,255,0.1);
    font-size: 16px;
}

.fr-result-row.total {
    border-bottom: none;
    padding-top: 24px;
    font-size: 22px;
    font-weight: 800;
}

.fr-result-row.total span:last-child {
    color: var(--fr-accent);
}

/* Forms */
.fr-forms {
    background: var(--fr-white);
}

.fr-forms-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 32px;
}

.fr-form-card {
    background: var(--fr-light);
    border-radius: var(--fr-radius);
    padding: 40px;
}

.fr-form-card h3 {
    font-size: 24px;
    font-weight: 800;
    margin: 0 0 8px;
}

.fr-form-card p {
    color: var(--fr-gray);
    margin: 0 0 28px;
}

.fr-input {
    width: 100%;
    padding: 16px 20px;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    font-size: 16px;
    margin-bottom: 16px;
    background: var(--fr-white);
    transition: border-color 0.2s, box-shadow 0.2s;
}

.fr-input:focus {
    outline: none;
    border-color: var(--fr-accent);
    box-shadow: 0 0 0 4px rgba(92, 231, 255, 0.15);
}

.fr-input::placeholder {
    color: #94a3b8;
}

.fr-textarea {
    min-height: 110px;
    resize: vertical;
}

.fr-alert {
    padding: 14px 18px;
    border-radius: 12px;
    margin-bottom: 18px;
    font-size: 15px;
}

.fr-alert-success {
    background: #dcfce7;
    color: #166534;
}

.fr-alert-error {
    background: #fee2e2;
    color: #991b1b;
}

.fr-footer {
    background: var(--fr-black);
    color: rgba(255,255,255,0.6);
    padding: 40px 0;
    text-align: center;
    font-size: 14px;
}

.fr-footer a {
    color: var(--fr-accent);
    text-decoration: none;
}

/* Responsive */
@media (max-width: 900px) {
    .fr-story-grid,
    .fr-calc-wrapper,
    .fr-forms-grid {
        grid-template-columns: 1fr;
    }

    .fr-stats {
        grid-template-columns: 1fr;
    }

    .fr-section {
        padding: 64px 0;
    }

    .fr-hero {
        padding: 80px 0 100px;
    }
}
</style>
@endpush

@section('content')
<div class="franchise-page">
    <!-- Hero -->
    <section class="fr-hero">
        <div class="fr-container">
            <div class="fr-hero-content">
                <div class="fr-hero-badges">
                    <div class="fr-badge">Паушальный взнос 100 000 ₽</div>
                    <div class="fr-badge">Роялти 1,6%</div>
                </div>
                <h1>Откройте свой <span>Gadget Bar</span></h1>
                <p>Франшиза магазина техники и аксессуаров с готовой моделью, обучением и поддержкой на всех этапах запуска.</p>
                <div class="fr-hero-buttons">
                    <a href="#calculator" class="fr-btn fr-btn-primary">Рассчитать прибыль</a>
                    <a href="#forms" class="fr-btn fr-btn-outline">Оставить заявку</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Brand story -->
    <section class="fr-section fr-story" id="about">
        <div class="fr-container">
            <div class="fr-story-grid">
                <div class="fr-story-image">
                    <img src="{{ asset('images/franchise/gadget-bar-logo.png') }}" alt="Gadget Bar" class="brand-logo">
                </div>
                <div class="fr-story-text">
                    <h2 class="fr-section-title">История бренда</h2>
                    <p class="fr-section-subtitle">Сеть магазинов, где техника становится ближе.</p>
                    <p>Gadget Bar начинался как небольшая точка по продаже смартфонов и аксессуаров. Сегодня это сеть магазинов с широким ассортиментом: смартфоны, наушники, гаджеты для дома, игровые консоли и техника для кухни.</p>
                    <p>Мы выстроили прозрачную логистику, обучение персонала, маркетинговую поддержку и IT-инфраструктуру — и делимся этим с партнёрами по франшизе.</p>
                    <div class="fr-stats">
                        <div class="fr-stat">
                            <div class="fr-stat-number">18-70</div>
                            <div class="fr-stat-label">заказов в месяц в активной точке</div>
                        </div>
                        <div class="fr-stat">
                            <div class="fr-stat-number">66-89 <span>тыс. ₽</span></div>
                            <div class="fr-stat-label">прибыль в месяц</div>
                        </div>
                        <div class="fr-stat">
                            <div class="fr-stat-number">1-4 <span>мес.</span></div>
                            <div class="fr-stat-label">окупаемость</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Advantages -->
    <section class="fr-section fr-advantages">
        <div class="fr-container">
            <h2 class="fr-section-title">Наши преимущества</h2>
            <p class="fr-section-subtitle">Работаем по проверенной модели: вы получаете инструменты, знания и поддержку, а не просто вывеску.</p>
            <div class="fr-advantages-grid">
                <div class="fr-advantage">
                    <div class="fr-advantage-icon">🏪</div>
                    <h3>Готовая концепция</h3>
                    <p>Единые стандарты магазина, витрины, мерчандайзинга и сервиса.</p>
                </div>
                <div class="fr-advantage">
                    <div class="fr-advantage-icon">📦</div>
                    <h3>Прямые поставки</h3>
                    <p>Работаем с крупными дистрибьюторами и брендами напрямую.</p>
                </div>
                <div class="fr-advantage">
                    <div class="fr-advantage-icon">🎓</div>
                    <h3>Обучение персонала</h3>
                    <p>Программа онбординга для владельца и продавцов-консультантов.</p>
                </div>
                <div class="fr-advantage">
                    <div class="fr-advantage-icon">📢</div>
                    <h3>Маркетинг и реклама</h3>
                    <p>Рекламная подписка — первые 2 месяца бесплатно.</p>
                </div>
                <div class="fr-advantage">
                    <div class="fr-advantage-icon">💻</div>
                    <h3>IT-инфраструктура</h3>
                    <p>Сайт, бот, CRM и учётная система для работы с каталогом и заказами.</p>
                </div>
                <div class="fr-advantage">
                    <div class="fr-advantage-icon">🤝</div>
                    <h3>Сопровождение</h3>
                    <p>Поддержка франчайзи на запуске и в процессе работы.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Conditions -->
    <section class="fr-section fr-conditions" id="conditions">
        <div class="fr-container">
            <h2 class="fr-section-title">Условия франшизы</h2>
            <p class="fr-section-subtitle">Что нужно для старта и что вы получаете взамен.</p>
            <div class="fr-conditions-grid">
                <div class="fr-condition-card">
                    <h3>Паушальный взнос</h3>
                    <div class="price">100 000 ₽</div>
                    <ul>
                        <li>Право использования бренда</li>
                        <li>Фирменное руководство франшизы</li>
                        <li>Обучение и запуск</li>
                    </ul>
                </div>
                <div class="fr-condition-card">
                    <h3>Роялти</h3>
                    <div class="price">1,6%</div>
                    <ul>
                        <li>От оборота ежемесячно</li>
                        <li>Поддержка и обновления</li>
                        <li>Маркетинговые материалы</li>
                    </ul>
                </div>
                <div class="fr-condition-card">
                    <h3>Рекламная подписка</h3>
                    <div class="price">15 000 ₽/мес</div>
                    <ul>
                        <li>Первые 2 месяца бесплатно</li>
                        <li>Настройка рекламы</li>
                        <li>Поддержка трафика</li>
                    </ul>
                </div>
                <div class="fr-condition-card">
                    <h3>Вложения на старт</h3>
                    <div class="price">от 15 000 ₽</div>
                    <ul>
                        <li>Вывеска: 5–30 тыс ₽</li>
                        <li>Домебелирование: 10–40 тыс ₽</li>
                        <li>Товарный остаток обсуждается отдельно</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Calculator -->
    <section class="fr-section fr-calculator" id="calculator">
        <div class="fr-container">
            <h2 class="fr-section-title">Калькулятор прибыли</h2>
            <p class="fr-section-subtitle">Оцените потенциальную выручку и прибыль вашей точки.</p>
            <div class="fr-calc-wrapper" x-data="franchiseCalc()">
                <div class="fr-calc-card">
                    <div class="fr-field">
                        <label for="orders">Количество заказов в месяц</label>
                        <input type="range" id="orders" x-model="orders" min="0" max="150" step="1">
                        <span class="value" x-text="orders + ' заказов'"></span>
                    </div>
                    <div class="fr-field">
                        <label for="avgCheck">Средний чек</label>
                        <input type="range" id="avgCheck" x-model="avgCheck" min="1000" max="50000" step="500">
                        <span class="value" x-text="formatMoney(avgCheck)"></span>
                    </div>
                    <div class="fr-field">
                        <label for="margin">Наценка, %</label>
                        <input type="range" id="margin" x-model="margin" min="10" max="60" step="1">
                        <span class="value" x-text="margin + '%'"></span>
                    </div>
                    <div class="fr-field">
                        <label for="expenses">Ежемесячные расходы (аренда, зарплата и пр.)</label>
                        <input type="range" id="expenses" x-model="expenses" min="0" max="300000" step="5000">
                        <span class="value" x-text="formatMoney(expenses)"></span>
                    </div>
                </div>
                <div class="fr-calc-result">
                    <h3>Расчёт</h3>
                    <div class="fr-result-row">
                        <span>Выручка</span>
                        <span x-text="formatMoney(revenue)"></span>
                    </div>
                    <div class="fr-result-row">
                        <span>Валовая прибыль</span>
                        <span x-text="formatMoney(grossProfit)"></span>
                    </div>
                    <div class="fr-result-row">
                        <span>Роялти (1,6%)</span>
                        <span x-text="formatMoney(royalty)"></span>
                    </div>
                    <div class="fr-result-row">
                        <span>Рекламная подписка</span>
                        <span x-text="formatMoney(15000)"></span>
                    </div>
                    <div class="fr-result-row">
                        <span>Расходы</span>
                        <span x-text="formatMoney(expenses)"></span>
                    </div>
                    <div class="fr-result-row total">
                        <span>Чистая прибыль</span>
                        <span x-text="formatMoney(netProfit)"></span>
                    </div>
                    <p style="margin-top: 24px; font-size: 14px; color: rgba(255,255,255,0.6);">Расчёт приблизительный и не является публичной офертой.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Forms -->
    <section class="fr-section fr-forms" id="forms">
        <div class="fr-container">
            <h2 class="fr-section-title">Связаться с нами</h2>
            <p class="fr-section-subtitle">Оставьте заявку на франшизу или закажите обратный звонок — мы расскажем подробнее.</p>
            <div class="fr-forms-grid">
                <div class="fr-form-card" x-data="franchiseForm()">
                    <h3>Заявка на франшизу</h3>
                    <p>Расскажем об условиях, сроках и следующих шагах.</p>
                    <template x-if="success">
                        <div class="fr-alert fr-alert-success" x-text="success"></div>
                    </template>
                    <template x-if="error">
                        <div class="fr-alert fr-alert-error" x-text="error"></div>
                    </template>
                    <form @submit.prevent="type = 'franchise'; submit()">
                        <input type="text" x-model="name" class="fr-input" placeholder="Ваше имя" required>
                        <input type="tel" x-model="phone" class="fr-input" placeholder="Телефон" required>
                        <input type="email" x-model="email" class="fr-input" placeholder="Email">
                        <input type="text" x-model="city" class="fr-input" placeholder="Город открытия">
                        <select x-model="budget" class="fr-input">
                            <option value="">Планируемый бюджет</option>
                            <option value="до 300 000 ₽">до 300 000 ₽</option>
                            <option value="300 000 — 500 000 ₽">300 000 — 500 000 ₽</option>
                            <option value="500 000 — 1 000 000 ₽">500 000 — 1 000 000 ₽</option>
                            <option value="более 1 000 000 ₽">более 1 000 000 ₽</option>
                        </select>
                        <textarea x-model="message" class="fr-input fr-textarea" placeholder="Комментарий"></textarea>
                        <button type="submit" class="fr-btn fr-btn-primary" :disabled="loading" x-text="loading ? 'Отправка...' : 'Отправить заявку'"></button>
                    </form>
                </div>
                <div class="fr-form-card" x-data="franchiseForm()">
                    <h3>Обратный звонок</h3>
                    <p>Удобное время звонка — сразу договоримся.</p>
                    <template x-if="success">
                        <div class="fr-alert fr-alert-success" x-text="success"></div>
                    </template>
                    <template x-if="error">
                        <div class="fr-alert fr-alert-error" x-text="error"></div>
                    </template>
                    <form @submit.prevent="type = 'callback'; submit()">
                        <input type="text" x-model="name" class="fr-input" placeholder="Ваше имя" required>
                        <input type="tel" x-model="phone" class="fr-input" placeholder="Телефон" required>
                        <textarea x-model="message" class="fr-input fr-textarea" placeholder="Удобное время звонка"></textarea>
                        <button type="submit" class="fr-btn fr-btn-primary" :disabled="loading" x-text="loading ? 'Отправка...' : 'Заказать звонок'"></button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <footer class="fr-footer">
        <div class="fr-container">
            <p>© {{ date('Y') }} Gadget Bar. Франшиза — <a href="https://fr.gbsale.ru">fr.gbsale.ru</a></p>
        </div>
    </footer>
</div>

@push('scripts')
<script>
function franchiseCalc() {
    return {
        orders: 30,
        avgCheck: 20000,
        margin: 23,
        expenses: 35000,

        get revenue() {
            return this.orders * this.avgCheck;
        },
        get grossProfit() {
            return Math.round(this.revenue * (this.margin / 100));
        },
        get royalty() {
            return Math.round(this.revenue * 0.016);
        },
        get netProfit() {
            return this.grossProfit - this.royalty - 15000 - this.expenses;
        },
        formatMoney(value) {
            return new Intl.NumberFormat('ru-RU', { style: 'currency', currency: 'RUB', maximumFractionDigits: 0 }).format(value);
        }
    }
}
</script>
@endpush
@endsection
