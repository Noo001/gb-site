@extends('layouts.franchise')

@section('title', 'Франшиза Gadget Bar — откройте магазин техники')
@section('meta_description', 'Франшиза Gadget Bar: паушальный взнос 100 000 ₽, роялти 1,6%, прибыль до 89 000 ₽/мес. Оставьте заявку на франшизу или закажите обратный звонок.')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root {
    --gb-black: #0D0E10;
    --gb-dark-1: #121315;
    --gb-dark-2: #161719;
    --gb-dark-3: #1A1B1E;
    --gb-white: #FFFFFF;
    --gb-light: #F7F8F9;
    --gb-light-2: #FAFAFA;
    --gb-cyan: #52DDF8;
    --gb-cyan-hover: #64E4FC;
    --gb-cyan-soft: rgba(82, 221, 248, 0.18);
    --gb-text: #111214;
    --gb-text-secondary: #71747C;
    --gb-dark-text: #FFFFFF;
    --gb-dark-text-secondary: #A1A4AB;
    --gb-border: rgba(0, 0, 0, 0.08);
    --gb-border-dark: rgba(255, 255, 255, 0.11);
    --gb-radius-sm: 10px;
    --gb-radius-md: 14px;
    --gb-radius-lg: 18px;
    --gb-shadow: 0 12px 40px rgba(0, 0, 0, 0.05);
    --gb-shadow-lg: 0 20px 55px rgba(0, 0, 0, 0.08);
    --gb-container: 1240px;
}

.franchise-page {
    font-family: 'Inter', 'Segoe UI', system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
    color: var(--gb-text);
    line-height: 1.6;
    overflow-x: hidden;
    background: var(--gb-white);
    -webkit-font-smoothing: antialiased;
}

.franchise-page * {
    box-sizing: border-box;
}

.fr-container {
    width: 100%;
    max-width: var(--gb-container);
    margin: 0 auto;
    padding: 0 32px;
}

@media (max-width: 768px) {
    .fr-container {
        padding: 0 20px;
    }
}

/* Scroll reveal */
.reveal {
    opacity: 0;
    transform: translateY(16px);
    transition: opacity 0.6s cubic-bezier(0.22, 1, 0.36, 1), transform 0.6s cubic-bezier(0.22, 1, 0.36, 1);
}

.reveal.visible {
    opacity: 1;
    transform: translateY(0);
}

.reveal-delay-1 { transition-delay: 0.06s; }
.reveal-delay-2 { transition-delay: 0.12s; }
.reveal-delay-3 { transition-delay: 0.18s; }
.reveal-delay-4 { transition-delay: 0.24s; }

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
    background: rgba(255, 255, 255, 0.92);
    border-bottom: 1px solid var(--gb-border);
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
    gap: 28px;
}

.fr-header-nav a {
    font-size: 15px;
    font-weight: 500;
    color: var(--gb-text);
    text-decoration: none;
    transition: color 0.2s ease;
}

.fr-header-nav a:hover {
    color: var(--gb-cyan);
}

@media (min-width: 900px) {
    .fr-header-nav {
        display: flex;
    }
}

.fr-header-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    height: 44px;
    padding: 0 20px;
    border-radius: var(--gb-radius-sm);
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    background: var(--gb-dark-2);
    color: var(--gb-white);
    border: 1px solid var(--gb-border-dark);
    transition: transform 0.2s ease, background 0.2s ease;
    white-space: nowrap;
}

.fr-header-btn:hover {
    transform: translateY(-1px);
    background: var(--gb-dark-3);
}

/* Buttons */
.fr-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    height: 52px;
    padding: 0 24px;
    border-radius: var(--gb-radius-sm);
    font-size: 16px;
    font-weight: 600;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: transform 0.2s ease, filter 0.2s ease, border-color 0.2s ease, background 0.2s ease;
}

.fr-btn:hover {
    transform: translateY(-1px);
}

.fr-btn-primary {
    background: var(--gb-cyan);
    color: #081013;
    box-shadow: 0 12px 32px rgba(82, 221, 248, 0.25);
}

.fr-btn-primary:hover {
    filter: brightness(1.05);
    background: var(--gb-cyan-hover);
    box-shadow: 0 16px 40px rgba(82, 221, 248, 0.35);
    transform: translateY(-2px);
}

.fr-btn-secondary {
    background: var(--gb-white);
    color: var(--gb-text);
    border: 1px solid var(--gb-border);
}

.fr-btn-secondary:hover {
    border-color: rgba(82, 221, 248, 0.65);
}

.fr-btn-dark {
    background: var(--gb-dark-2);
    color: var(--gb-white);
    border: 1px solid var(--gb-border-dark);
}

.fr-btn-dark:hover {
    background: var(--gb-dark-3);
}

/* Hero */
.fr-hero {
    position: relative;
    padding: 72px 0 88px;
    background: linear-gradient(135deg, #FFFFFF 0%, #F0FCFF 40%, #E0F8FF 100%);
    overflow: hidden;
}

.fr-hero::before {
    content: '';
    position: absolute;
    top: -20%;
    right: -5%;
    width: 700px;
    height: 700px;
    background: radial-gradient(circle, rgba(82, 221, 248, 0.28) 0%, rgba(82, 221, 248, 0.10) 35%, transparent 70%);
    pointer-events: none;
    animation: heroGlow 8s ease-in-out infinite;
}

.fr-hero::after {
    content: '';
    position: absolute;
    bottom: -15%;
    left: -10%;
    width: 500px;
    height: 500px;
    background: radial-gradient(circle, rgba(82, 221, 248, 0.16) 0%, transparent 65%);
    pointer-events: none;
    animation: heroGlow 10s ease-in-out infinite reverse;
}

@keyframes heroGlow {
    0%, 100% { opacity: 0.8; transform: scale(1); }
    50% { opacity: 1; transform: scale(1.05); }
}

.fr-hero-grid {
    position: relative;
    z-index: 2;
    display: grid;
    grid-template-columns: 1fr;
    gap: 40px;
    align-items: center;
}

@media (min-width: 900px) {
    .fr-hero-grid {
        grid-template-columns: 1.1fr 0.9fr;
        gap: 56px;
    }
}

.fr-hero-content {
    max-width: 600px;
}

.fr-hero-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 24px;
}

.fr-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: var(--gb-white);
    border: 1px solid var(--gb-border);
    border-radius: 999px;
    padding: 10px 16px;
    font-size: 14px;
    font-weight: 500;
    color: var(--gb-text);
    transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
}

.fr-badge:hover {
    transform: translateY(-2px);
    border-color: rgba(82, 221, 248, 0.4);
    box-shadow: 0 4px 12px rgba(82, 221, 248, 0.15);
}

.fr-badge .dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: var(--gb-cyan);
    box-shadow: 0 0 10px rgba(82, 221, 248, 0.7);
    animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.7; transform: scale(1.2); }
}

.fr-hero-title {
    margin: 0 0 20px;
}

.fr-hero-title .pre {
    display: block;
    font-size: clamp(22px, 3vw, 32px);
    font-weight: 600;
    color: var(--gb-text-secondary);
    margin-bottom: 8px;
    letter-spacing: -0.02em;
}

.fr-hero-logo-img {
    display: block;
    width: 100%;
    max-width: 520px;
    height: auto;
}

.fr-hero p {
    font-size: clamp(16px, 1.8vw, 18px);
    color: var(--gb-text-secondary);
    margin: 0 0 32px;
    max-width: 520px;
    line-height: 1.6;
}

.fr-hero-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
}

/* Hero card */
.fr-hero-visual {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
}

.fr-hero-card {
    position: relative;
    background: var(--gb-white);
    border: 1px solid var(--gb-border);
    border-radius: var(--gb-radius-lg);
    padding: 32px;
    box-shadow: 0 24px 60px rgba(82, 221, 248, 0.18), var(--gb-shadow);
    max-width: 400px;
    width: 100%;
    animation: heroCardFloat 6s ease-in-out infinite;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.fr-hero-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 32px 70px rgba(82, 221, 248, 0.25), var(--gb-shadow-lg);
}

@keyframes heroCardFloat {
    0%, 100% { transform: translateY(0) rotate(0deg); }
    50% { transform: translateY(-12px) rotate(1deg); }
}

.fr-hero-card-icon {
    width: 56px;
    height: 56px;
    border-radius: var(--gb-radius-md);
    background: linear-gradient(135deg, var(--gb-cyan), var(--gb-cyan-hover));
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 20px;
    box-shadow: 0 8px 20px rgba(82, 221, 248, 0.25);
}

.fr-hero-card-icon svg {
    width: 26px;
    height: 26px;
    color: var(--gb-white);
}

.fr-hero-card h3 {
    font-size: 22px;
    font-weight: 700;
    margin: 0 0 8px;
    letter-spacing: -0.02em;
}

.fr-hero-card p {
    font-size: 15px;
    color: var(--gb-text-secondary);
    margin: 0 0 20px;
}

.fr-hero-card-row {
    display: flex;
    justify-content: space-between;
    padding: 12px 0;
    border-bottom: 1px solid var(--gb-border);
    font-size: 14px;
}

.fr-hero-card-row:last-child {
    border-bottom: none;
}

.fr-hero-card-row span:last-child {
    font-weight: 700;
    color: var(--gb-cyan);
}

/* Section base */
.fr-section {
    padding: 88px 0;
}

.fr-section-header {
    max-width: 680px;
    margin-bottom: 48px;
}

.fr-section-label {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--gb-cyan);
    margin-bottom: 12px;
}

.fr-section-label::before {
    content: '';
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: var(--gb-cyan);
    box-shadow: 0 0 10px rgba(82, 221, 248, 0.7);
    animation: pulse 2s ease-in-out infinite;
}

.fr-section-title {
    font-size: clamp(30px, 3.5vw, 44px);
    font-weight: 800;
    margin: 0 0 14px;
    line-height: 1.05;
    letter-spacing: -0.03em;
}

.fr-section-subtitle {
    font-size: 17px;
    color: var(--gb-text-secondary);
    margin: 0;
    line-height: 1.6;
}

/* Brand story */
.fr-story {
    background: var(--gb-light-2);
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
        gap: 64px;
    }
}

.fr-story-image {
    position: relative;
    border-radius: var(--gb-radius-lg);
    overflow: hidden;
    background: linear-gradient(135deg, #FFFFFF 0%, #F0FCFF 100%);
    border: 1px solid var(--gb-border);
    min-height: 400px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.fr-story-image::before {
    content: '';
    position: absolute;
    inset: 0;
    background: radial-gradient(circle at 30% 30%, rgba(82, 221, 248, 0.08), transparent 60%);
}

.fr-story-image .brand-logo {
    width: auto;
    height: auto;
    max-width: 75%;
    max-height: 130px;
    object-fit: contain;
}

.fr-story-text p {
    font-size: 16px;
    color: var(--gb-text-secondary);
    margin: 0 0 16px;
    line-height: 1.65;
}

.fr-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 14px;
    margin-top: 32px;
}

.fr-stat {
    background: var(--gb-white);
    border: 1px solid var(--gb-border);
    border-radius: var(--gb-radius-md);
    padding: 24px;
}

.fr-stat-number {
    font-size: 28px;
    font-weight: 800;
    color: var(--gb-text);
    letter-spacing: -0.02em;
    white-space: nowrap;
}

.fr-stat-number span {
    color: var(--gb-cyan);
}

.fr-stat-label {
    font-size: 13px;
    color: var(--gb-text-secondary);
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
    background: var(--gb-white);
}

.fr-advantages-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 20px;
}

.fr-advantage {
    background: var(--gb-white);
    border: 1px solid var(--gb-border);
    border-radius: var(--gb-radius-md);
    padding: 28px;
    transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
    position: relative;
    overflow: hidden;
}

.fr-advantage::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 2px;
    background: linear-gradient(90deg, var(--gb-cyan), var(--gb-cyan-hover));
    transform: scaleX(0);
    transform-origin: left;
    transition: transform 0.3s ease;
}

.fr-advantage:hover {
    transform: translateY(-4px);
    box-shadow: var(--gb-shadow-lg);
    border-color: rgba(82, 221, 248, 0.65);
}

.fr-advantage:hover::before {
    transform: scaleX(1);
}

.fr-advantage-icon {
    width: 48px;
    height: 48px;
    border-radius: var(--gb-radius-sm);
    background: linear-gradient(135deg, var(--gb-cyan), var(--gb-cyan-hover));
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 18px;
    box-shadow: 0 6px 16px rgba(82, 221, 248, 0.22);
    transition: transform 0.25s ease, box-shadow 0.25s ease;
}

.fr-advantage:hover .fr-advantage-icon {
    transform: scale(1.08) rotate(-4deg);
    box-shadow: 0 10px 24px rgba(82, 221, 248, 0.35);
}

.fr-advantage-icon svg {
    width: 22px;
    height: 22px;
    color: var(--gb-white);
    transition: transform 0.25s ease;
}

.fr-advantage:hover .fr-advantage-icon svg {
    transform: scale(1.1);
}

.fr-advantage h3 {
    font-size: 18px;
    font-weight: 700;
    margin: 0 0 8px;
    letter-spacing: -0.01em;
}

.fr-advantage p {
    font-size: 14px;
    color: var(--gb-text-secondary);
    margin: 0;
    line-height: 1.6;
}

/* Franchise formats */
.fr-formats {
    background: var(--gb-light-2);
}

.fr-formats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
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
    position: relative;
    background: var(--gb-white);
    border: 1px solid var(--gb-border);
    border-radius: var(--gb-radius-md);
    padding: 24px;
    transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.fr-format-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--gb-cyan), var(--gb-cyan-hover));
    transform: scaleX(0);
    transform-origin: left;
    transition: transform 0.35s ease;
}

.fr-format-card::after {
    content: '';
    position: absolute;
    inset: 0;
    background: radial-gradient(circle at 20% 20%, rgba(82, 221, 248, 0.06), transparent 60%);
    opacity: 0;
    transition: opacity 0.3s ease;
    pointer-events: none;
}

.fr-format-card:hover {
    transform: translateY(-6px);
    box-shadow: var(--gb-shadow-lg), 0 20px 40px rgba(82, 221, 248, 0.12);
    border-color: rgba(82, 221, 248, 0.65);
}

.fr-format-card:hover::before {
    transform: scaleX(1);
}

.fr-format-card:hover::after {
    opacity: 1;
}

.fr-format-badge {
    display: inline-flex;
    align-self: flex-start;
    background: linear-gradient(135deg, var(--gb-cyan), var(--gb-cyan-hover));
    color: #081013;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    padding: 5px 10px;
    border-radius: 999px;
    margin-bottom: 14px;
}

.fr-format-card h3 {
    font-size: 20px;
    font-weight: 700;
    margin: 0 0 12px;
    letter-spacing: -0.02em;
}

.fr-format-list {
    list-style: none;
    margin: 0 0 18px;
    padding: 0;
    flex: 1;
}

.fr-format-list li {
    display: flex;
    justify-content: space-between;
    padding: 9px 0;
    border-bottom: 1px solid var(--gb-border);
    font-size: 13px;
}

.fr-format-list li:last-child {
    border-bottom: none;
}

.fr-format-list li span:first-child {
    color: var(--gb-text-secondary);
}

.fr-format-list li span:last-child {
    font-weight: 600;
    color: var(--gb-text);
    text-align: right;
    max-width: 55%;
}

.fr-format-profit {
    background: linear-gradient(135deg, rgba(82, 221, 248, 0.10), rgba(82, 221, 248, 0.04));
    border: 1px solid rgba(82, 221, 248, 0.25);
    border-radius: var(--gb-radius-sm);
    padding: 14px;
    text-align: center;
    margin-top: auto;
}

.fr-format-profit-value {
    font-size: 22px;
    font-weight: 800;
    background: linear-gradient(135deg, var(--gb-cyan), var(--gb-cyan-hover));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    letter-spacing: -0.02em;
}

.fr-format-profit-label {
    font-size: 12px;
    color: var(--gb-text-secondary);
    margin-top: 4px;
}

/* Conditions */
.fr-conditions {
    background: var(--gb-white);
}

.fr-conditions-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
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
    background: var(--gb-white);
    border: 1px solid var(--gb-border);
    border-radius: var(--gb-radius-md);
    padding: 24px;
    transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
}

.fr-condition-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--gb-shadow);
    border-color: rgba(82, 221, 248, 0.4);
}

.fr-condition-card h3 {
    font-size: 15px;
    font-weight: 600;
    margin: 0 0 10px;
    color: var(--gb-text-secondary);
}

.fr-condition-card .price {
    font-size: 32px;
    font-weight: 800;
    margin: 0 0 14px;
    color: var(--gb-text);
    letter-spacing: -0.02em;
}

.fr-condition-card .price span {
    color: var(--gb-cyan);
}

.fr-condition-card ul {
    margin: 0;
    padding-left: 16px;
    color: var(--gb-text-secondary);
    font-size: 14px;
}

.fr-condition-card li {
    margin-bottom: 6px;
    line-height: 1.5;
}

/* Calculator — dark section */
.fr-calculator {
    background: var(--gb-black);
    position: relative;
    overflow: hidden;
}

.fr-calculator::before {
    content: '';
    position: absolute;
    top: -20%;
    right: -10%;
    width: 700px;
    height: 700px;
    background: radial-gradient(circle, rgba(82, 221, 248, 0.14) 0%, transparent 60%);
    pointer-events: none;
}

.fr-calculator::after {
    content: '';
    position: absolute;
    bottom: -15%;
    left: -5%;
    width: 500px;
    height: 500px;
    background: radial-gradient(circle, rgba(82, 221, 248, 0.08) 0%, transparent 65%);
    pointer-events: none;
}

.fr-calculator .fr-section-title {
    color: var(--gb-dark-text);
}

.fr-calculator .fr-section-subtitle {
    color: var(--gb-dark-text-secondary);
}

.fr-calc-wrapper {
    display: grid;
    grid-template-columns: 1fr;
    gap: 24px;
    align-items: start;
}

@media (min-width: 900px) {
    .fr-calc-wrapper {
        grid-template-columns: 1fr 1fr;
        gap: 32px;
    }
}

.fr-calc-card {
    background: var(--gb-dark-1);
    border: 1px solid var(--gb-border-dark);
    border-radius: var(--gb-radius-md);
    padding: 32px;
}

.fr-field {
    margin-bottom: 24px;
}

.fr-field:last-child {
    margin-bottom: 0;
}

.fr-field label {
    display: flex;
    justify-content: space-between;
    font-weight: 500;
    margin-bottom: 10px;
    font-size: 14px;
    color: var(--gb-dark-text);
}

.fr-field input[type="range"] {
    width: 100%;
    height: 5px;
    border-radius: 3px;
    background: rgba(255, 255, 255, 0.14);
    outline: none;
    -webkit-appearance: none;
    appearance: none;
}

.fr-field input[type="range"]::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: var(--gb-cyan);
    cursor: pointer;
    border: none;
    box-shadow: 0 4px 14px rgba(82, 221, 248, 0.35);
}

.fr-field input[type="range"]::-moz-range-thumb {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: var(--gb-cyan);
    cursor: pointer;
    border: none;
    box-shadow: 0 4px 14px rgba(82, 221, 248, 0.35);
}

.fr-field .value {
    font-weight: 700;
    color: var(--gb-cyan);
}

.fr-calc-result {
    background: linear-gradient(145deg, var(--gb-dark-2), var(--gb-dark-1));
    border: 1px solid var(--gb-border-dark);
    border-radius: var(--gb-radius-md);
    padding: 32px;
    position: sticky;
    top: 88px;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.25);
}

.fr-calc-result h3 {
    font-size: 20px;
    font-weight: 700;
    margin: 0 0 24px;
    color: var(--gb-cyan);
    letter-spacing: -0.01em;
}

.fr-result-row {
    display: flex;
    justify-content: space-between;
    padding: 12px 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    font-size: 15px;
    color: var(--gb-dark-text-secondary);
}

.fr-result-row span:last-child {
    color: var(--gb-dark-text);
}

.fr-result-row.total {
    border-bottom: none;
    padding-top: 20px;
    font-size: 22px;
    font-weight: 800;
}

.fr-result-row.total span:last-child {
    color: var(--gb-cyan);
}

.fr-calc-note {
    margin-top: 20px;
    font-size: 13px;
    color: var(--gb-dark-text-secondary);
    line-height: 1.5;
}

/* Forms */
.fr-forms {
    background: linear-gradient(180deg, var(--gb-white) 0%, var(--gb-light-2) 100%);
}

.fr-forms-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 24px;
}

@media (min-width: 900px) {
    .fr-forms-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

.fr-form-card {
    background: var(--gb-white);
    border: 1px solid var(--gb-border);
    border-radius: var(--gb-radius-lg);
    padding: 36px;
    box-shadow: var(--gb-shadow);
    transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
}

.fr-form-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--gb-shadow-lg);
    border-color: rgba(82, 221, 248, 0.4);
}

.fr-form-card h3 {
    font-size: 22px;
    font-weight: 700;
    margin: 0 0 6px;
    letter-spacing: -0.02em;
}

.fr-form-card > p {
    color: var(--gb-text-secondary);
    margin: 0 0 24px;
    font-size: 15px;
}

.fr-input {
    width: 100%;
    height: 50px;
    padding: 0 16px;
    border: 1px solid var(--gb-border);
    border-radius: var(--gb-radius-sm);
    font-size: 15px;
    margin-bottom: 12px;
    background: var(--gb-white);
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
    font-family: inherit;
}

.fr-input:focus {
    outline: none;
    border-color: var(--gb-cyan);
    box-shadow: 0 0 0 3px rgba(82, 221, 248, 0.12);
}

.fr-input::placeholder {
    color: #9CA3AF;
}

.fr-textarea {
    min-height: 110px;
    padding: 14px 16px;
    resize: vertical;
}

.fr-alert {
    padding: 12px 16px;
    border-radius: var(--gb-radius-sm);
    margin-bottom: 16px;
    font-size: 14px;
}

.fr-alert-success {
    background: #ECFDF3;
    color: #15703E;
}

.fr-alert-error {
    background: #FEF2F2;
    color: #B42318;
}

/* Footer */
.fr-footer {
    background: var(--gb-light-2);
    color: var(--gb-text-secondary);
    padding: 40px 0;
    text-align: center;
    font-size: 14px;
    border-top: 1px solid var(--gb-border);
}

.fr-footer a {
    color: var(--gb-text);
    text-decoration: none;
    font-weight: 600;
}

/* Responsive */
@media (max-width: 900px) {
    .fr-section {
        padding: 64px 0;
    }

    .fr-hero {
        padding: 48px 0 64px;
    }

    .fr-calc-result {
        position: static;
    }
}

@media (max-width: 600px) {
    .fr-btn {
        width: 100%;
    }

    .fr-hero-title .pre {
        font-size: 20px;
        margin-bottom: 6px;
    }

    .fr-hero-logo-img {
        max-width: 100%;
    }

    .fr-hero-card {
        padding: 24px;
    }

    .fr-form-card {
        padding: 28px;
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
        <div class="fr-container">
            <div class="fr-hero-grid">
                <div class="fr-hero-content">
                    <div class="fr-hero-badges">
                        <div class="fr-badge"><span class="dot"></span> Прибыль <strong>66–89 тыс. ₽/мес</strong></div>
                        <div class="fr-badge"><span class="dot"></span> Окупаемость <strong>1–4 месяца</strong></div>
                    </div>
                    <div class="fr-hero-title">
                        <span class="pre">Откройте свой</span>
                        <img src="/images/franchise/gadget-bar-logo.png" alt="Gadget Bar" class="fr-hero-logo-img">
                    </div>
                    <p>Франшиза магазина техники и аксессуаров с готовой моделью, обучением и поддержкой на всех этапах запуска.</p>
                    <div class="fr-hero-buttons">
                        <a href="#calculator" class="fr-btn fr-btn-primary">Рассчитать прибыль</a>
                        <a href="#forms" class="fr-btn fr-btn-secondary">Оставить заявку</a>
                    </div>
                </div>
                <div class="fr-hero-visual">
                    <div class="fr-hero-card">
                        <div class="fr-hero-card-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"></path>
                            </svg>
                        </div>
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
                            <div class="fr-stat-number"><span class="counter" data-target="30">0</span>+</div>
                            <div class="fr-stat-label">заказов в день в активной точке</div>
                        </div>
                        <div class="fr-stat">
                            <div class="fr-stat-number"><span class="counter" data-target="89">0</span> <span>тыс. ₽</span></div>
                            <div class="fr-stat-label">прибыль в месяц до</div>
                        </div>
                        <div class="fr-stat">
                            <div class="fr-stat-number"><span class="counter" data-target="4">0</span> <span>мес.</span></div>
                            <div class="fr-stat-label">окупаемость до</div>
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
                    <div class="fr-advantage-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                            <polyline points="9 22 9 12 15 12 15 22"></polyline>
                        </svg>
                    </div>
                    <h3>Готовая концепция</h3>
                    <p>Единые стандарты магазина, витрины, мерчандайзинга и сервиса.</p>
                </div>
                <div class="fr-advantage reveal reveal-delay-2">
                    <div class="fr-advantage-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                            <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                            <line x1="12" y1="22.08" x2="12" y2="12"></line>
                        </svg>
                    </div>
                    <h3>Прямые поставки</h3>
                    <p>Работаем с крупными дистрибьюторами и брендами напрямую.</p>
                </div>
                <div class="fr-advantage reveal reveal-delay-3">
                    <div class="fr-advantage-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 10v6M2 10l10-5 10 5-10 5z"></path>
                            <path d="M6 12v5c3 3 9 3 12 0v-5"></path>
                        </svg>
                    </div>
                    <h3>Обучение персонала</h3>
                    <p>Программа онбординга для владельца и продавцов-консультантов.</p>
                </div>
                <div class="fr-advantage reveal reveal-delay-4">
                    <div class="fr-advantage-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                        </svg>
                    </div>
                    <h3>Маркетинг и реклама</h3>
                    <p>Рекламная подписка — первые 2 месяца бесплатно.</p>
                </div>
                <div class="fr-advantage reveal reveal-delay-1">
                    <div class="fr-advantage-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                            <line x1="8" y1="21" x2="16" y2="21"></line>
                            <line x1="12" y1="17" x2="12" y2="21"></line>
                        </svg>
                    </div>
                    <h3>IT-инфраструктура</h3>
                    <p>Сайт, бот, CRM и учётная система для работы с каталогом и заказами.</p>
                </div>
                <div class="fr-advantage reveal reveal-delay-2">
                    <div class="fr-advantage-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                    </div>
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
            <p class="reveal" style="margin-top: 20px; font-size: 13px; color: var(--gb-text-secondary);">* Расчётные значения. Точные цифры зависят от города, локации и сезона.</p>
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

    // Animated counters
    const counterObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const counter = entry.target;
                const target = parseInt(counter.dataset.target);
                const duration = 1200;
                const step = target / (duration / 16);
                let current = 0;

                const timer = setInterval(() => {
                    current += step;
                    if (current >= target) {
                        counter.textContent = target;
                        clearInterval(timer);
                    } else {
                        counter.textContent = Math.floor(current);
                    }
                }, 16);

                counterObserver.unobserve(counter);
            }
        });
    }, { threshold: 0.5 });

    document.querySelectorAll('.counter').forEach(el => counterObserver.observe(el));

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
