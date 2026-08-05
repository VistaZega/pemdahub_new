<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PembdaHUB — Smart School Management | Yayasan Perguruan PEMBDA Nias</title>
    <meta name="description" content="PembdaHUB - Sistem Manajemen Sekolah Cerdas Yayasan Perguruan PEMBDA Nias. RFID Attendance, Student Portal, Real-time Monitoring. Membentuk SDM Berkualitas sejak 1970.">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,400&display=swap" rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- PWA Manifest & App Shell Meta Tags -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#6366f1">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="PembdaHUB">
    <link rel="apple-touch-icon" href="/images/icons/icon-192x192.png">

    <script>
      if ('serviceWorker' in navigator) {
        window.addEventListener('load', function() {
          navigator.serviceWorker.register('/sw.js');
        });
      }
    </script>

    <!-- AOS -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        :root {
            /* === BOLD INDIGO PREMIUM THEME === */
            --bg: #f4f3ff;
            --bg-card: #ffffff;
            --text-primary: #0f0d2e;
            --text-secondary: #5b6478;
            --text-muted: #9ca3af;
            --border: #e0ddf7;
            --radius: 20px;
            --radius-sm: 14px;
            --shadow-card: 0 1px 4px rgba(79,46,209,0.06), 0 4px 16px rgba(79,46,209,0.05);
            --shadow-hover: 0 20px 60px -15px rgba(79,46,209,0.22);

            /* Brand - Indigo family */
            --indigo: #4f2ed1;
            --indigo-dark: #1e1b4b;
            --indigo-mid: #3730a3;
            --indigo-light: #6366f1;
            --indigo-bg: #eef2ff;

            /* Gold accent */
            --gold: #f59e0b;
            --gold-bright: #fbbf24;
            --gold-bg: #fffbeb;

            /* Feature colors */
            --coral: #ef4444;
            --coral-bg: #fff1f2;
            --blue: #3b82f6;
            --blue-bg: #eff6ff;
            --emerald: #10b981;
            --emerald-bg: #ecfdf5;
            --amber: #f59e0b;
            --amber-bg: #fffbeb;
            --violet: #8b5cf6;
            --violet-bg: #f5f3ff;
            --cyan: #06b6d4;
            --cyan-bg: #ecfeff;
            --navy: #1e1b4b;

            /* Glassmorphism */
            --glass-bg: rgba(255,255,255,0.06);
            --glass-border: rgba(255,255,255,0.1);
            --glass-blur: blur(12px);

            /* Transitions */
            --transition-smooth: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            --transition-fast: all 0.25s ease;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif;
            background: var(--bg);
            color: var(--text-primary);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            overflow-x: hidden;
        }

        html { scroll-behavior: smooth; }

        /* Full-width container */
        .fw { width: 100%; padding: 0 40px; }
        @media (max-width: 768px) { .fw { padding: 0 20px; } }

        /* Section spacing */
        .section { padding: 100px 0; }
        @media (max-width: 768px) { .section { padding: 64px 0; } }

        /* Bento Grid */
        .bento { display: grid; gap: 16px; }
        .bento-2 { grid-template-columns: repeat(2, 1fr); }
        .bento-3 { grid-template-columns: repeat(3, 1fr); }
        .bento-4 { grid-template-columns: repeat(4, 1fr); }
        @media (max-width: 1024px) {
            .bento-3, .bento-4 { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 640px) {
            .bento-2, .bento-3, .bento-4 { grid-template-columns: 1fr; }
        }

        /* Bento Card */
        .bcard {
            background: var(--bg-card);
            border-radius: var(--radius);
            padding: 32px;
            box-shadow: var(--shadow-card);
            border: 1px solid var(--border);
            transition: var(--transition-smooth);
            position: relative;
            overflow: hidden;
        }
        .bcard:hover {
            transform: translateY(-5px) scale(1.005);
            box-shadow: var(--shadow-hover);
            border-color: rgba(99,102,241,0.35);
        }
        .bcard.span-2 { grid-column: span 2; }
        .bcard.span-3 { grid-column: span 3; }
        @media (max-width: 640px) {
            .bcard.span-2, .bcard.span-3 { grid-column: span 1; }
        }

        /* Typography */
        .display { font-size: clamp(48px, 6vw, 80px); font-weight: 900; letter-spacing: -0.03em; line-height: 1.05; }
        .h1 { font-size: clamp(32px, 4vw, 48px); font-weight: 800; letter-spacing: -0.025em; line-height: 1.1; }
        .h2 { font-size: clamp(24px, 3vw, 36px); font-weight: 700; letter-spacing: -0.02em; line-height: 1.2; }
        .h3 { font-size: 20px; font-weight: 700; letter-spacing: -0.01em; }
        .body-lg { font-size: 18px; line-height: 1.7; color: var(--text-secondary); }
        .body { font-size: 15px; line-height: 1.7; color: var(--text-secondary); }
        .caption { font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.08em; }

        /* Badge - hero context (on dark bg) */
        .badge {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 10px 22px; border-radius: 100px;
            font-size: 13px; font-weight: 600;
            border: 1px solid rgba(255,255,255,0.18);
            background: rgba(255,255,255,0.1);
            color: rgba(255,255,255,0.92);
            backdrop-filter: blur(12px);
        }

        /* Buttons */
        .btn {
            display: inline-flex; align-items: center; gap: 10px;
            padding: 16px 32px; border-radius: 14px;
            font-weight: 700; font-size: 15px;
            text-decoration: none; border: none; cursor: pointer;
            transition: all 0.3s ease;
        }
        /* Gold - primary hero CTA */
        .btn-gold {
            background: linear-gradient(135deg, #f59e0b, #fbbf24);
            color: #1e1b4b;
            box-shadow: 0 4px 20px rgba(245,158,11,0.45);
        }
        .btn-gold:hover { transform: translateY(-2px); box-shadow: 0 8px 32px rgba(245,158,11,0.55); filter: brightness(1.05); }
        /* Ghost white - secondary on dark bg */
        .btn-ghost-white {
            background: rgba(255,255,255,0.1);
            color: rgba(255,255,255,0.92);
            border: 1.5px solid rgba(255,255,255,0.25);
            backdrop-filter: blur(10px);
        }
        .btn-ghost-white:hover { background: rgba(255,255,255,0.18); border-color: rgba(255,255,255,0.5); color: #fff; }
        /* Dark - nav CTA on light sections */
        .btn-dark {
            background: var(--indigo-dark); color: #fff;
        }
        .btn-dark:hover { background: var(--indigo-mid); transform: translateY(-2px); box-shadow: 0 8px 30px -8px rgba(30,27,75,0.4); }
        /* Ghost - light section secondary */
        .btn-ghost {
            background: transparent; color: var(--text-primary);
            border: 1.5px solid var(--border);
        }
        .btn-ghost:hover { border-color: var(--indigo-light); color: var(--indigo); background: var(--indigo-bg); }

        /* Section Label */
        .section-label {
            display: flex; align-items: center; gap: 12px;
            margin-bottom: 16px;
        }
        .section-label-dot {
            width: 8px; height: 8px; border-radius: 50%;
        }
        .section-label-text {
            font-size: 13px; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.1em;
        }

        /* Icon circle */
        .icon-circle {
            width: 52px; height: 52px; border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px; flex-shrink: 0;
        }

        /* Stat */
        .stat-num {
            font-size: clamp(40px, 5vw, 56px); font-weight: 900;
            letter-spacing: -0.03em; line-height: 1;
        }

        /* ============================================================
           NAVBAR — Bold Indigo (always dark background)
           ============================================================ */
        .navbar {
            position: fixed; top: 0; left: 0; right: 0; z-index: 100;
            transition: all 0.35s ease; padding: 18px 0;
            background: rgba(30,27,75,0.0); /* transparent initially, same indigo bg as hero */
        }
        .navbar.scrolled {
            background: rgba(30, 27, 75, 0.96);
            backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px);
            border-bottom: 1px solid rgba(255,255,255,0.07);
            padding: 12px 0;
            box-shadow: 0 4px 32px rgba(30,27,75,0.35);
        }
        .nav-inner {
            display: flex; align-items: center; justify-content: space-between;
        }
        .nav-links { display: flex; gap: 2px; align-items: center; }
        .nav-link {
            padding: 8px 14px; border-radius: 10px;
            font-size: 14px; font-weight: 500; color: rgba(255,255,255,0.72);
            text-decoration: none; transition: all 0.2s ease;
        }
        .nav-link:hover { color: #fff; background: rgba(255,255,255,0.1); }
        .nav-brand {
            display: flex; align-items: center; gap: 12px;
            text-decoration: none; color: #fff;
        }
        .nav-brand-text { display: flex; flex-direction: column; }
        .nav-brand-name {
            font-size: 16px; font-weight: 800; letter-spacing: -0.02em; color: #fff; line-height: 1.1;
        }
        .nav-brand-name span { color: #ef4444; }
        .nav-brand-sub { font-size: 10px; color: rgba(255,255,255,0.45); font-weight: 500; }
        .nav-logo {
            width: 40px; height: 40px; border-radius: 12px;
            overflow: hidden; display: flex; align-items: center; justify-content: center;
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.18);
        }

        /* Mobile nav */
        .mobile-menu-btn { display: none; background: none; border: none; font-size: 22px; color: rgba(255,255,255,0.85); cursor: pointer; padding: 8px; }
        .mobile-overlay {
            display: none; position: fixed; inset: 0; z-index: 999;
            background: rgba(18, 16, 55, 0.98); backdrop-filter: blur(30px);
            flex-direction: column; align-items: center; justify-content: center; gap: 8px;
        }
        .mobile-overlay.active { display: flex; }
        .mobile-overlay a {
            font-size: 22px; font-weight: 700; color: rgba(255,255,255,0.85);
            text-decoration: none; padding: 12px 28px; border-radius: 14px;
            transition: background 0.2s;
        }
        .mobile-overlay a:hover { background: rgba(255,255,255,0.1); color: #fff; }
        .mobile-close { position: absolute; top: 20px; right: 24px; font-size: 28px; background: none; border: none; cursor: pointer; color: rgba(255,255,255,0.6); }

        @media (max-width: 900px) {
            .nav-links { display: none; }
            .mobile-menu-btn { display: block; }
        }

        /* Feature pill */
        .feature-pill {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 6px 14px; border-radius: 10px;
            font-size: 12px; font-weight: 600;
        }

        /* Feature pill */
        .feature-pill {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 6px 14px; border-radius: 10px;
            font-size: 12px; font-weight: 600;
        }

        /* ============================================================
           BOLD INDIGO HERO SECTION
           ============================================================ */
        .hero-section {
            background: linear-gradient(160deg, #1e1b4b 0%, #2d2a6e 50%, #1e1b4b 100%);
            position: relative; overflow: hidden;
            min-height: 100vh; display: flex; align-items: center;
            padding-top: 100px; padding-bottom: 80px;
        }
        .hero-grid {
            position: absolute; inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,0.028) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.028) 1px, transparent 1px);
            background-size: 64px 64px;
            pointer-events: none;
        }
        .hero-glow-1 {
            position: absolute; top: -180px; right: -100px;
            width: 650px; height: 650px; border-radius: 50%;
            background: radial-gradient(circle, rgba(99,102,241,0.3) 0%, transparent 70%);
            pointer-events: none;
        }
        .hero-glow-2 {
            position: absolute; bottom: -120px; left: -80px;
            width: 480px; height: 480px; border-radius: 50%;
            background: radial-gradient(circle, rgba(139,92,246,0.18) 0%, transparent 70%);
            pointer-events: none;
        }
        .hero-glow-3 {
            position: absolute; top: 40%; left: 50%; transform: translateX(-50%);
            width: 800px; height: 300px; border-radius: 50%;
            background: radial-gradient(ellipse, rgba(245,158,11,0.06) 0%, transparent 70%);
            pointer-events: none;
        }
        /* Animated circles */
        .hero-ring {
            position: absolute; border-radius: 50%;
            border: 1px solid rgba(255,255,255,0.05);
            pointer-events: none;
            animation: ringPulse 6s ease-in-out infinite;
        }
        @keyframes ringPulse {
            0%, 100% { opacity: 0.5; transform: scale(1); }
            50% { opacity: 1; transform: scale(1.03); }
        }

        /* Hero feature mini cards */
        .hero-card {
            border-radius: 18px; padding: 22px 24px;
            transition: all 0.3s ease;
            position: relative; overflow: hidden;
            border: 1px solid rgba(255,255,255,0.1);
        }
        .hero-card:hover { transform: translateY(-4px); }
        .hero-card-blue { background: linear-gradient(135deg, #1d4ed8, #2563eb); box-shadow: 0 8px 32px rgba(37,99,235,0.35); }
        .hero-card-emerald { background: linear-gradient(135deg, #059669, #10b981); box-shadow: 0 8px 32px rgba(16,185,129,0.35); }
        .hero-card-gold { background: linear-gradient(135deg, #d97706, #f59e0b); box-shadow: 0 8px 32px rgba(245,158,11,0.35); }
        .hero-card-coral { background: linear-gradient(135deg, #dc2626, #ef4444); box-shadow: 0 8px 32px rgba(239,68,68,0.35); }

        /* Wave divider */
        .wave-top {
            width: 100%; overflow: hidden; line-height: 0;
            background: var(--bg);
        }
        .wave-top svg { display: block; fill: #1e1b4b; }

        /* Colored cards - more vibrant */
        .bcard-coral { background: linear-gradient(135deg, #fff1f2, #ffe4e6); border-color: #fca5a5; }
        .bcard-blue { background: linear-gradient(135deg, #eff6ff, #dbeafe); border-color: #93c5fd; }
        .bcard-emerald { background: linear-gradient(135deg, #ecfdf5, #d1fae5); border-color: #6ee7b7; }
        .bcard-amber { background: linear-gradient(135deg, #fffbeb, #fef3c7); border-color: #fcd34d; }
        .bcard-violet { background: linear-gradient(135deg, #f5f3ff, #ede9fe); border-color: #c4b5fd; }
        .bcard-cyan { background: linear-gradient(135deg, #ecfeff, #cffafe); border-color: #67e8f9; }
        .bcard-dark { background: linear-gradient(135deg, #1e1b4b, #2d2a6e); border-color: rgba(255,255,255,0.08); color: #fff; }
        .bcard-indigo { background: linear-gradient(135deg, #eef2ff, #e0e7ff); border-color: rgba(99,102,241,0.35); }
        .bcard-gold { background: linear-gradient(135deg, #fffbeb, #fef3c7); border-color: #fde68a; }

        /* Program sub-item */
        .prog-item {
            display: flex; align-items: center; gap: 10px;
            padding: 12px 16px; border-radius: 12px;
            background: rgba(0,0,0,0.03); margin-top: 8px;
            font-size: 14px; font-weight: 500;
        }

        /* News card image placeholder */
        .news-img {
            height: 180px; border-radius: 14px; margin-bottom: 20px;
            display: flex; align-items: center; justify-content: center;
            position: relative; overflow: hidden;
        }

        /* Gallery */
        .gallery-bento {
            display: grid; grid-template-columns: repeat(4, 1fr);
            grid-auto-rows: 180px; gap: 12px;
        }
        .gallery-bento > :first-child { grid-column: span 2; grid-row: span 2; }
        .gal-item {
            border-radius: 16px; overflow: hidden; position: relative;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; transition: var(--transition-smooth);
        }
        .gal-item img {
            transition: var(--transition-smooth);
            width: 100%; height: 100%; object-fit: cover;
        }
        .gal-item:hover img {
            transform: scale(1.06);
        }
        .gal-item::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(15,23,42,0.85) 0%, rgba(15,23,42,0.2) 60%, transparent 100%);
            opacity: 0;
            transition: opacity 0.35s ease;
            pointer-events: none;
        }
        .gal-item:hover::after {
            opacity: 1;
        }
        .gal-item i { font-size: 28px; opacity: 0.25; transition: var(--transition-smooth); }
        .gal-item:hover i { transform: scale(1.12); opacity: 0.4; }
        .gal-label {
            position: absolute; bottom: 12px; left: 12px;
            font-size: 13px; font-weight: 700; color: #fff;
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border: 1px solid rgba(255,255,255,0.1);
            padding: 6px 14px; border-radius: 10px;
            opacity: 0;
            transform: translateY(8px);
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1;
        }
        .gal-item:hover .gal-label {
            opacity: 1;
            transform: translateY(0);
        }
        @media (max-width: 768px) {
            .gallery-bento { grid-template-columns: repeat(2, 1fr); grid-auto-rows: 140px; }
        }
        /* ============================================================
           UNIT SEKOLAH — Hover & Icon Animation
           ============================================================ */
        .unit-card {
            transition: var(--transition-smooth) !important;
        }
        .unit-card:hover {
            transform: translateY(-6px) scale(1.015);
            box-shadow: var(--shadow-hover);
        }
        .unit-header {
            height: 160px; border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 24px; position: relative; overflow: hidden;
            transition: var(--transition-smooth);
        }
        .unit-card:hover .unit-header {
            filter: brightness(1.08);
        }
        .unit-card .unit-header i {
            transition: var(--transition-smooth);
        }
        .unit-card:hover .unit-header i {
            transform: scale(1.15) rotate(6deg);
        }

        /* Footer */
        .footer { background: var(--indigo-dark); color: #94a3b8; border-top: 1px solid rgba(255,255,255,0.05); }
        .footer a { color: #94a3b8; text-decoration: none; transition: color 0.2s; }
        .footer a:hover { color: var(--gold-bright); }

        /* Pulse */
        .pulse { width: 8px; height: 8px; border-radius: 50%; animation: pulseAnim 2s ease infinite; }
        @keyframes pulseAnim { 0%,100% { opacity:1; transform:scale(1); } 50% { opacity:0.4; transform:scale(1.4); } }

        /* Marquee */
        .marquee-track { display: flex; overflow: hidden; }
        .marquee-content {
            display: flex; gap: 12px;
            animation: marqueeScroll 30s linear infinite;
            white-space: nowrap;
        }
        .marquee-item {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 10px 20px; border-radius: 100px;
            font-size: 13px; font-weight: 600;
            color: var(--text-secondary);
            background: var(--bg);
            border: 1px solid var(--border);
            flex-shrink: 0;
        }
        .marquee-item i { font-size: 14px; color: var(--blue); }
        @keyframes marqueeScroll {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }

        /* Featured card responsive */
        @media (max-width: 768px) {
            .bento-2 > .bcard { grid-template-columns: 1fr !important; }
            .bento-2 > .bcard > div:last-child { min-height: 160px !important; }
        }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(99,102,241,0.35); border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(99,102,241,0.55); }

        /* Glow line accent */
        .glow-line {
            height: 3px;
            background: linear-gradient(90deg, transparent, var(--gold), var(--indigo-light), transparent);
            border-radius: 2px; margin-bottom: 32px;
        }

        /* === Typewriter Cursor Blink === */
        @keyframes typewriter-blink {
            0%, 100% { border-color: transparent; }
            50% { border-color: var(--gold-bright); }
        }
        .typewriter-cursor {
            animation: typewriter-blink 0.8s step-end infinite;
        }

        /* === Floating Particles Background === */
        .particle {
            position: absolute;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 50%;
            pointer-events: none;
            z-index: 0;
            animation: float-particle 15s linear infinite;
        }
        @keyframes float-particle {
            0% {
                transform: translateY(100%) translateX(0) scale(0.6);
                opacity: 0;
            }
            10% {
                opacity: 0.6;
            }
            90% {
                opacity: 0.6;
            }
            100% {
                transform: translateY(-20%) translateX(50px) scale(1.2);
                opacity: 0;
            }
        }

        /* === Shimmer / Glow Effects === */
        @keyframes shimmer-effect {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }
        .shimmer-card {
            position: relative;
            overflow: hidden;
        }
        .shimmer-card::after {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 200%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.06), transparent);
            transform: skewX(-20deg);
            animation: shimmer-effect 6s infinite linear;
            pointer-events: none;
        }

        /* === Animated Gradient Border === */
        .gradient-border-card {
            position: relative;
            z-index: 1;
        }
        .gradient-border-card::before {
            content: '';
            position: absolute;
            top: -2px; left: -2px; right: -2px; bottom: -2px;
            background: linear-gradient(135deg, var(--indigo-light), var(--gold), var(--violet), var(--cyan));
            border-radius: calc(var(--radius) + 2px);
            z-index: -1;
            opacity: 0;
            transition: opacity 0.4s ease;
            background-size: 300% 300%;
            animation: animate-gradient-border 8s ease infinite;
        }
        .gradient-border-card:hover::before {
            opacity: 1;
        }
        @keyframes animate-gradient-border {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* === Glow effect for interactive hover === */
        .hover-glow {
            transition: var(--transition-smooth);
        }
        .hover-glow:hover {
            box-shadow: 0 0 30px rgba(99, 102, 241, 0.35);
            border-color: var(--indigo-light);
        }

        /* ============================================================
           STAT CARD — Glassmorphism on dark backgrounds
           ============================================================ */
        .stat-card {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius);
            padding: 36px 28px;
            text-align: center;
            transition: var(--transition-smooth);
            backdrop-filter: var(--glass-blur);
            -webkit-backdrop-filter: var(--glass-blur);
        }
        .stat-card:hover {
            background: rgba(255,255,255,0.12);
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0,0,0,0.15);
        }
        .stat-card .stat-number {
            font-size: clamp(40px,5vw,56px);
            font-weight: 900;
            letter-spacing: -0.03em;
            line-height: 1;
        }
        .stat-card .stat-label {
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: rgba(255,255,255,0.5);
            margin-top: 10px;
        }
        .stat-card .stat-line {
            width: 32px;
            height: 2px;
            border-radius: 1px;
            margin: 12px auto 0;
        }

        /* ============================================================
           HERO CARDS — Responsive Grid
           ============================================================ */
        .hero-card-icon {
            width: 44px; height: 44px; border-radius: 14px;
            background: rgba(255,255,255,0.15);
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 14px; font-size: 20px; color: #fff;
            transition: var(--transition-fast);
        }
        .hero-card:hover .hero-card-icon {
            background: rgba(255,255,255,0.25);
            transform: scale(1.08);
        }
        @media (max-width: 768px) {
            .hero-cards-grid { grid-template-columns: repeat(2, 1fr) !important; }
        }
        @media (max-width: 480px) {
            .hero-cards-grid { grid-template-columns: 1fr !important; }
        }

        /* ============================================================
           KEGIATAN SISWA — Grid & Hover
           ============================================================ */
        .kegiatan-grid .bcard:hover .icon-circle {
            transform: scale(1.12);
        }
        .kegiatan-grid .bcard:hover .icon-circle i {
            transform: rotate(10deg);
        }
        @media (max-width: 900px) {
            .kegiatan-grid {
                grid-template-columns: repeat(3, 1fr) !important;
            }
        }
        @media (max-width: 600px) {
            .kegiatan-grid {
                grid-template-columns: repeat(2, 1fr) !important;
            }
        }

        /* ============================================================
           PELATIHAN — Filter Buttons & Training Cards
           ============================================================ */
        .filter-btn {
            background: transparent;
            color: var(--text-secondary);
            border: 1.5px solid var(--border);
            padding: 8px 18px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition-fast);
        }
        .filter-btn:hover {
            border-color: var(--indigo-light);
            color: var(--indigo);
            background: var(--indigo-bg);
        }
        .filter-btn.active {
            background: var(--indigo-dark);
            color: #fff;
            border-color: var(--indigo-dark);
            box-shadow: 0 4px 12px rgba(30,27,75,0.15);
        }
        .btn-download {
            transition: var(--transition-fast);
        }
        .btn-download:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0,0,0,0.15);
            filter: brightness(1.05);
        }
        .training-card {
            transition: var(--transition-smooth);
        }
        .training-card.hidden {
            opacity: 0;
            transform: scale(0.92);
            pointer-events: none;
            position: absolute;
            width: 0; height: 0;
            overflow: hidden;
            padding: 0 !important;
            margin: 0 !important;
            border: none !important;
        }

        /* ============================================================
           NAV CTA BUTTON — Reusable small gold button for navbar
           ============================================================ */
        .nav-cta {
            padding: 10px 22px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            color: #1e1b4b;
            background: linear-gradient(135deg, #f59e0b, #fbbf24);
            display: inline-flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 4px 16px rgba(245,158,11,0.35);
            transition: var(--transition-fast);
        }
        .nav-cta:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(245,158,11,0.5);
            filter: brightness(1.05);
        }

        /* ============================================================
           FOOTER — Responsive & Social Hover
           ============================================================ */
        .footer-grid {
            display: grid;
            grid-template-columns: 1.5fr 1fr 1fr 1fr;
            gap: 48px;
        }
        .footer-social-btn {
            width: 40px; height: 40px;
            border-radius: 12px;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.08);
            display: flex; align-items: center; justify-content: center;
            color: #94a3b8;
            text-decoration: none;
            font-size: 16px;
            transition: var(--transition-fast);
        }
        .footer-social-btn:hover {
            background: rgba(251,191,36,0.15);
            border-color: rgba(251,191,36,0.3);
            color: #fbbf24;
            transform: translateY(-2px);
        }
        @media (max-width: 900px) {
            .footer-grid { grid-template-columns: 1fr; gap: 32px; }
        }

        /* ============================================================
           PROFIL YAYASAN — Responsive Internal Grid
           ============================================================ */
        @media (max-width: 768px) {
            .profil-grid { grid-template-columns: 1fr !important; }
        }
    </style>
</head>

<body>
    @include('landing.partials.navigation')
    @include('landing.partials.hero')
    @include('landing.partials.platform-overview')
    @include('landing.partials.statistik')
    @include('landing.partials.unit-sekolah')
    @include('landing.partials.program-keahlian')
    @include('landing.partials.prestasi')
    @include('landing.partials.ekosistem-digital')
    @include('landing.partials.pembdahub-features')
    @include('landing.partials.sambutan-ketua')
    @include('landing.partials.profil-yayasan')
    @include('landing.partials.kegiatan-siswa')
    @include('landing.partials.pkl-showcase')
    @include('landing.partials.berita')
    @include('landing.partials.alumni-showcase')
    @include('landing.partials.galeri')
    @include('landing.partials.pelatihan')
    @include('landing.partials.psb-cta')
    @include('landing.partials.footer')

    {{-- FLOATING AUDIO PLAYER (MARS YAYASAN) --}}
    <div id="mars-player" class="floating-audio">
        <audio id="mars-audio" loop preload="none">
            {{-- Menggunakan absolute path '/' agar selalu mengarah ke root public_html di server --}}
            <source src="/audio/mars-pembda.mp4" type="audio/mp4">
            Your browser does not support the audio element.
        </audio>
        <button id="mars-toggle" class="audio-toggle" title="Putar Mars Yayasan">
            <i class="fa-solid fa-music"></i>
            <span class="audio-text">Mars Pembda</span>
        </button>
    </div>

    <style>
        .floating-audio {
            position: fixed;
            bottom: 30px;
            left: 30px;
            z-index: 9999;
        }
        .audio-toggle {
            background: linear-gradient(135deg, var(--indigo), var(--indigo-dark));
            color: #fff;
            border: none;
            border-radius: 100px;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            box-shadow: 0 10px 25px rgba(30, 27, 75, 0.4);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-family: 'Inter', sans-serif;
            font-weight: 600;
            font-size: 14px;
        }
        .audio-toggle:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(30, 27, 75, 0.5);
            background: linear-gradient(135deg, var(--gold), var(--gold-bright));
            color: var(--indigo-dark);
        }
        .audio-toggle.playing {
            background: linear-gradient(135deg, var(--emerald), #34d399);
            color: #fff;
            animation: pulse-audio 2s infinite;
        }
        .audio-toggle.playing i {
            animation: spin-slow 4s linear infinite;
        }
        @keyframes spin-slow { 100% { transform: rotate(360deg); } }
        @keyframes pulse-audio {
            0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
            70% { box-shadow: 0 0 0 15px rgba(16, 185, 129, 0); }
            100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }
        @media (max-width: 768px) {
            .floating-audio { bottom: 20px; left: 20px; }
            .audio-text { display: none; }
            .audio-toggle { padding: 14px; border-radius: 50%; }
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toggle = document.getElementById('mars-toggle');
            const audio = document.getElementById('mars-audio');
            const icon = toggle.querySelector('i');
            const text = toggle.querySelector('.audio-text');
            let isPlaying = false;

            if(toggle && audio) {
                toggle.addEventListener('click', () => {
                    if(!isPlaying) {
                        // Tambahkan indikator loading
                        icon.className = 'fa-solid fa-spinner fa-spin';
                        text.textContent = 'Memuat...';
                        
                        audio.play().then(() => {
                            isPlaying = true;
                            toggle.classList.add('playing');
                            icon.className = 'fa-solid fa-compact-disc';
                            text.textContent = 'Sedang Memutar...';
                        }).catch(e => {
                            console.error('Gagal memutar audio. Pastikan file ada di public_html/audio/mars-pembda.mp4', e);
                            icon.className = 'fa-solid fa-triangle-exclamation';
                            text.textContent = 'Audio Tidak Ditemukan';
                            
                            // Reset icon after 3 seconds
                            setTimeout(() => {
                                icon.className = 'fa-solid fa-music';
                                text.textContent = 'Mars Pembda';
                            }, 3000);
                        });
                    } else {
                        audio.pause();
                        isPlaying = false;
                        toggle.classList.remove('playing');
                        icon.className = 'fa-solid fa-music';
                        text.textContent = 'Mars Pembda';
                    }
                });
            }
        });
    </script>
    {{-- END FLOATING AUDIO PLAYER --}}

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({ duration: 700, easing: 'ease-out-cubic', once: true, offset: 60 });

        // Navbar
        const navbar = document.getElementById('navbar');
        const onScroll = () => navbar.classList.toggle('scrolled', window.scrollY > 60);
        window.addEventListener('scroll', onScroll); onScroll();

        // Mobile menu
        const mBtn = document.getElementById('mobile-menu-btn');
        const mNav = document.getElementById('mobile-overlay');
        const mClose = document.getElementById('mobile-close');
        mBtn?.addEventListener('click', () => { mNav.classList.add('active'); document.body.style.overflow='hidden'; });
        mClose?.addEventListener('click', () => { mNav.classList.remove('active'); document.body.style.overflow=''; });
        mNav?.querySelectorAll('a').forEach(l => l.addEventListener('click', () => { mNav.classList.remove('active'); document.body.style.overflow=''; }));

        // Smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(a => {
            a.addEventListener('click', e => {
                e.preventDefault();
                const t = document.querySelector(a.getAttribute('href'));
                if(t) { window.scrollTo({ top: t.getBoundingClientRect().top + window.scrollY - 80, behavior:'smooth' }); }
            });
        });

        // Counters
        function runCounters() {
            document.querySelectorAll('[data-count]').forEach(el => {
                if(el.dataset.done) return;
                const r = el.getBoundingClientRect();
                if(r.top < window.innerHeight && r.bottom > 0) {
                    el.dataset.done = '1';
                    const target = +el.dataset.count, suffix = el.dataset.suffix||'';
                    const start = Date.now(), dur = 1800;
                    (function upd() {
                        const p = Math.min((Date.now()-start)/dur, 1);
                        const ease = 1 - Math.pow(1-p, 3);
                        el.textContent = Math.round(target*ease).toLocaleString('id-ID') + suffix;
                        if(p<1) requestAnimationFrame(upd);
                    })();
                }
            });
        }
        window.addEventListener('scroll', runCounters); runCounters();

        // Typewriter Effect
        document.addEventListener('DOMContentLoaded', () => {
            const txtElement = document.querySelector('.typewriter-text');
            if (!txtElement) return;
            const words = [
                "RFID Attendance Multi-Input",
                "LMS (Learning Management System)",
                "CBT (Computer Based Test)",
                "Forum Akademik & Diskusi",
                "PKL & Alumni Portal",
                "Sistem Reputasi Siswa & Guru"
            ];
            let wordIndex = 0;
            let txt = '';
            let isDeleting = false;

            function type() {
                const current = wordIndex % words.length;
                const fullTxt = words[current];

                if (isDeleting) {
                    txt = fullTxt.substring(0, txt.length - 1);
                } else {
                    txt = fullTxt.substring(0, txt.length + 1);
                }

                txtElement.textContent = txt;

                let typeSpeed = 80;
                if (isDeleting) {
                    typeSpeed /= 2;
                }

                if (!isDeleting && txt === fullTxt) {
                    typeSpeed = 2000; // Wait at full word
                    isDeleting = true;
                } else if (isDeleting && txt === '') {
                    isDeleting = false;
                    wordIndex++;
                    typeSpeed = 500; // Wait before typing next word
                }

                setTimeout(type, typeSpeed);
            }

            type();
        });

        // Pelatihan filter
        function filterTraining(category, btnElement) {
            document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
            btnElement.classList.add('active');
            
            const cards = document.querySelectorAll('.training-card');
            cards.forEach(card => {
                if (category === 'all' || card.getAttribute('data-category') === category) {
                    card.classList.remove('hidden');
                    setTimeout(() => {
                        card.style.opacity = '1';
                        card.style.transform = 'scale(1)';
                    }, 10);
                } else {
                    card.style.opacity = '0';
                    card.style.transform = 'scale(0.92)';
                    setTimeout(() => {
                        card.classList.add('hidden');
                    }, 300);
                }
            });
        }
    </script>
</body>
</html>