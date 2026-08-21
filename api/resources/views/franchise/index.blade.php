@extends('layouts.franchise')

@section('title', 'Франшиза Gadget Bar — откройте магазин техники')
@section('meta_description', 'Франшиза Gadget Bar: паушальный взнос 100 000 ₽, роялти 1,6%, прибыль до 89 000 ₽/мес. Оставьте заявку на франшизу или закажите обратный звонок.')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
:root {
    --fr-accent: #0cc0df;
    --fr-accent-light: #5ce7ff;
    --fr-accent-soft: rgba(12, 192, 223, 0.08);
    --fr-black: #0a0a0a;
    --fr-gray: #555555;
    --fr-light: #f8f9fb;
    --fr-white: #ffffff;
    --fr-border: #e8eaed;
    --fr-radius: 28px;
    --fr-radius-sm: 20px;
    --fr-shadow: 0 4px 24px rgba(0,0,0,0.04);
    --fr-shadow-hover: 0 20px 50px rgba(0,0,0,0.08);
    --fr-transition: cubic-bezier(0.22, 1, 0.36, 1);
}

.franchise-page {
    font-family: 'Inter', 'Segoe UI', system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
    color: var(--fr-black);
    line-height: 1.6;
    overflow-x: hidden;
    background: var(--fr-white);
}

.franchise-page * {
    box-sizing: border-box;
}

.fr-container {
    width: 100%;
    max-width: 1180px;
    margin: 0 auto;
    padding: 0 24px;
}

/* Scroll reveal */
.reveal {
    opacity: 0;
    transform: translateY(28px);
    transition: opacity 0.7s var(--fr-transition), transform 0.7s var(--fr-transition);
}

.reveal.visible {
    opacity: 1;
    transform: translateY(0);
}

.reveal-delay-1 { transition-delay: 0.08s; }
.reveal-delay-2 { transition-delay: 0.16s; }
.reveal-delay-3 { transition-delay: 0.24s; }
.reveal-delay-4 { transition-delay: 0.32s; }

/* No-JS / print fallback */
@media (scripting: none), print {
    .reveal {
        opacity: 1 !important;
        transform: none !important;
    }
}

/* Header */
.fr-header {
    position: sticky;
    top: 0;
    z-index: 100;
    background: rgba(255,255,255,0.82);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border-bottom: 1px solid rgba(0,0,0,0.04);
}

.fr-header-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: 72px;
}

.fr-header-logo {
    height: 28px;
    width: auto;
}

@media (min-width: 768px) {
    .fr-header-logo {
        height: 32px;
    }
}

.fr-header-nav {
    display: none;
    align-items: center;
    gap: 32px;
}

.fr-header-nav a {
    font-size: 15px;
    font-weight: 500;
    color: var(--fr-black);
    text-decoration: none;
    transition: color 0.2s;
}

.fr-header-nav a:hover {
    color: var(--fr-accent);
}

.fr-header-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 10px 16px;
    border-radius: 999px;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
    background: var(--fr-black);
    color: var(--fr-white);
    transition: transform 0.2s, background 0.2s;
    white-space: nowrap;
}

@media (min-width: 768px) {
    .fr-header-btn {
        padding: 12px 22px;
        font-size: 14px;
    }
}

.fr-header-btn:hover {
    transform: translateY(-2px);
    background: #222;
}

@media (min-width: 768px) {
    .fr-header-nav {
        display: flex;
    }
}

/* Buttons */
.fr-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 18px 32px;
    border-radius: 999px;
    font-size: 16px;
    font-weight: 700;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: transform 0.25s var(--fr-transition), box-shadow 0.25s var(--fr-transition), background 0.25s;
}

.fr-btn:hover {
    transform: translateY(-3px);
}

.fr-btn-primary {
    background: var(--fr-accent);
    color: var(--fr-white);
    box-shadow: 0 12px 32px rgba(12, 192, 223, 0.28);
}

.fr-btn-primary:hover {
    background: #09adc8;
    box-shadow: 0 16px 40px rgba(12, 192, 223, 0.38);
}

.fr-btn-outline {
    background: var(--fr-white);
    color: var(--fr-black);
    border: 1.5px solid var(--fr-border);
    box-shadow: var(--fr-shadow);
}

.fr-btn-outline:hover {
    border-color: var(--fr-accent);
    color: var(--fr-accent);
}

.fr-btn-dark {
    background: var(--fr-black);
    color: var(--fr-white);
    box-shadow: 0 12px 32px rgba(0,0,0,0.12);
}

.fr-btn-dark:hover {
    background: #222;
    box-shadow: 0 16px 40px rgba(0,0,0,0.18);
}

/* Hero */
.fr-hero {
    position: relative;
    display: flex;
    align-items: center;
    padding: 60px 0 80px;
    overflow: hidden;
    background: linear-gradient(180deg, #ffffff 0%, #f8fbfc 50%, #ffffff 100%);
}

.fr-hero-bg {
    position: absolute;
    inset: 0;
    overflow: hidden;
    pointer-events: none;
}

.fr-blob {
    position: absolute;
    border-radius: 50%;
    filter: blur(80px);
    opacity: 0.55;
    animation: float 10s ease-in-out infinite;
}

.fr-blob-1 {
    width: 520px;
    height: 520px;
    background: rgba(12, 192, 223, 0.18);
    top: -120px;
    right: -80px;
    animation-delay: 0s;
}

.fr-blob-2 {
    width: 360px;
    height: 360px;
    background: rgba(92, 231, 255, 0.14);
    bottom: 10%;
    right: 15%;
    animation-delay: -3s;
}

.fr-blob-3 {
    width: 260px;
    height: 260px;
    background: rgba(12, 192, 223, 0.1);
    top: 30%;
    left: -60px;
    animation-delay: -6s;
}

@keyframes float {
    0%, 100% { transform: translate(0, 0) scale(1); }
    33% { transform: translate(20px, -30px) scale(1.05); }
    66% { transform: translate(-15px, 20px) scale(0.97); }
}

.fr-hero-grid {
    position: relative;
    z-index: 2;
    display: grid;
    grid-template-columns: 1fr;
    gap: 48px;
    align-items: center;
}

@media (min-width: 900px) {
    .fr-hero-grid {
        grid-template-columns: 1.05fr 0.95fr;
        gap: 64px;
    }
}

.fr-hero-content {
    max-width: 620px;
}

.fr-hero-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 28px;
}

.fr-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: var(--fr-white);
    border: 1px solid var(--fr-border);
    border-radius: 999px;
    padding: 10px 18px;
    font-size: 14px;
    font-weight: 600;
    color: var(--fr-black);
    box-shadow: var(--fr-shadow);
}

.fr-badge span {
    color: var(--fr-accent);
    font-weight: 800;
}

.fr-hero-title {
    margin: 0 0 20px;
}

.fr-hero-title .pre {
    display: block;
    font-size: clamp(22px, 3vw, 32px);
    font-weight: 600;
    color: var(--fr-gray);
    margin-bottom: 6px;
    letter-spacing: -0.02em;
}

.fr-hero-logo-img {
    display: block;
    width: 100%;
    max-width: 520px;
    height: auto;
}

.fr-hero p {
    font-size: clamp(17px, 2vw, 20px);
    color: var(--fr-gray);
    margin: 0 0 36px;
    max-width: 520px;
    line-height: 1.6;
}

.fr-hero-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 14px;
}

.fr-hero-visual {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
}

.fr-hero-card {
    position: relative;
    background: var(--fr-white);
    border-radius: var(--fr-radius);
    padding: 36px;
    box-shadow: 0 24px 70px rgba(12, 192, 223, 0.12), 0 8px 30px rgba(0,0,0,0.04);
    max-width: 380px;
    width: 100%;
    animation: heroCardFloat 6s ease-in-out infinite;
}

@keyframes heroCardFloat {
    0%, 100% { transform: translateY(0) rotate(0deg); }
    50% { transform: translateY(-12px) rotate(1deg); }
}

.fr-hero-card::before {
    content: '';
    position: absolute;
    inset: 12px;
    border: 1px dashed rgba(12, 192, 223, 0.3);
    border-radius: 20px;
    pointer-events: none;
}

.fr-hero-card-icon {
    width: 64px;
    height: 64px;
    border-radius: 18px;
    background: linear-gradient(135deg, var(--fr-accent), var(--fr-accent-light));
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    margin-bottom: 20px;
    box-shadow: 0 10px 24px rgba(12, 192, 223, 0.25);
}

.fr-hero-card h3 {
    font-size: 22px;
    font-weight: 800;
    margin: 0 0 10px;
}

.fr-hero-card p {
    font-size: 15px;
    color: var(--fr-gray);
    margin: 0 0 20px;
}

.fr-hero-card-row {
    display: flex;
    justify-content: space-between;
    padding: 12px 0;
    border-bottom: 1px solid var(--fr-border);
    font-size: 14px;
}

.fr-hero-card-row:last-child {
    border-bottom: none;
}

.fr-hero-card-row span:last-child {
    font-weight: 800;
    color: var(--fr-accent);
}

/* Section base */
.fr-section {
    padding: 80px 0;
}

.fr-section-header {
    max-width: 700px;
    margin-bottom: 56px;
}

.fr-section-label {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--fr-accent);
    margin-bottom: 14px;
}

.fr-section-label::before {
    content: '';
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--fr-accent);
}

.fr-section-title {
    font-size: clamp(28px, 4vw, 42px);
    font-weight: 900;
    margin: 0 0 16px;
    line-height: 1.15;
    letter-spacing: -0.02em;
}

.fr-section-subtitle {
    font-size: 17px;
    color: var(--fr-gray);
    margin: 0;
    line-height: 1.6;
}

/* Brand story */
.fr-story {
    background: var(--fr-light);
}

.fr-story-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 48px;
    align-items: center;
}

@media (min-width: 900px) {
    .fr-story-grid {
        grid-template-columns: 1fr 1.1fr;
        gap: 72px;
    }
}

.fr-story-image {
    position: relative;
    border-radius: var(--fr-radius);
    overflow: hidden;
    background: var(--fr-white);
    min-height: 440px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: var(--fr-shadow-hover);
}

.fr-story-image::before {
    content: '';
    position: absolute;
    inset: 0;
    background: radial-gradient(circle at 30% 30%, rgba(12, 192, 223, 0.08), transparent 60%);
}

.fr-story-image .brand-logo {
    width: auto;
    height: auto;
    max-width: 80%;
    max-height: 140px;
    object-fit: contain;
    position: relative;
    z-index: 2;
}

.fr-story-text p {
    font-size: 17px;
    color: var(--fr-gray);
    margin: 0 0 18px;
    line-height: 1.7;
}

.fr-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    margin-top: 32px;
}

.fr-stat {
    background: var(--fr-white);
    border-radius: var(--fr-radius-sm);
    padding: 24px;
    box-shadow: var(--fr-shadow);
    transition: transform 0.25s, box-shadow 0.25s;
}

.fr-stat:hover {
    transform: translateY(-4px);
    box-shadow: var(--fr-shadow-hover);
}

.fr-stat-number {
    font-size: 28px;
    font-weight: 900;
    color: var(--fr-black);
    white-space: nowrap;
    letter-spacing: -0.02em;
}

.fr-stat-number span {
    color: var(--fr-accent);
    white-space: nowrap;
}

.fr-stat-label {
    font-size: 13px;
    color: var(--fr-gray);
    margin-top: 6px;
    line-height: 1.4;
}

@media (max-width: 600px) {
    .fr-stats {
        grid-template-columns: 1fr;
    }
}

/* Advantages */
.fr-advantages {
    background: var(--fr-white);
}

.fr-advantages-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 24px;
}

.fr-advantage {
    background: var(--fr-white);
    border: 1px solid var(--fr-border);
    border-radius: var(--fr-radius-sm);
    padding: 32px;
    transition: transform 0.3s var(--fr-transition), box-shadow 0.3s var(--fr-transition), border-color 0.3s;
}

.fr-advantage:hover {
    transform: translateY(-8px);
    box-shadow: var(--fr-shadow-hover);
    border-color: rgba(12, 192, 223, 0.25);
}

.fr-advantage-icon {
    width: 56px;
    height: 56px;
    border-radius: 16px;
    background: var(--fr-accent-soft);
    color: var(--fr-accent);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 26px;
    margin-bottom: 20px;
    transition: transform 0.3s, background 0.3s;
}

.fr-advantage:hover .fr-advantage-icon {
    transform: scale(1.08) rotate(-4deg);
    background: var(--fr-accent);
    color: var(--fr-white);
}

.fr-advantage h3 {
    font-size: 19px;
    font-weight: 800;
    margin: 0 0 10px;
}

.fr-advantage p {
    font-size: 15px;
    color: var(--fr-gray);
    margin: 0;
    line-height: 1.6;
}

/* Franchise formats */
.fr-formats {
    background: linear-gradient(180deg, var(--fr-white) 0%, var(--fr-light) 100%);
}

.fr-formats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
}

@media (max-width: 1100px) {
    .fr-formats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 600px) {
    .fr-formats-grid {
        grid-template-columns: 1fr;
    }
}

.fr-format-card {
    background: var(--fr-white);
    border-radius: var(--fr-radius-sm);
    padding: 28px;
    border: 1px solid var(--fr-border);
    box-shadow: var(--fr-shadow);
    transition: transform 0.3s var(--fr-transition), box-shadow 0.3s var(--fr-transition);
    display: flex;
    flex-direction: column;
    position: relative;
    overflow: hidden;
}

.fr-format-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--fr-accent), var(--fr-accent-light));
    transform: scaleX(0);
    transform-origin: left;
    transition: transform 0.4s var(--fr-transition);
}

.fr-format-card:hover {
    transform: translateY(-8px);
    box-shadow: var(--fr-shadow-hover);
}

.fr-format-card:hover::before {
    transform: scaleX(1);
}

.fr-format-badge {
    display: inline-flex;
    align-self: flex-start;
    background: var(--fr-accent-soft);
    color: var(--fr-accent);
    font-size: 12px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    padding: 6px 12px;
    border-radius: 999px;
    margin-bottom: 16px;
}

.fr-format-card h3 {
    font-size: 21px;
    font-weight: 900;
    margin: 0 0 14px;
    letter-spacing: -0.02em;
}

.fr-format-list {
    list-style: none;
    margin: 0 0 20px;
    padding: 0;
    flex: 1;
}

.fr-format-list li {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid var(--fr-border);
    font-size: 14px;
}

.fr-format-list li:last-child {
    border-bottom: none;
}

.fr-format-list li span:first-child {
    color: var(--fr-gray);
}

.fr-format-list li span:last-child {
    font-weight: 700;
    color: var(--fr-black);
    text-align: right;
    max-width: 55%;
}

.fr-format-profit {
    background: var(--fr-light);
    border-radius: 14px;
    padding: 16px;
    text-align: center;
    margin-top: auto;
}

.fr-format-profit-value {
    font-size: 24px;
    font-weight: 900;
    color: var(--fr-accent);
    letter-spacing: -0.02em;
}

.fr-format-profit-label {
    font-size: 13px;
    color: var(--fr-gray);
    margin-top: 4px;
}

/* Conditions */
.fr-conditions {
    background: var(--fr-white);
}

.fr-conditions-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
}

@media (max-width: 1000px) {
    .fr-conditions-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 600px) {
    .fr-conditions-grid {
        grid-template-columns: 1fr;
    }
}

.fr-condition-card {
    background: var(--fr-white);
    border: 1px solid var(--fr-border);
    border-radius: var(--fr-radius-sm);
    padding: 28px;
    box-shadow: var(--fr-shadow);
    transition: transform 0.25s, box-shadow 0.25s;
}

.fr-condition-card:hover {
    transform: translateY(-6px);
    box-shadow: var(--fr-shadow-hover);
}

.fr-condition-card h3 {
    font-size: 17px;
    font-weight: 700;
    margin: 0 0 12px;
    color: var(--fr-gray);
}

.fr-condition-card .price {
    font-size: 36px;
    font-weight: 900;
    margin: 0 0 16px;
    color: var(--fr-black);
    letter-spacing: -0.02em;
}

.fr-condition-card .price span {
    color: var(--fr-accent);
}

.fr-condition-card ul {
    margin: 0;
    padding-left: 18px;
    color: var(--fr-gray);
    font-size: 15px;
}

.fr-condition-card li {
    margin-bottom: 8px;
    line-height: 1.5;
}

/* Calculator */
.fr-calculator {
    background: var(--fr-light);
}

.fr-calc-wrapper {
    display: grid;
    grid-template-columns: 1fr;
    gap: 32px;
    align-items: start;
}

@media (min-width: 900px) {
    .fr-calc-wrapper {
        grid-template-columns: 1fr 1fr;
        gap: 40px;
    }
}

.fr-calc-card {
    background: var(--fr-white);
    border-radius: var(--fr-radius);
    padding: 40px;
    box-shadow: var(--fr-shadow-hover);
}

.fr-field {
    margin-bottom: 28px;
}

.fr-field:last-child {
    margin-bottom: 0;
}

.fr-field label {
    display: flex;
    justify-content: space-between;
    font-weight: 700;
    margin-bottom: 12px;
    font-size: 15px;
}

.fr-field input[type="range"] {
    width: 100%;
    height: 6px;
    border-radius: 3px;
    background: var(--fr-border);
    outline: none;
    -webkit-appearance: none;
    appearance: none;
}

.fr-field input[type="range"]::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    background: var(--fr-accent);
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(12, 192, 223, 0.35);
    transition: transform 0.15s;
}

.fr-field input[type="range"]::-webkit-slider-thumb:hover {
    transform: scale(1.15);
}

.fr-field input[type="range"]::-moz-range-thumb {
    width: 22px;
    height: 22px;
    border-radius: 50%;
    background: var(--fr-accent);
    cursor: pointer;
    border: none;
    box-shadow: 0 4px 12px rgba(12, 192, 223, 0.35);
}

.fr-field .value {
    display: inline-block;
    font-weight: 800;
    color: var(--fr-accent);
}

.fr-calc-result {
    background: var(--fr-black);
    color: var(--fr-white);
    border-radius: var(--fr-radius);
    padding: 40px;
    position: sticky;
    top: 100px;
    box-shadow: 0 24px 60px rgba(0,0,0,0.14);
}

.fr-calc-result h3 {
    font-size: 20px;
    font-weight: 800;
    margin: 0 0 28px;
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
    font-size: 24px;
    font-weight: 900;
}

.fr-result-row.total span:last-child {
    color: var(--fr-accent);
}

.fr-calc-note {
    margin-top: 24px;
    font-size: 13px;
    color: rgba(255,255,255,0.5);
    line-height: 1.5;
}

/* Forms */
.fr-forms {
    background: var(--fr-white);
}

.fr-forms-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 32px;
}

@media (min-width: 900px) {
    .fr-forms-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

.fr-form-card {
    background: var(--fr-white);
    border: 1px solid var(--fr-border);
    border-radius: var(--fr-radius);
    padding: 40px;
    box-shadow: var(--fr-shadow-hover);
}

.fr-form-card h3 {
    font-size: 24px;
    font-weight: 900;
    margin: 0 0 8px;
    letter-spacing: -0.02em;
}

.fr-form-card > p {
    color: var(--fr-gray);
    margin: 0 0 28px;
    font-size: 15px;
}

.fr-input {
    width: 100%;
    padding: 16px 20px;
    border: 1px solid var(--fr-border);
    border-radius: 14px;
    font-size: 15px;
    margin-bottom: 14px;
    background: var(--fr-white);
    transition: border-color 0.2s, box-shadow 0.2s;
    font-family: inherit;
}

.fr-input:focus {
    outline: none;
    border-color: var(--fr-accent);
    box-shadow: 0 0 0 4px rgba(12, 192, 223, 0.12);
}

.fr-input::placeholder {
    color: #9ca3af;
}

.fr-textarea {
    min-height: 100px;
    resize: vertical;
}

.fr-alert {
    padding: 14px 18px;
    border-radius: 12px;
    margin-bottom: 18px;
    font-size: 14px;
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
    background: var(--fr-light);
    color: var(--fr-gray);
    padding: 40px 0;
    text-align: center;
    font-size: 14px;
    border-top: 1px solid var(--fr-border);
}

.fr-footer a {
    color: var(--fr-black);
    text-decoration: none;
    font-weight: 700;
}

/* Hero text animation */
@keyframes fadeUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-up {
    animation: fadeUp 0.7s var(--fr-transition) forwards;
    opacity: 0;
}

.delay-1 { animation-delay: 0.1s; }
.delay-2 { animation-delay: 0.2s; }
.delay-3 { animation-delay: 0.3s; }
.delay-4 { animation-delay: 0.4s; }
.delay-5 { animation-delay: 0.5s; }

/* Responsive */
@media (max-width: 900px) {
    .fr-section {
        padding: 56px 0;
    }

    .fr-hero {
        padding: 40px 0 56px;
    }

    .fr-calc-result {
        position: static;
    }
}

@media (max-width: 600px) {
    .fr-container {
        padding: 0 18px;
    }

    .fr-btn {
        width: 100%;
    }

    .fr-hero-title .pre {
        font-size: 20px;
        margin-bottom: 4px;
    }

    .fr-hero-logo-img {
        max-width: 100%;
    }

    .fr-hero-card {
        padding: 24px;
    }

    .fr-hero-card h3 {
        font-size: 20px;
    }
}
</style>
@endpush

@section('content')
<div class="franchise-page">
    <!-- Header -->
    <header class="fr-header">
        <div class="fr-container fr-header-inner">
            <img src="/images/franchise/gadget-bar-logo.png" alt="Gadget Bar" class="fr-header-logo">
            <nav class="fr-header-nav">
                <a href="#about">О бренде</a>
                <a href="#advantages">Преимущества</a>
                <a href="#formats">Форматы</a>
                <a href="#conditions">Условия</a>
                <a href="#calculator">Калькулятор</a>
            </nav>
            <a href="#forms" class="fr-header-btn">Оставить заявку</a>
        </div>
    </header>

    <!-- Hero -->
    <section class="fr-hero">
        <div class="fr-hero-bg">
            <div class="fr-blob fr-blob-1"></div>
            <div class="fr-blob fr-blob-2"></div>
            <div class="fr-blob fr-blob-3"></div>
        </div>
        <div class="fr-container">
            <div class="fr-hero-grid">
                <div class="fr-hero-content">
                    <div class="fr-hero-badges animate-fade-up delay-1">
                        <div class="fr-badge">Прибыль <span>66–89 тыс. ₽/мес</span></div>
                        <div class="fr-badge">Окупаемость <span>1–4 месяца</span></div>
                    </div>
                    <div class="fr-hero-title animate-fade-up delay-2">
                        <span class="pre">Откройте свой</span>
                        <img src="/images/franchise/gadget-bar-logo.png" alt="Gadget Bar" class="fr-hero-logo-img">
                    </div>
                    <p class="animate-fade-up delay-3">Франшиза магазина техники и аксессуаров с готовой моделью, обучением и поддержкой на всех этапах запуска.</p>
                    <div class="fr-hero-buttons animate-fade-up delay-4">
                        <a href="#calculator" class="fr-btn fr-btn-primary">Рассчитать прибыль</a>
                        <a href="#forms" class="fr-btn fr-btn-outline">Оставить заявку</a>
                    </div>
                </div>
                <div class="fr-hero-visual animate-fade-up delay-5">
                    <div class="fr-hero-card">
                        <div class="fr-hero-card-icon">🚀</div>
                        <h3>Быстрый старт</h3>
                        <p>Уже через 30–45 дней ваша точка начнёт работать и приносить первую прибыль.</p>
                        <div class="fr-hero-card-row">
                            <span>Паушальный взнос</span>
                            <span>от 100 000 ₽</span>
                        </div>
                        <div class="fr-hero-card-row">
                            <span>Роялти</span>
                            <span>1,6%</span>
                        </div>
                        <div class="fr-hero-card-row">
                            <span>Поддержка</span>
                            <span>24/7</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Brand story -->
    <section class="fr-section fr-story" id="about">
        <div class="fr-container">
            <div class="fr-story-grid">
                <div class="fr-story-image reveal">
                    <img src="/images/franchise/gadget-bar-logo.png" alt="Gadget Bar" class="brand-logo">
                </div>
                <div class="fr-story-text">
                    <div class="fr-section-header reveal">
                        <div class="fr-section-label">История бренда</div>
                        <h2 class="fr-section-title">Сеть магазинов, где техника становится ближе</h2>
                    </div>
                    <p class="reveal reveal-delay-1">Gadget Bar начинался как небольшая точка по продаже смартфонов и аксессуаров. Сегодня это сеть магазинов с широким ассортиментом: смартфоны, наушники, гаджеты для дома, игровые консоли и техника для кухни.</p>
                    <p class="reveal reveal-delay-2">Мы выстроили прозрачную логистику, обучение персонала, маркетинговую поддержку и IT-инфраструктуру — и делимся этим с партнёрами по франшизе.</p>
                    <div class="fr-stats reveal reveal-delay-3">
                        <div class="fr-stat">
                            <div class="fr-stat-number">30+</div>
                            <div class="fr-stat-label">заказов в день в активной точке</div>
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
    <section class="fr-section fr-advantages" id="advantages">
        <div class="fr-container">
            <div class="fr-section-header reveal">
                <div class="fr-section-label">Наши преимущества</div>
                <h2 class="fr-section-title">Работаем по проверенной модели</h2>
                <p class="fr-section-subtitle">Вы получаете инструменты, знания и поддержку, а не просто вывеску.</p>
            </div>
            <div class="fr-advantages-grid">
                <div class="fr-advantage reveal reveal-delay-1">
                    <div class="fr-advantage-icon">🏪</div>
                    <h3>Готовая концепция</h3>
                    <p>Единые стандарты магазина, витрины, мерчандайзинга и сервиса.</p>
                </div>
                <div class="fr-advantage reveal reveal-delay-2">
                    <div class="fr-advantage-icon">📦</div>
                    <h3>Прямые поставки</h3>
                    <p>Работаем с крупными дистрибьюторами и брендами напрямую.</p>
                </div>
                <div class="fr-advantage reveal reveal-delay-3">
                    <div class="fr-advantage-icon">🎓</div>
                    <h3>Обучение персонала</h3>
                    <p>Программа онбординга для владельца и продавцов-консультантов.</p>
                </div>
                <div class="fr-advantage reveal reveal-delay-4">
                    <div class="fr-advantage-icon">📢</div>
                    <h3>Маркетинг и реклама</h3>
                    <p>Рекламная подписка — первые 2 месяца бесплатно.</p>
                </div>
                <div class="fr-advantage reveal reveal-delay-1">
                    <div class="fr-advantage-icon">💻</div>
                    <h3>IT-инфраструктура</h3>
                    <p>Сайт, бот, CRM и учётная система для работы с каталогом и заказами.</p>
                </div>
                <div class="fr-advantage reveal reveal-delay-2">
                    <div class="fr-advantage-icon">🤝</div>
                    <h3>Сопровождение</h3>
                    <p>Поддержка франчайзи на запуске и в процессе работы.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Franchise formats -->
    <section class="fr-section fr-formats" id="formats">
        <div class="fr-container">
            <div class="fr-section-header reveal">
                <div class="fr-section-label">Форматы франшизы</div>
                <h2 class="fr-section-title">Выберите формат под ваш бюджет</h2>
                <p class="fr-section-subtitle">Четыре варианта запуска: от компактного островка до полноценного магазина. Цифры уточняются на встрече.</p>
            </div>
            <div class="fr-formats-grid">
                <div class="fr-format-card reveal reveal-delay-1">
                    <div class="fr-format-badge">Старт</div>
                    <h3>ПВЗ / островок до 6 м²</h3>
                    <ul class="fr-format-list">
                        <li><span>Площадь</span><span>до 6 м²</span></li>
                        <li><span>Локация</span><span>островок в ТЦ, ПВЗ в сервисе</span></li>
                        <li><span>Срок запуска</span><span>от 30 дней</span></li>
                        <li><span>Паушальный взнос</span><span>100 000 ₽</span></li>
                        <li><span>Роялти</span><span>1,6%</span></li>
                        <li><span>Окупаемость</span><span>1–4 мес.</span></li>
                    </ul>
                    <div class="fr-format-profit">
                        <div class="fr-format-profit-value">66–89 тыс. ₽</div>
                        <div class="fr-format-profit-label">прибыль в месяц</div>
                    </div>
                </div>

                <div class="fr-format-card reveal reveal-delay-2">
                    <div class="fr-format-badge">Стандарт</div>
                    <h3>Островок в ТЦ</h3>
                    <ul class="fr-format-list">
                        <li><span>Площадь</span><span>8–15 м²</span></li>
                        <li><span>Локация</span><span>островок в торговом центре</span></li>
                        <li><span>Срок запуска</span><span>от 45 дней</span></li>
                        <li><span>Паушальный взнос</span><span>от 150 000 ₽</span></li>
                        <li><span>Роялти</span><span>1,6%</span></li>
                        <li><span>Окупаемость</span><span>2–6 мес.</span></li>
                    </ul>
                    <div class="fr-format-profit">
                        <div class="fr-format-profit-value">100–150 тыс. ₽</div>
                        <div class="fr-format-profit-label">прибыль в месяц*</div>
                    </div>
                </div>

                <div class="fr-format-card reveal reveal-delay-3">
                    <div class="fr-format-badge">Полноценный</div>
                    <h3>Магазин в ТЦ</h3>
                    <ul class="fr-format-list">
                        <li><span>Площадь</span><span>20–40 м²</span></li>
                        <li><span>Локация</span><span>павильон / помещение в ТЦ</span></li>
                        <li><span>Срок запуска</span><span>от 60 дней</span></li>
                        <li><span>Паушальный взнос</span><span>от 250 000 ₽</span></li>
                        <li><span>Роялти</span><span>1,6%</span></li>
                        <li><span>Окупаемость</span><span>4–8 мес.</span></li>
                    </ul>
                    <div class="fr-format-profit">
                        <div class="fr-format-profit-value">150–250 тыс. ₽</div>
                        <div class="fr-format-profit-label">прибыль в месяц*</div>
                    </div>
                </div>

                <div class="fr-format-card reveal reveal-delay-4">
                    <div class="fr-format-badge">Флагман</div>
                    <h3>Флагманский магазин</h3>
                    <ul class="fr-format-list">
                        <li><span>Площадь</span><span>от 50 м²</span></li>
                        <li><span>Локация</span><span>отдельный магазин / стрит-ритейл</span></li>
                        <li><span>Срок запуска</span><span>от 90 дней</span></li>
                        <li><span>Паушальный взнос</span><span>от 400 000 ₽</span></li>
                        <li><span>Роялти</span><span>1,6%</span></li>
                        <li><span>Окупаемость</span><span>6–12 мес.</span></li>
                    </ul>
                    <div class="fr-format-profit">
                        <div class="fr-format-profit-value">300+ тыс. ₽</div>
                        <div class="fr-format-profit-label">прибыль в месяц*</div>
                    </div>
                </div>
            </div>
            <p class="reveal" style="margin-top: 24px; font-size: 13px; color: var(--fr-gray);">* Расчётные значения. Точные цифры зависят от города, локации и сезона.</p>
        </div>
    </section>

    <!-- Conditions -->
    <section class="fr-section fr-conditions" id="conditions">
        <div class="fr-container">
            <div class="fr-section-header reveal">
                <div class="fr-section-label">Условия</div>
                <h2 class="fr-section-title">Что нужно для старта</h2>
                <p class="fr-section-subtitle">Прозрачные условия без скрытых платежей.</p>
            </div>
            <div class="fr-conditions-grid">
                <div class="fr-condition-card reveal reveal-delay-1">
                    <h3>Паушальный взнос</h3>
                    <div class="price">100 000 ₽</div>
                    <ul>
                        <li>Право использования бренда</li>
                        <li>Фирменное руководство франшизы</li>
                        <li>Обучение и запуск</li>
                    </ul>
                </div>
                <div class="fr-condition-card reveal reveal-delay-2">
                    <h3>Роялти</h3>
                    <div class="price">1,6%</div>
                    <ul>
                        <li>От оборота ежемесячно</li>
                        <li>Поддержка и обновления</li>
                        <li>Маркетинговые материалы</li>
                    </ul>
                </div>
                <div class="fr-condition-card reveal reveal-delay-3">
                    <h3>Рекламная подписка</h3>
                    <div class="price">15 000 ₽/мес</div>
                    <ul>
                        <li>Первые 2 месяца бесплатно</li>
                        <li>Настройка рекламы</li>
                        <li>Поддержка трафика</li>
                    </ul>
                </div>
                <div class="fr-condition-card reveal reveal-delay-4">
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
            <div class="fr-section-header reveal">
                <div class="fr-section-label">Калькулятор</div>
                <h2 class="fr-section-title">Рассчитайте прибыль</h2>
                <p class="fr-section-subtitle">Оцените потенциальную выручку и прибыль вашей точки.</p>
            </div>
            <div class="fr-calc-wrapper" x-data="franchiseCalc()">
                <div class="fr-calc-card reveal">
                    <div class="fr-field">
                        <label for="orders">Количество заказов в месяц <span class="value" x-text="orders + ' заказов'"></span></label>
                        <input type="range" id="orders" x-model="orders" min="0" max="150" step="1">
                    </div>
                    <div class="fr-field">
                        <label for="avgCheck">Средний чек <span class="value" x-text="formatMoney(avgCheck)"></span></label>
                        <input type="range" id="avgCheck" x-model="avgCheck" min="1000" max="50000" step="500">
                    </div>
                    <div class="fr-field">
                        <label for="margin">Наценка, % <span class="value" x-text="margin + '%'"></span></label>
                        <input type="range" id="margin" x-model="margin" min="10" max="60" step="1">
                    </div>
                    <div class="fr-field">
                        <label for="expenses">Ежемесячные расходы <span class="value" x-text="formatMoney(expenses)"></span></label>
                        <input type="range" id="expenses" x-model="expenses" min="0" max="300000" step="5000">
                    </div>
                </div>
                <div class="fr-calc-result reveal reveal-delay-2">
                    <h3>Ваш расчёт</h3>
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
                    <p class="fr-calc-note">Расчёт приблизительный и не является публичной офертой.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Forms -->
    <section class="fr-section fr-forms" id="forms">
        <div class="fr-container">
            <div class="fr-section-header reveal">
                <div class="fr-section-label">Связаться</div>
                <h2 class="fr-section-title">Оставьте заявку</h2>
                <p class="fr-section-subtitle">Расскажем об условиях, сроках и следующих шагах. Или закажите обратный звонок.</p>
            </div>
            <div class="fr-forms-grid">
                <div class="fr-form-card reveal reveal-delay-1" x-data="franchiseForm()">
                    <h3>Заявка на франшизу</h3>
                    <p>Получите подробную презентацию и расчёт для вашего города.</p>
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
                        <button type="submit" class="fr-btn fr-btn-primary" :disabled="loading" x-text="loading ? 'Отправка...' : 'Отправить заявку'" style="width: 100%;"></button>
                    </form>
                </div>
                <div class="fr-form-card reveal reveal-delay-2" x-data="franchiseForm()">
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
                        <button type="submit" class="fr-btn fr-btn-dark" :disabled="loading" x-text="loading ? 'Отправка...' : 'Заказать звонок'" style="width: 100%;"></button>
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

// Scroll reveal
document.addEventListener('DOMContentLoaded', function() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.12,
        rootMargin: '0px 0px -40px 0px'
    });

    document.querySelectorAll('.reveal').forEach(el => observer.observe(el));

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href === '#') return;
            const target = document.querySelector(href);
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });
});
</script>
@endpush
@endsection
