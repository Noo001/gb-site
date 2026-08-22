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
    background: linear-gradient(135deg, #FFFFFF 0%, #E0F9FF 30%, #C8F2FF 60%, #E0F9FF 100%);
    background-size: 200% 200%;
    animation: heroGradient 12s ease infinite;
    overflow: hidden;
}

@keyframes heroGradient {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

.fr-hero::before {
    content: '';
    position: absolute;
    top: -25%;
    right: -10%;
    width: 800px;
    height: 800px;
    background: radial-gradient(circle, rgba(82, 221, 248, 0.42) 0%, rgba(82, 221, 248, 0.16) 35%, transparent 70%);
    pointer-events: none;
    animation: heroGlow 8s ease-in-out infinite;
}

.fr-hero::after {
    content: '';
    position: absolute;
    bottom: -20%;
    left: -15%;
    width: 600px;
    height: 600px;
    background: radial-gradient(circle, rgba(82, 221, 248, 0.28) 0%, transparent 65%);
    pointer-events: none;
    animation: heroGlow 10s ease-in-out infinite reverse;
}

@keyframes heroGlow {
    0%, 100% { opacity: 0.85; transform: scale(1); }
    50% { opacity: 1; transform: scale(1.12); }
}

.fr-hero-blobs {
    position: absolute;
    inset: 0;
    overflow: hidden;
    pointer-events: none;
    z-index: 1;
}

.fr-blob {
    position: absolute;
    border-radius: 50%;
    filter: blur(70px);
    opacity: 0.75;
    animation: blobFloat 14s ease-in-out infinite;
}

.fr-blob-1 {
    width: 500px;
    height: 500px;
    background: radial-gradient(circle, rgba(82, 221, 248, 0.95), transparent 70%);
    top: -14%;
    right: -10%;
}

.fr-blob-2 {
    width: 380px;
    height: 380px;
    background: radial-gradient(circle, rgba(82, 221, 248, 0.65), transparent 70%);
    bottom: 0%;
    left: -12%;
    animation-delay: -5s;
}

.fr-blob-3 {
    width: 300px;
    height: 300px;
    background: radial-gradient(circle, rgba(82, 221, 248, 0.55), transparent 70%);
    top: 38%;
    right: 26%;
    animation-delay: -9s;
}

@keyframes blobFloat {
    0%, 100% { transform: translate(0, 0) scale(1); }
    33% { transform: translate(30px, -40px) scale(1.08); }
    66% { transform: translate(-25px, 30px) scale(0.96); }
}

.fr-hero-rings {
    position: absolute;
    inset: 0;
    pointer-events: none;
    z-index: 1;
}

.fr-hero-ring {
    position: absolute;
    border: 1px solid rgba(82, 221, 248, 0.22);
    border-radius: 50%;
    animation: ringPulse 7s ease-out infinite;
}

.fr-hero-ring:nth-child(1) {
    width: 560px;
    height: 560px;
    top: 5%;
    right: 1%;
}

.fr-hero-ring:nth-child(2) {
    width: 420px;
    height: 420px;
    top: 14%;
    right: 8%;
    animation-delay: -2.5s;
}

.fr-hero-ring:nth-child(3) {
    width: 280px;
    height: 280px;
    top: 22%;
    right: 16%;
    animation-delay: -5s;
}

@keyframes ringPulse {
    0% { transform: scale(0.8); opacity: 0.65; }
    60% { opacity: 0.18; }
    100% { transform: scale(1.35); opacity: 0; }
}

.fr-hero-scene {
    position: absolute;
    right: -30px;
    top: 50%;
    width: 540px;
    transform: translateY(-50%);
    pointer-events: none;
    z-index: 1;
    opacity: 0;
    animation: sceneFloat 10s ease-in-out infinite, heroSceneIn 1.2s ease forwards;
    animation-delay: 0s, 0.75s;
    filter: drop-shadow(0 30px 60px rgba(82, 221, 248, 0.18));
}

.fr-hero-scene svg {
    width: 100%;
    height: auto;
    display: block;
}

.scene-phone { animation: scenePhone 8s ease-in-out infinite; transform-origin: 260px 220px; }
.scene-watch { animation: sceneWatch 7s ease-in-out infinite; transform-origin: 120px 280px; }
.scene-buds { animation: sceneBuds 9s ease-in-out infinite; transform-origin: 380px 300px; }
.scene-pad { animation: scenePad 11s ease-in-out infinite; transform-origin: 420px 160px; }
.scene-sparkle { animation: sparkle 3s ease-in-out infinite; }
.scene-sparkle-2 { animation: sparkle 4s ease-in-out infinite 1s; }

@media (max-width: 1200px) {
    .fr-hero-scene { width: 420px; right: -60px; }
}

@media (max-width: 900px) {
    .fr-hero-scene { display: none; }
}

@keyframes sceneFloat {
    0%, 100% { transform: translateY(-50%) rotate(0deg); }
    50% { transform: translateY(calc(-50% - 16px)) rotate(1deg); }
}

@keyframes scenePhone {
    0%, 100% { transform: rotate(-8deg) translateY(0); }
    50% { transform: rotate(-5deg) translateY(-12px); }
}

@keyframes sceneWatch {
    0%, 100% { transform: rotate(10deg) translateY(0); }
    50% { transform: rotate(14deg) translateY(-10px); }
}

@keyframes sceneBuds {
    0%, 100% { transform: rotate(6deg) translateY(0); }
    50% { transform: rotate(3deg) translateY(-14px); }
}

@keyframes scenePad {
    0%, 100% { transform: rotate(-4deg) translateY(0); }
    50% { transform: rotate(-7deg) translateY(-8px); }
}

@keyframes sparkle {
    0%, 100% { opacity: 0.35; transform: scale(0.9); }
    50% { opacity: 1; transform: scale(1.15); }
}

/* Hero entrance animations */
.fr-hero-content > * {
    opacity: 0;
    transform: translateY(22px);
    animation: heroFadeInUp 0.7s cubic-bezier(0.22, 1, 0.36, 1) forwards;
}

.fr-hero-badges { animation-delay: 0.12s; }
.fr-hero-title { animation-delay: 0.22s; }
.fr-hero-content > p { animation-delay: 0.34s; }
.fr-hero-buttons { animation-delay: 0.46s; }

.fr-hero-visual {
    opacity: 0;
    transform: translateY(30px) scale(0.96);
    animation: heroVisualIn 1s cubic-bezier(0.22, 1, 0.36, 1) forwards;
    animation-delay: 0.4s;
}

@keyframes heroFadeInUp {
    to { opacity: 1; transform: translateY(0); }
}

@keyframes heroVisualIn {
    to { opacity: 1; transform: translateY(0) scale(1); }
}

@keyframes heroSceneIn {
    to { opacity: 0.95; }
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
    perspective: 1000px;
}

.fr-hero-card-wrapper {
    transition: transform 0.12s ease-out;
    transform-style: preserve-3d;
    will-change: transform;
}

.fr-hero-card {
    position: relative;
    background: rgba(255, 255, 255, 0.92);
    border: 1px solid rgba(82, 221, 248, 0.35);
    border-radius: var(--gb-radius-lg);
    padding: 32px;
    box-shadow: 0 28px 70px rgba(82, 221, 248, 0.28), 0 10px 30px rgba(0, 0, 0, 0.04);
    max-width: 400px;
    width: 100%;
    animation: heroCardFloat 5s ease-in-out infinite;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    transform-style: preserve-3d;
    overflow: hidden;
    backdrop-filter: blur(6px);
}

.fr-hero-card::before {
    content: '';
    position: absolute;
    top: -60%;
    left: -60%;
    width: 220%;
    height: 220%;
    background: linear-gradient(45deg, transparent 42%, rgba(255,255,255,0.45) 50%, transparent 58%);
    transform: rotate(25deg) translateX(-130%);
    transition: transform 1s ease;
    pointer-events: none;
    z-index: 2;
}

.fr-hero-card:hover::before {
    transform: rotate(25deg) translateX(130%);
}

.fr-hero-card:hover {
    transform: translateY(-6px) rotateX(2deg) rotateY(-2deg);
    box-shadow: 0 36px 80px rgba(82, 221, 248, 0.32), 0 12px 30px rgba(0, 0, 0, 0.05);
}

.fr-hero-card-glow {
    position: absolute;
    inset: -40px;
    background: radial-gradient(circle at 50% 50%, rgba(82, 221, 248, 0.5), transparent 60%);
    filter: blur(30px);
    z-index: -1;
    animation: glowPulse 4s ease-in-out infinite;
    pointer-events: none;
}

@keyframes glowPulse {
    0%, 100% { opacity: 0.75; transform: scale(1); }
    50% { opacity: 1; transform: scale(1.12); }
}

@keyframes heroCardFloat {
    0%, 100% { transform: translateY(0) rotateX(0deg) rotateY(0deg); }
    25% { transform: translateY(-14px) rotateX(2deg) rotateY(1.5deg); }
    50% { transform: translateY(-8px) rotateX(-1deg) rotateY(-1.5deg); }
    75% { transform: translateY(-16px) rotateX(1deg) rotateY(1deg); }
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

.fr-hero-metrics {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
    margin-top: 18px;
    padding-top: 18px;
    border-top: 1px solid var(--gb-border);
}

.fr-hero-metric {
    text-align: center;
    padding: 12px 8px;
    background: rgba(82, 221, 248, 0.06);
    border: 1px solid rgba(82, 221, 248, 0.15);
    border-radius: var(--gb-radius-md);
    transition: transform 0.2s ease, background 0.2s ease;
}

.fr-hero-metric:hover {
    transform: translateY(-3px);
    background: rgba(82, 221, 248, 0.10);
}

.fr-hero-metric span {
    display: block;
    font-size: 18px;
    font-weight: 800;
    color: var(--gb-cyan);
    line-height: 1.2;
    margin-bottom: 4px;
}

.fr-hero-metric small {
    display: block;
    font-size: 11px;
    color: var(--gb-text-secondary);
    line-height: 1.25;
}

@media (max-width: 420px) {
    .fr-hero-metrics {
        gap: 8px;
    }
    .fr-hero-metric {
        padding: 10px 4px;
    }
    .fr-hero-metric span {
        font-size: 15px;
    }
    .fr-hero-metric small {
        font-size: 10px;
    }
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
    transition: transform 0.4s cubic-bezier(0.22, 1, 0.36, 1), box-shadow 0.4s ease;
}

.fr-story-image::before {
    content: '';
    position: absolute;
    inset: 0;
    background: radial-gradient(circle at 30% 30%, rgba(82, 221, 248, 0.12), transparent 60%);
}

.fr-story-image::after {
    content: '';
    position: absolute;
    inset: -40px;
    background: radial-gradient(circle at 50% 50%, rgba(82, 221, 248, 0.2), transparent 60%);
    filter: blur(30px);
    z-index: -1;
    animation: storyGlow 5s ease-in-out infinite;
    pointer-events: none;
}

@keyframes storyGlow {
    0%, 100% { opacity: 0.7; transform: scale(1); }
    50% { opacity: 1; transform: scale(1.06); }
}

.fr-story-image:hover {
    transform: translateY(-4px) scale(1.01);
    box-shadow: 0 24px 60px rgba(82, 221, 248, 0.12);
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
    transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
}

.fr-stat:hover {
    transform: translateY(-4px);
    box-shadow: 0 14px 34px rgba(82, 221, 248, 0.1);
    border-color: rgba(82, 221, 248, 0.35);
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
    transition: transform 0.3s cubic-bezier(0.22, 1, 0.36, 1), box-shadow 0.3s ease, border-color 0.3s ease;
    position: relative;
    overflow: hidden;
}

.fr-advantage::before {
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

.fr-advantage::after {
    content: '';
    position: absolute;
    inset: 0;
    background: radial-gradient(circle at 50% 0%, rgba(82, 221, 248, 0.08), transparent 60%);
    opacity: 0;
    transition: opacity 0.3s ease;
    pointer-events: none;
}

.fr-advantage:hover {
    transform: translateY(-6px);
    box-shadow: 0 20px 45px rgba(82, 221, 248, 0.14), var(--gb-shadow-lg);
    border-color: rgba(82, 221, 248, 0.55);
}

.fr-advantage:hover::before {
    transform: scaleX(1);
}

.fr-advantage:hover::after {
    opacity: 1;
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
    position: relative;
    overflow: hidden;
}

.fr-formats::before {
    content: '';
    position: absolute;
    top: -12%;
    right: -8%;
    width: 420px;
    height: 420px;
    background: radial-gradient(circle, rgba(82, 221, 248, 0.14), transparent 65%);
    pointer-events: none;
    animation: formatGlow 10s ease-in-out infinite;
}

.fr-formats::after {
    content: '';
    position: absolute;
    bottom: -12%;
    left: -8%;
    width: 320px;
    height: 320px;
    background: radial-gradient(circle, rgba(82, 221, 248, 0.09), transparent 65%);
    pointer-events: none;
    animation: formatGlow 12s ease-in-out infinite reverse;
}

@keyframes formatGlow {
    0%, 100% { opacity: 0.8; transform: scale(1); }
    50% { opacity: 1; transform: scale(1.1); }
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
    transition: transform 0.35s cubic-bezier(0.22, 1, 0.36, 1), box-shadow 0.35s ease, border-color 0.35s ease;
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
    transition: transform 0.4s ease;
}

.fr-format-card::after {
    content: '';
    position: absolute;
    inset: 0;
    background: radial-gradient(circle at 50% 0%, rgba(82, 221, 248, 0.1), transparent 65%);
    opacity: 0;
    transition: opacity 0.35s ease;
    pointer-events: none;
}

.fr-format-card:hover {
    transform: translateY(-8px) scale(1.015);
    box-shadow: 0 28px 60px rgba(82, 221, 248, 0.16), 0 12px 30px rgba(0, 0, 0, 0.05);
    border-color: rgba(82, 221, 248, 0.55);
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
    margin-bottom: 12px;
}

.fr-format-icon {
    width: 46px;
    height: 46px;
    border-radius: var(--gb-radius-sm);
    background: linear-gradient(135deg, var(--gb-cyan), var(--gb-cyan-hover));
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 14px;
    box-shadow: 0 6px 16px rgba(82, 221, 248, 0.22);
    transition: transform 0.25s ease, box-shadow 0.25s ease;
}

.fr-format-card:hover .fr-format-icon {
    transform: scale(1.08) rotate(-4deg);
    box-shadow: 0 10px 24px rgba(82, 221, 248, 0.35);
}

.fr-format-icon svg {
    width: 22px;
    height: 22px;
    color: var(--gb-white);
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
    background: linear-gradient(135deg, rgba(82, 221, 248, 0.14), rgba(82, 221, 248, 0.05));
    border: 1px solid rgba(82, 221, 248, 0.3);
    border-radius: var(--gb-radius-sm);
    padding: 16px;
    text-align: center;
    margin-top: auto;
    position: relative;
    overflow: hidden;
    transition: transform 0.25s ease, box-shadow 0.25s ease;
}

.fr-format-card:hover .fr-format-profit {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(82, 221, 248, 0.12);
}

.fr-format-profit-value {
    font-size: 22px;
    font-weight: 800;
    background: linear-gradient(135deg, var(--gb-cyan), #2BC8EA);
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
    position: relative;
    overflow: hidden;
}

.fr-conditions::before {
    content: '';
    position: absolute;
    top: -8%;
    left: -6%;
    width: 360px;
    height: 360px;
    background: radial-gradient(circle, rgba(82, 221, 248, 0.1), transparent 65%);
    pointer-events: none;
    animation: conditionGlow 11s ease-in-out infinite;
}

.fr-conditions::after {
    content: '';
    position: absolute;
    bottom: -10%;
    right: -6%;
    width: 300px;
    height: 300px;
    background: radial-gradient(circle, rgba(82, 221, 248, 0.07), transparent 65%);
    pointer-events: none;
    animation: conditionGlow 13s ease-in-out infinite reverse;
}

@keyframes conditionGlow {
    0%, 100% { opacity: 0.8; transform: scale(1); }
    50% { opacity: 1; transform: scale(1.08); }
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
    transition: transform 0.3s cubic-bezier(0.22, 1, 0.36, 1), box-shadow 0.3s ease, border-color 0.3s ease;
    position: relative;
    overflow: hidden;
}

.fr-condition-card::before {
    content: '';
    position: absolute;
    inset: 0;
    background: radial-gradient(circle at 50% 0%, rgba(82, 221, 248, 0.06), transparent 60%);
    opacity: 0;
    transition: opacity 0.3s ease;
    pointer-events: none;
}

.fr-condition-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 18px 40px rgba(82, 221, 248, 0.1), var(--gb-shadow);
    border-color: rgba(82, 221, 248, 0.45);
}

.fr-condition-card:hover::before {
    opacity: 1;
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
    background: linear-gradient(135deg, var(--gb-text), #3a3d44);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    letter-spacing: -0.02em;
}

.fr-condition-card .price span {
    color: var(--gb-cyan);
    -webkit-text-fill-color: var(--gb-cyan);
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
    background: radial-gradient(circle, rgba(82, 221, 248, 0.16) 0%, transparent 60%);
    pointer-events: none;
    animation: calcGlow 10s ease-in-out infinite;
}

.fr-calculator::after {
    content: '';
    position: absolute;
    bottom: -15%;
    left: -5%;
    width: 500px;
    height: 500px;
    background: radial-gradient(circle, rgba(82, 221, 248, 0.1) 0%, transparent 65%);
    pointer-events: none;
    animation: calcGlow 12s ease-in-out infinite reverse;
}

@keyframes calcGlow {
    0%, 100% { opacity: 0.8; transform: scale(1); }
    50% { opacity: 1; transform: scale(1.08); }
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
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.25), inset 0 1px 0 rgba(255,255,255,0.04);
    position: relative;
    overflow: hidden;
}

.fr-calc-result::before {
    content: '';
    position: absolute;
    top: -40%;
    right: -40%;
    width: 80%;
    height: 80%;
    background: radial-gradient(circle, rgba(82, 221, 248, 0.12), transparent 60%);
    pointer-events: none;
    animation: calcResultGlow 6s ease-in-out infinite;
}

@keyframes calcResultGlow {
    0%, 100% { opacity: 0.6; transform: translateY(0); }
    50% { opacity: 1; transform: translateY(10px); }
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
    text-shadow: 0 0 24px rgba(82, 221, 248, 0.25);
}

.fr-result-row.total span:last-child {
    color: var(--gb-cyan);
    text-shadow: 0 0 18px rgba(82, 221, 248, 0.35);
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
    transition: transform 0.3s cubic-bezier(0.22, 1, 0.36, 1), box-shadow 0.3s ease, border-color 0.3s ease;
    position: relative;
    overflow: hidden;
}

.fr-form-card::before {
    content: '';
    position: absolute;
    inset: 0;
    background: radial-gradient(circle at 50% 0%, rgba(82, 221, 248, 0.08), transparent 60%);
    opacity: 0;
    transition: opacity 0.3s ease;
    pointer-events: none;
}

.fr-form-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 22px 50px rgba(82, 221, 248, 0.12), var(--gb-shadow-lg);
    border-color: rgba(82, 221, 248, 0.45);
}

.fr-form-card:hover::before {
    opacity: 1;
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
        <div class="fr-hero-blobs" aria-hidden="true">
            <div class="fr-blob fr-blob-1"></div>
            <div class="fr-blob fr-blob-2"></div>
            <div class="fr-blob fr-blob-3"></div>
        </div>
        <div class="fr-hero-rings" aria-hidden="true">
            <div class="fr-hero-ring"></div>
            <div class="fr-hero-ring"></div>
            <div class="fr-hero-ring"></div>
        </div>
        <div class="fr-hero-scene" aria-hidden="true">
            <svg viewBox="0 0 500 420" fill="none" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <linearGradient id="phoneBody" x1="0" y1="0" x2="1" y2="1">
                        <stop offset="0%" stop-color="#2A2A2A"/>
                        <stop offset="100%" stop-color="#0F0F0F"/>
                    </linearGradient>
                    <linearGradient id="phoneScreen" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="#E6FBFF"/>
                        <stop offset="50%" stop-color="#52DDF8"/>
                        <stop offset="100%" stop-color="#0EA5C7"/>
                    </linearGradient>
                    <linearGradient id="watchFace" x1="0" y1="0" x2="1" y2="1">
                        <stop offset="0%" stop-color="#F2F2F2"/>
                        <stop offset="100%" stop-color="#D9D9D9"/>
                    </linearGradient>
                    <linearGradient id="cyanRing" x1="0" y1="0" x2="1" y2="1">
                        <stop offset="0%" stop-color="#52DDF8"/>
                        <stop offset="100%" stop-color="#0EA5C7"/>
                    </linearGradient>
                    <filter id="sceneShadow" x="-50%" y="-50%" width="200%" height="200%">
                        <feDropShadow dx="0" dy="18" stdDeviation="20" flood-color="#52DDF8" flood-opacity="0.18"/>
                        <feDropShadow dx="0" dy="8" stdDeviation="14" flood-color="#000" flood-opacity="0.18"/>
                    </filter>
                </defs>

                <!-- Wireless charging pad -->
                <g class="scene-pad" filter="url(#sceneShadow)">
                    <ellipse cx="395" cy="145" rx="58" ry="14" fill="#1A1A1A"/>
                    <ellipse cx="395" cy="140" rx="58" ry="14" fill="#2A2A2A"/>
                    <circle cx="395" cy="140" r="22" fill="none" stroke="url(#cyanRing)" stroke-width="3"/>
                    <circle cx="395" cy="140" r="14" fill="url(#cyanRing)" opacity="0.9"/>
                    <path d="M388 140h14M395 133v14" stroke="#fff" stroke-width="2" stroke-linecap="round"/>
                </g>

                <!-- Smartphone -->
                <g class="scene-phone" filter="url(#sceneShadow)">
                    <rect x="215" y="55" width="130" height="250" rx="22" fill="url(#phoneBody)"/>
                    <rect x="223" y="63" width="114" height="234" rx="16" fill="url(#phoneScreen)"/>
                    <rect x="253" y="71" width="54" height="6" rx="3" fill="#111"/>
                    <circle cx="273" cy="74" r="2" fill="#333"/>
                    <text x="280" y="170" text-anchor="middle" fill="#0F0F0F" font-size="22" font-weight="800" font-family="Arial, sans-serif" letter-spacing="-0.03em">GADGET</text>
                    <text x="280" y="192" text-anchor="middle" fill="#0F0F0F" font-size="22" font-weight="800" font-family="Arial, sans-serif" letter-spacing="-0.03em">BAR</text>
                    <circle cx="280" cy="205" r="4" fill="#0F0F0F"/>
                    <rect x="245" y="225" width="70" height="28" rx="6" fill="#0F0F0F"/>
                    <text x="280" y="244" text-anchor="middle" fill="#52DDF8" font-size="11" font-weight="700" font-family="Arial, sans-serif">ФРАНШИЗА</text>
                    <!-- Side buttons -->
                    <rect x="212" y="105" width="3" height="24" rx="1" fill="#444"/>
                    <rect x="212" y="138" width="3" height="36" rx="1" fill="#444"/>
                </g>

                <!-- Smartwatch -->
                <g class="scene-watch" filter="url(#sceneShadow)">
                    <path d="M85 235h70v110H85z" fill="#1A1A1A"/>
                    <rect x="82" y="260" width="76" height="80" rx="18" fill="#2A2A2A"/>
                    <rect x="86" y="264" width="68" height="72" rx="14" fill="url(#watchFace)"/>
                    <circle cx="120" cy="300" r="22" fill="none" stroke="url(#cyanRing)" stroke-width="4"/>
                    <circle cx="120" cy="300" r="5" fill="#111"/>
                    <path d="M120 286v14" stroke="#111" stroke-width="3" stroke-linecap="round"/>
                    <path d="M128 295l-8 5" stroke="#111" stroke-width="3" stroke-linecap="round"/>
                    <rect x="110" y="318" width="20" height="8" rx="4" fill="url(#cyanRing)"/>
                </g>

                <!-- Earbuds case -->
                <g class="scene-buds" filter="url(#sceneShadow)">
                    <rect x="330" y="260" width="90" height="65" rx="28" fill="url(#phoneBody)"/>
                    <rect x="340" y="270" width="70" height="45" rx="20" fill="#3A3A3A" opacity="0.6"/>
                    <line x1="375" y1="292" x2="375" y2="292" stroke="#fff" stroke-width="4" stroke-linecap="round"/>
                    <circle cx="360" cy="330" r="6" fill="#fff"/>
                    <circle cx="390" cy="330" r="6" fill="#fff"/>
                    <path d="M348 330h54" stroke="#222" stroke-width="2"/>
                </g>

                <!-- Sparkles -->
                <g class="scene-sparkle">
                    <path d="M155 95l3 8 8 3-8 3-3 8-3-8-8-3 8-3 3-8z" fill="#52DDF8"/>
                </g>
                <g class="scene-sparkle-2">
                    <path d="M445 245l2 6 6 2-6 2-2 6-2-6-6-2 6-2 2-6z" fill="#52DDF8"/>
                </g>
                <g class="scene-sparkle" style="animation-delay:1.5s">
                    <circle cx="200" cy="330" r="4" fill="#52DDF8"/>
                </g>
            </svg>
        </div>
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
                <div class="fr-hero-visual" id="heroVisual">
                    <div class="fr-hero-card-wrapper" id="heroCardWrapper">
                        <div class="fr-hero-card">
                            <div class="fr-hero-card-glow" aria-hidden="true"></div>
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
                        <div class="fr-hero-metrics">
                            <div class="fr-hero-metric">
                                <span>30+</span>
                                <small>заказов в день</small>
                            </div>
                            <div class="fr-hero-metric">
                                <span>89 тыс. ₽</span>
                                <small>прибыль</small>
                            </div>
                            <div class="fr-hero-metric">
                                <span>1–4 мес.</span>
                                <small>окупаемость</small>
                            </div>
                        </div>
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
                    <div class="fr-format-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18v-7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v7z"></path><path d="M4 14V7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v7"></path><line x1="12" y1="3" x2="12" y2="14"></line></svg>
                    </div>
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
                    <div class="fr-format-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
                    </div>
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
                    <div class="fr-format-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"></path><path d="M5 21V7l8-4 8 4v14"></path><path d="M10 9h4"></path><path d="M10 13h4"></path><path d="M10 17h4"></path></svg>
                    </div>
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
                    <div class="fr-format-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 10h16v10a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V10z"></path><path d="M6 10V6a3 3 0 0 1 3-3h6a3 3 0 0 1 3 3v4"></path><path d="M12 14v4"></path><path d="M8 14v4"></path><path d="M16 14v4"></path></svg>
                    </div>
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

    // 3D tilt for hero card on mouse move
    const heroVisual = document.getElementById('heroVisual');
    const heroWrapper = document.getElementById('heroCardWrapper');
    if (heroVisual && heroWrapper && window.matchMedia('(pointer: fine)').matches) {
        heroVisual.addEventListener('mousemove', (e) => {
            const rect = heroVisual.getBoundingClientRect();
            const x = (e.clientX - rect.left) / rect.width - 0.5;
            const y = (e.clientY - rect.top) / rect.height - 0.5;
            heroWrapper.style.transform = `rotateY(${x * 12}deg) rotateX(${-y * 12}deg)`;
        });
        heroVisual.addEventListener('mouseleave', () => {
            heroWrapper.style.transform = '';
        });
    }
});
</script>
@endpush
@endsection
