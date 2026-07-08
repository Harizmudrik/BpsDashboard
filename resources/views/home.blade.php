<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Data BPS — Dashboard Indikator Utama</title>
    <meta name="description" content="Portal Visualisasi Indikator Sosial Ekonomi Indonesia berdasarkan Data BPS Resmi.">
    <link rel="icon" type="image/svg+xml" href="{{ asset('LOGOBPS.svg') }}">
    <link rel="shortcut icon" type="image/svg+xml" href="{{ asset('LOGOBPS.svg') }}">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Load Leaflet Map styles and scripts -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #f8fafc;
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }
        h1, h2, h3, h4 {
            font-family: 'Outfit', sans-serif;
        }

        /* ===== Sticky Navbar ===== */
        .top-navbar {
            background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%);
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 16px rgba(15, 23, 42, 0.12);
        }
        .nav-inner {
            max-width: 1280px;
            margin: 0 auto;
            padding: 12px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .nav-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: #fff;
        }
        .nav-brand-text h2 {
            font-size: 15px;
            font-weight: 700;
            line-height: 1.2;
        }
        .nav-brand-text p {
            font-size: 10px;
            color: #93c5fd;
        }
        .nav-links {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .nav-link {
            color: #cbd5e1;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            padding: 8px 16px;
            border-radius: 10px;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .nav-link:hover {
            background: rgba(255,255,255,0.1);
            color: #fff;
        }
        .nav-link.active {
            background: rgba(37, 99, 235, 0.3);
            color: #fff;
            border: 1px solid rgba(59, 130, 246, 0.4);
        }

        /* ===== Breadcrumb ===== */
        .breadcrumb-bar {
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            padding: 10px 24px;
        }
        .breadcrumb-inner {
            max-width: 1280px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            color: #64748b;
        }
        .breadcrumb-inner a {
            color: #2563eb;
            text-decoration: none;
            font-weight: 600;
        }
        .breadcrumb-inner a:hover {
            text-decoration: underline;
        }
        .breadcrumb-sep {
            color: #94a3b8;
        }
        .breadcrumb-current {
            color: #0f172a;
            font-weight: 600;
        }

        /* ===== Interaction Hint Tooltip ===== */
        .hint-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: linear-gradient(135deg, #eff6ff, #dbeafe);
            border: 1px solid #bfdbfe;
            color: #1d4ed8;
            font-size: 11px;
            font-weight: 600;
            padding: 5px 12px;
            border-radius: 100px;
            animation: hintPulse 3s ease-in-out infinite;
        }
        @keyframes hintPulse {
            0%, 100% { opacity: 0.85; }
            50% { opacity: 1; box-shadow: 0 0 8px rgba(37, 99, 235, 0.2); }
        }
        .clickable-indicator {
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .clickable-indicator:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.12);
        }

        /* Top Header Grid / Header Style */
        .hero-banner {
            background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 50%, #0284c7 100%);
            position: relative;
            color: #fff;
            padding: 80px 24px 100px 24px;
            text-align: center;
            overflow: hidden;
        }
        .hero-banner::before {
            content: '';
            position: absolute;
            top: -20%;
            left: -10%;
            width: 50%;
            height: 100%;
            background: radial-gradient(circle, rgba(2, 132, 199, 0.25) 0%, transparent 70%);
            filter: blur(40px);
        }
        .hero-container {
            max-width: 900px;
            margin: 0 auto;
            position: relative;
            z-index: 10;
        }
        .bps-badge {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 6px 16px;
            border-radius: 100px;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 0.05em;
            color: #38bdf8;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 24px;
        }
        .bps-badge span {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #38bdf8;
            box-shadow: 0 0 10px #38bdf8;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(0.95); opacity: 0.5; }
            50% { transform: scale(1.1); opacity: 1; }
            100% { transform: scale(0.95); opacity: 0.5; }
        }

        .hero-title {
            font-size: 42px;
            font-weight: 800;
            line-height: 1.15;
            margin-bottom: 16px;
            letter-spacing: -0.02em;
        }
        .hero-subtitle {
            font-size: 16px;
            color: #93c5fd;
            margin-bottom: 32px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
            line-height: 1.6;
        }

        /* Search Bar */
        .search-container {
            max-width: 600px;
            margin: 0 auto;
            background: #fff;
            padding: 6px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
            display: flex;
            align-items: center;
            position: relative;
            z-index: 100;
        }
        .search-input {
            flex: 1;
            border: none;
            padding: 12px 16px;
            font-size: 15px;
            outline: none;
            border-radius: 12px;
            color: #1e293b;
        }
        .search-btn {
            background: #2563eb;
            color: #fff;
            border: none;
            padding: 12px 24px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        .search-btn:hover {
            background: #1d4ed8;
        }

        /* Autocomplete Suggestions */
        .suggestions-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            margin-top: 8px;
            max-height: 250px;
            overflow-y: auto;
            z-index: 1001;
            text-align: left;
            border: 1px solid rgba(226, 232, 240, 0.8);
        }
        .suggestion-item {
            display: flex;
            padding: 12px 16px;
            text-decoration: none;
            color: inherit;
            transition: background 0.2s;
            border-bottom: 1px solid #f1f5f9;
        }
        .suggestion-item:last-child {
            border-bottom: none;
        }
        .suggestion-item:hover {
            background: rgba(37, 99, 235, 0.05);
        }
        .suggestion-category {
            font-size: 10px;
            font-weight: 700;
            background: #dbeafe;
            color: #1e40af;
            padding: 2px 8px;
            border-radius: 100px;
            text-transform: uppercase;
        }

        /* Main Content Container */
        .main-content {
            max-width: 1280px;
            margin: -40px auto 40px auto;
            padding: 0 24px;
            position: relative;
            z-index: 10;
            width: 100%;
        }

        /* Slider Wrapper */
        .slider-section {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.7);
            border-radius: 24px;
            padding: 32px;
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.05);
            position: relative;
            margin-bottom: 32px;
        }
        .slider-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
        }
        .slider-title {
            font-size: 22px;
            font-weight: 700;
            color: #0f172a;
        }
        .slider-nav {
            display: flex;
            gap: 8px;
        }
        .nav-arrow {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #f1f5f9;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: #475569;
            transition: all 0.2s;
        }
        .nav-arrow:hover:not(:disabled) {
            background: #e2e8f0;
            color: #0f172a;
        }
        .nav-arrow:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }

        /* Carousel Area */
        .carousel-container {
            overflow: hidden;
            width: 100%;
        }
        .carousel-track {
            display: flex;
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            gap: 20px;
        }

        /* Premium Glassmorphic Cards */
        .metric-card {
            flex: 0 0 calc(33.333% - 14px);
            background: rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.6);
            border-radius: 20px;
            padding: 24px;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }
        .metric-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: transparent;
            transition: background 0.3s;
        }
        .metric-card:hover {
            transform: translateY(-8px) scale(1.02);
            background: rgba(255, 255, 255, 0.9);
            border-color: rgba(37, 99, 235, 0.35);
            box-shadow: 0 20px 40px -15px rgba(37, 99, 235, 0.15);
        }
        .metric-card:hover::before {
            background: linear-gradient(90deg, #3b82f6, #06b6d4);
        }

        .metric-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .metric-icon-wrap {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: rgba(37, 99, 235, 0.08);
            color: #2563eb;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            transition: all 0.3s;
        }
        .metric-card:hover .metric-icon-wrap {
            background: #2563eb;
            color: #fff;
        }

        .trend-pill {
            padding: 4px 10px;
            border-radius: 100px;
            font-size: 12px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .trend-pill.down {
            background: #dcfce7;
            color: #15803d;
        }
        .trend-pill.up {
            background: #fee2e2;
            color: #b91c1c;
        }
        .trend-pill.neutral {
            background: #f1f5f9;
            color: #64748b;
        }

        .metric-label {
            font-size: 14px;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 8px;
        }
        .metric-value-wrap {
            display: flex;
            align-items: baseline;
            gap: 4px;
        }
        .metric-value {
            font-size: 34px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.02em;
        }
        .metric-unit {
            font-size: 18px;
            font-weight: 600;
            color: #64748b;
        }
        .metric-period {
            font-size: 11px;
            color: #94a3b8;
            margin-top: 12px;
            font-weight: 500;
        }

        /* Dots indicator */
        .dots-container {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-top: 24px;
        }
        .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #cbd5e1;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
        }
        .dot.active {
            background: #2563eb;
            width: 24px;
            border-radius: 100px;
        }

        /* Portal Info */
        .portal-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-top: 32px;
        }
        .info-card {
            background: #fff;
            border-radius: 20px;
            padding: 32px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.02);
            border: 1px solid #f1f5f9;
        }
        .info-title {
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .info-text {
            font-size: 14px;
            color: #475569;
            line-height: 1.7;
            margin-bottom: 16px;
        }
        .info-list {
            list-style: none;
            font-size: 14px;
            color: #475569;
        }
        .info-list li {
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .info-list li::before {
            content: "✓";
            color: #10b981;
            font-weight: bold;
        }

        .select-filter {
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            padding: 8px 12px;
            font-size: 13px;
            font-family: inherit;
            outline: none;
            color: #1e293b;
            background: #fff;
            cursor: pointer;
            transition: all 0.2s;
        }
        .select-filter:focus {
            border-color: #2563eb;
        }

        /* Footer */
        .footer {
            margin-top: auto;
            background: #0f172a;
            color: #94a3b8;
            padding: 40px 24px;
            border-top: 1px solid #1e293b;
        }
        .footer-inner {
            max-width: 1280px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }
        .footer-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #fff;
            font-weight: 700;
            font-size: 16px;
        }

        /* ============================================
           RESPONSIVE DESIGN — Desktop / Tablet / HP
           ============================================ */

        /* TABLET (768px - 1024px) */
        @media(max-width: 1024px) {
            .metric-card {
                flex: 0 0 calc(50% - 10px);
            }
            .nav-inner {
                padding: 10px 16px;
            }
        }

        /* SMALL TABLET / LARGE PHONE (641px - 768px) */
        @media(max-width: 768px) {
            .hero-banner {
                padding: 50px 16px 70px 16px;
            }
            .hero-title {
                font-size: 28px;
            }
            .hero-subtitle {
                font-size: 14px;
            }
            .main-content {
                padding: 0 12px;
                margin-top: -30px;
            }
            .slider-section {
                padding: 20px 16px;
                border-radius: 16px;
            }
            .slider-title {
                font-size: 18px;
            }
            .metric-card {
                flex: 0 0 calc(50% - 10px);
                padding: 18px;
                border-radius: 14px;
            }
            .metric-value {
                font-size: 26px;
            }
            .portal-info {
                grid-template-columns: 1fr;
            }
            /* Map section: stack vertically */
            .map-grid-responsive {
                grid-template-columns: 1fr !important;
            }
        }

        /* MOBILE PHONE (max 640px) */
        @media(max-width: 640px) {
            .nav-inner {
                padding: 8px 12px;
                flex-direction: column;
                align-items: flex-start;
                gap: 4px;
            }
            .nav-brand-text h2 {
                font-size: 13px;
            }
            .nav-brand-text p {
                font-size: 9px;
            }
            .hero-banner {
                padding: 36px 14px 56px 14px;
            }
            .hero-title {
                font-size: 24px;
                line-height: 1.2;
            }
            .hero-subtitle {
                font-size: 13px;
                margin-bottom: 20px;
            }
            .bps-badge {
                font-size: 11px;
                padding: 5px 12px;
                margin-bottom: 16px;
            }
            .search-container {
                flex-direction: column;
                padding: 8px;
                border-radius: 14px;
            }
            .search-input {
                width: 100%;
                font-size: 14px;
                padding: 10px 12px;
            }
            .search-btn {
                width: 100%;
                padding: 10px;
                border-radius: 10px;
                font-size: 13px;
            }
            .main-content {
                padding: 0 10px;
                margin-top: -24px;
            }
            .slider-section {
                padding: 16px 12px;
                border-radius: 14px;
            }
            .slider-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }
            .slider-title {
                font-size: 16px;
            }
            .slider-nav {
                align-self: flex-end;
            }
            .nav-arrow {
                width: 34px;
                height: 34px;
            }
            .metric-card {
                flex: 0 0 100%;
                padding: 16px;
                border-radius: 14px;
            }
            .metric-card-header {
                margin-bottom: 14px;
            }
            .metric-icon-wrap {
                width: 36px;
                height: 36px;
                border-radius: 10px;
                font-size: 16px;
            }
            .metric-label {
                font-size: 12px;
            }
            .metric-value {
                font-size: 28px;
            }
            .metric-unit {
                font-size: 14px;
            }
            .metric-period {
                font-size: 10px;
            }
            .portal-info {
                grid-template-columns: 1fr;
                gap: 16px;
            }
            .info-card {
                padding: 20px;
                border-radius: 14px;
            }
            .info-title {
                font-size: 15px;
            }
            .info-text {
                font-size: 13px;
            }
            .hint-badge {
                font-size: 10px;
                padding: 4px 10px;
            }
            .footer-inner {
                flex-direction: column;
                text-align: center;
                gap: 12px;
            }
            .footer-logo {
                font-size: 14px;
            }
        }

        /* VERY SMALL PHONE (max 380px) */
        @media(max-width: 380px) {
            .hero-title {
                font-size: 20px;
            }
            .metric-value {
                font-size: 24px;
            }
            .slider-title {
                font-size: 14px;
            }
        }
    </style>
</head>
<body x-data="carouselSlider()">

    <!-- Sticky Navigation Bar with integrated Breadcrumb -->
    <nav class="top-navbar">
        <div class="nav-inner">
            <a href="{{ route('home') }}" class="nav-brand">
                <img src="{{ asset('LOGOBPS.svg') }}" alt="BPS logo" style="height:32px; filter: brightness(0) invert(1);">
                <div class="nav-brand-text">
                    <h2>Portal Data BPS</h2>
                    <p>Integrasi Indikator Makro Indonesia</p>
                </div>
            </a>
        </div>
        <!-- Breadcrumb (integrated) -->
        <div style="max-width:1280px; margin:0 auto; padding:0 24px 10px 24px; display:flex; align-items:center; gap:8px; font-size:12px; color:rgba(148,163,184,0.8);">
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            <span style="color:#93c5fd; font-weight:600;">Beranda</span>
            <span style="color:rgba(148,163,184,0.5);">›</span>
            <span style="color:#fff; font-weight:600;">Dashboard Indikator Utama</span>
        </div>
    </nav>

    <!-- Hero Banner -->
    <section class="hero-banner">
        <div class="hero-container">
            <div class="bps-badge">
                <span></span> PORTAL INTEGRASI DATA BPS
            </div>
            <h1 class="hero-title">Indikator Sosial Ekonomi Indonesia</h1>
            <p class="hero-subtitle">Visualisasi data interaktif untuk mempermudah pemahaman data makro Badan Pusat Statistik bagi akademisi, peneliti, dan masyarakat umum.</p>
            
            <!-- Search Bar with Autocomplete Dropdown -->
            <div style="position: relative; max-width: 600px; margin: 0 auto; z-index: 100;">
                <div class="search-container">
                    <input type="text" 
                           placeholder="Cari data (misal: pengangguran, kemiskinan, inflasi, PDB)..." 
                           class="search-input" 
                           x-model="searchQuery" 
                           @input="fetchSuggestions()"
                           @keydown.escape="suggestions = []"
                           @click.outside="suggestions = []"
                           @keydown.enter="searchMetric()">
                    <button class="search-btn" @click="searchMetric()">Cari Data</button>
                </div>
                
                <!-- Autocomplete suggestions dropdown -->
                <div class="suggestions-dropdown" x-show="suggestions.length > 0" x-cloak>
                    <template x-for="item in suggestions" :key="Math.random()">
                        <a :href="item.url" class="suggestion-item">
                            <div style="display:flex; justify-content:space-between; align-items:center; width:100%;">
                                <div style="text-align:left;">
                                    <span style="font-weight: 600; font-size:13px; color:#1e293b;" x-text="item.label"></span>
                                </div>
                                <span class="suggestion-category" x-text="item.category"></span>
                            </div>
                        </a>
                    </template>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content Area -->
    <main class="main-content">

        <!-- Mini Map Section -->
        <section class="panel glass-panel" style="margin-bottom: 32px; background: #fff; padding: 28px; border-radius: 24px; box-shadow: 0 10px 30px rgba(15, 23, 42, 0.04); border: 1px solid #f1f5f9;">
            <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom: 24px; flex-wrap:wrap; gap:16px;">
                <div>
                    <h2 class="slider-title" style="display:flex; align-items:center; gap:8px;">
                        <svg width="24" height="24" fill="none" stroke="#2563eb" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                        Peta Geospasial Interaktif
                    </h2>
                    <p style="font-size:13px; color:#64748b; margin-top:2px;">Gambaran komparatif indikator utama antar provinsi secara visual</p>
                    <div class="hint-badge" style="margin-top:8px;">
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"/></svg>
                        Klik wilayah pada peta untuk masuk ke halaman data detail provinsi tersebut
                    </div>
                </div>
            </div>

            <div class="map-grid-responsive" style="display:grid; grid-template-columns: 1.8fr 1fr; gap: 24px; min-height: 400px;">
                <div style="border-radius:16px; overflow:hidden; border: 1px solid #cbd5e1; height:400px; position:relative;">
                    <div id="miniMap" style="width:100%; height:100%; z-index:1;"></div>
                </div>
                <div style="background:#f8fafc; border: 1px solid #e2e8f0; border-radius:16px; padding:20px; display:flex; flex-direction:column; justify-content:center;">
                    <template x-if="hoveredProvince">
                        <div>
                            <div style="display:flex; align-items:center; gap:8px; margin-bottom:16px; padding-bottom:12px; border-bottom:2px solid #e2e8f0;">
                                <svg width="20" height="20" fill="none" stroke="#2563eb" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <h3 style="font-size:18px; font-weight:800; color:#0f172a;" x-text="hoveredProvince.name"></h3>
                            </div>
                            
                            <div style="display:flex; flex-direction:column; gap:12px;">
                                <!-- TPT -->
                                <div style="background:#fff; padding:10px 14px; border-radius:10px; border:1px solid #f1f5f9; cursor:pointer;" @click="window.location.href='{{ url('/metric/tpt') }}?provinsi=' + hoveredProvince.code">
                                    <div style="display:flex; justify-content:space-between; font-size:11px; font-weight:700; color:#64748b;">
                                        <span>PENGANGGURAN (TPT)</span>
                                        <span style="color:#0f172a;" x-text="hoveredProvince.tpt ? hoveredProvince.tpt.toFixed(2) + '%' : '-'"></span>
                                    </div>
                                    <div style="height:6px; background:#f1f5f9; border-radius:100px; margin-top:8px; overflow:hidden;">
                                        <div :style="'height:100%; background:#3b82f6; width:' + Math.min(100, (hoveredProvince.tpt || 0) * 10) + '%'"></div>
                                    </div>
                                </div>

                                <!-- GINI -->
                                <div style="background:#fff; padding:10px 14px; border-radius:10px; border:1px solid #f1f5f9; cursor:pointer;" @click="window.location.href='{{ url('/metric/gini_ratio') }}?provinsi=' + hoveredProvince.code">
                                    <div style="display:flex; justify-content:space-between; font-size:11px; font-weight:700; color:#64748b;">
                                        <span>KETIMPANGAN (GINI)</span>
                                        <span style="color:#0f172a;" x-text="hoveredProvince.gini_ratio ? hoveredProvince.gini_ratio.toFixed(3) : '-'"></span>
                                    </div>
                                    <div style="height:6px; background:#f1f5f9; border-radius:100px; margin-top:8px; overflow:hidden;">
                                        <div :style="'height:100%; background:#10b981; width:' + Math.min(100, (hoveredProvince.gini_ratio || 0) * 200) + '%'"></div>
                                    </div>
                                </div>

                                <!-- POVERTY -->
                                <div style="background:#fff; padding:10px 14px; border-radius:10px; border:1px solid #f1f5f9; cursor:pointer;" @click="window.location.href='{{ url('/metric/kemiskinan') }}?provinsi=' + hoveredProvince.code">
                                    <div style="display:flex; justify-content:space-between; font-size:11px; font-weight:700; color:#64748b;">
                                        <span>KEMISKINAN (P0)</span>
                                        <span style="color:#0f172a;" x-text="hoveredProvince.kemiskinan ? hoveredProvince.kemiskinan.toFixed(2) + '%' : '-'"></span>
                                    </div>
                                    <div style="height:6px; background:#f1f5f9; border-radius:100px; margin-top:8px; overflow:hidden;">
                                        <div :style="'height:100%; background:#f59e0b; width:' + Math.min(100, (hoveredProvince.kemiskinan || 0) * 3) + '%'"></div>
                                    </div>
                                </div>

                                <!-- YoY INFLATION -->
                                <div style="background:#fff; padding:10px 14px; border-radius:10px; border:1px solid #f1f5f9; cursor:pointer;" @click="window.location.href='{{ url('/metric/inflasi_tahunan') }}?provinsi=' + hoveredProvince.code">
                                    <div style="display:flex; justify-content:space-between; font-size:11px; font-weight:700; color:#64748b;">
                                        <span>INFLASI TAHUNAN</span>
                                        <span style="color:#0f172a;" x-text="hoveredProvince.inflasi_tahunan ? hoveredProvince.inflasi_tahunan.toFixed(2) + '%' : '-'"></span>
                                    </div>
                                    <div style="height:6px; background:#f1f5f9; border-radius:100px; margin-top:8px; overflow:hidden;">
                                        <div :style="'height:100%; background:#ec4899; width:' + Math.min(100, (hoveredProvince.inflasi_tahunan || 0) * 10) + '%'"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <p style="font-size:10px; color:#94a3b8; margin-top:16px; text-align:center; font-style:italic;">*Klik wilayah di peta untuk meninjau data historis lengkap.</p>
                        </div>
                    </template>
                    <template x-if="!hoveredProvince">
                        <div style="text-align:center; color:#94a3b8; padding:20px;">
                            <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="margin:0 auto 12px auto; display:block; opacity:0.6;"><path stroke-linecap="round" stroke-linejoin="round" d="M15.042 21.672 13.684 16.6m0 0-2.51 2.225.569-9.47 5.227 7.917-3.286-.672ZM12 2.25V4.5m5.303.197-1.591 1.591M21 12h-2.25m-.197 5.303-1.591-1.591M12 21.75V19.5m-5.303-.197 1.591-1.591M3 12h2.25m.197-5.303 1.591 1.591"/></svg>
                            <p style="font-size:13px; font-weight:600; line-height:1.5;">Arahkan kursor ke wilayah provinsi pada peta untuk melihat statistik lengkap.</p>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Map Legend -->
            <div style="display:flex; align-items:center; gap:16px; margin-top:20px; padding:14px 20px; background:#f8fafc; border-radius:12px; border:1px solid #e2e8f0; flex-wrap:wrap;">
                <span style="font-size:12px; font-weight:700; color:#475569; margin-right:4px;">LEGENDA PETA:</span>
                <template x-if="activeIndicator === 'tpt'">
                    <div style="display:flex; gap:14px; flex-wrap:wrap; align-items:center;">
                        <div style="display:flex; align-items:center; gap:5px;"><span style="width:16px;height:16px;border-radius:4px;background:#10b981;display:inline-block;"></span><span style="font-size:11px;color:#334155;">Rendah / Baik (&lt; 3%)</span></div>
                        <div style="display:flex; align-items:center; gap:5px;"><span style="width:16px;height:16px;border-radius:4px;background:#fbbf24;display:inline-block;"></span><span style="font-size:11px;color:#334155;">Sedang (3% – 5%)</span></div>
                        <div style="display:flex; align-items:center; gap:5px;"><span style="width:16px;height:16px;border-radius:4px;background:#f97316;display:inline-block;"></span><span style="font-size:11px;color:#334155;">Tinggi / Waspada (5% – 7%)</span></div>
                        <div style="display:flex; align-items:center; gap:5px;"><span style="width:16px;height:16px;border-radius:4px;background:#b91c1c;display:inline-block;"></span><span style="font-size:11px;color:#334155;">Sangat Tinggi / Kritis (≥ 7%)</span></div>
                    </div>
                </template>
                <template x-if="activeIndicator === 'gini_ratio'">
                    <div style="display:flex; gap:14px; flex-wrap:wrap; align-items:center;">
                        <div style="display:flex; align-items:center; gap:5px;"><span style="width:16px;height:16px;border-radius:4px;background:#10b981;display:inline-block;"></span><span style="font-size:11px;color:#334155;">Merata (&lt; 0.32)</span></div>
                        <div style="display:flex; align-items:center; gap:5px;"><span style="width:16px;height:16px;border-radius:4px;background:#fbbf24;display:inline-block;"></span><span style="font-size:11px;color:#334155;">Sedang (0.32 – 0.37)</span></div>
                        <div style="display:flex; align-items:center; gap:5px;"><span style="width:16px;height:16px;border-radius:4px;background:#f97316;display:inline-block;"></span><span style="font-size:11px;color:#334155;">Timpang (0.37 – 0.40)</span></div>
                        <div style="display:flex; align-items:center; gap:5px;"><span style="width:16px;height:16px;border-radius:4px;background:#b91c1c;display:inline-block;"></span><span style="font-size:11px;color:#334155;">Sangat Timpang (≥ 0.40)</span></div>
                    </div>
                </template>
                <template x-if="activeIndicator === 'kemiskinan'">
                    <div style="display:flex; gap:14px; flex-wrap:wrap; align-items:center;">
                        <div style="display:flex; align-items:center; gap:5px;"><span style="width:16px;height:16px;border-radius:4px;background:#10b981;display:inline-block;"></span><span style="font-size:11px;color:#334155;">Rendah (&lt; 7%)</span></div>
                        <div style="display:flex; align-items:center; gap:5px;"><span style="width:16px;height:16px;border-radius:4px;background:#fbbf24;display:inline-block;"></span><span style="font-size:11px;color:#334155;">Sedang (7% – 12%)</span></div>
                        <div style="display:flex; align-items:center; gap:5px;"><span style="width:16px;height:16px;border-radius:4px;background:#f97316;display:inline-block;"></span><span style="font-size:11px;color:#334155;">Tinggi (12% – 18%)</span></div>
                        <div style="display:flex; align-items:center; gap:5px;"><span style="width:16px;height:16px;border-radius:4px;background:#b91c1c;display:inline-block;"></span><span style="font-size:11px;color:#334155;">Sangat Tinggi (≥ 18%)</span></div>
                    </div>
                </template>
                <template x-if="activeIndicator === 'inflasi_tahunan'">
                    <div style="display:flex; gap:14px; flex-wrap:wrap; align-items:center;">
                        <div style="display:flex; align-items:center; gap:5px;"><span style="width:16px;height:16px;border-radius:4px;background:#10b981;display:inline-block;"></span><span style="font-size:11px;color:#334155;">Rendah (&lt; 1.5%)</span></div>
                        <div style="display:flex; align-items:center; gap:5px;"><span style="width:16px;height:16px;border-radius:4px;background:#fbbf24;display:inline-block;"></span><span style="font-size:11px;color:#334155;">Terkendali (1.5% – 3%)</span></div>
                        <div style="display:flex; align-items:center; gap:5px;"><span style="width:16px;height:16px;border-radius:4px;background:#f97316;display:inline-block;"></span><span style="font-size:11px;color:#334155;">Tinggi (3% – 4.5%)</span></div>
                        <div style="display:flex; align-items:center; gap:5px;"><span style="width:16px;height:16px;border-radius:4px;background:#b91c1c;display:inline-block;"></span><span style="font-size:11px;color:#334155;">Sangat Tinggi (≥ 4.5%)</span></div>
                    </div>
                </template>
                <div style="display:flex; align-items:center; gap:5px; margin-left:auto;"><span style="width:16px;height:16px;border-radius:4px;background:#f1f5f9;display:inline-block;border:1px solid #e2e8f0;"></span><span style="font-size:11px;color:#94a3b8;">Tidak ada data</span></div>
            </div>
        </section>
        
        <!-- Slider Section -->
        <section class="slider-section">
            <div class="slider-header">
                <div>
                    <h2 class="slider-title">Indikator Utama Terbaru</h2>
                    <p style="font-size:12px; color:#64748b; margin-top:2px;">Geser ke kanan/kiri untuk melihat metrik lainnya</p>
                    <div class="hint-badge" style="margin-top:6px;">
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"/></svg>
                        Klik kartu indikator untuk melihat detail data, grafik, dan tabel lengkap
                    </div>
                </div>
                <div class="slider-nav">
                    <button class="nav-arrow" @click="prev()" :disabled="currentIndex === 0" aria-label="Sebelumnya">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <button class="nav-arrow" @click="next()" :disabled="currentIndex >= maxIndex" aria-label="Berikutnya">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>

            <!-- Carousel track -->
            <div class="carousel-container">
                <div class="carousel-track" :style="'transform: translateX(-' + (currentIndex * cardOffset) + 'px)'">
                    @foreach($cards as $c)
                        <a href="{{ route('metric.detail', $c['key']) }}" class="metric-card" data-label="{{ strtolower($c['label']) }}">
                            <div>
                                <div class="metric-card-header">
                                    <div class="metric-icon-wrap">
                                        <!-- SVG icons for each metric -->
                                        @if($c['icon'] === 'briefcase')
                                            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                        @elseif($c['icon'] === 'scale')
                                            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
                                        @elseif($c['icon'] === 'home')
                                            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                        @elseif($c['icon'] === 'trending-up')
                                            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                                        @elseif($c['icon'] === 'activity')
                                            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 7.89M9 11l3 3L22 4"/></svg>
                                        @elseif($c['icon'] === 'pie-chart')
                                            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/><path stroke-linecap="round" stroke-linejoin="round" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/></svg>
                                        @else
                                            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                        @endif
                                    </div>
                                    
                                    <!-- Trend status logic -->
                                    @if($c['trend'] !== null)
                                        @php
                                            $isPositiveMetric = in_array($c['key'], ['pdb_growth']);
                                            $trendClass = '';
                                            $trendLabel = '';
                                            
                                            if ($c['trend'] == 0) {
                                                $trendClass = 'neutral';
                                                $trendLabel = '0.00';
                                            } elseif ($c['trend'] < 0) {
                                                $trendClass = $isPositiveMetric ? 'up' : 'down';
                                                $trendLabel = '▼ ' . abs($c['trend']);
                                            } else {
                                                $trendClass = $isPositiveMetric ? 'down' : 'up';
                                                $trendLabel = '▲ +' . $c['trend'];
                                            }
                                        @endphp
                                        <span class="trend-pill {{ $trendClass }}">
                                            {{ $trendLabel }}
                                        </span>
                                    @else
                                        <span class="trend-pill neutral">
                                            -
                                        </span>
                                    @endif
                                </div>
                                <div class="metric-label">{{ $c['label'] }}</div>
                                <div class="metric-value-wrap">
                                    <span class="metric-value">{{ $c['value'] }}</span>
                                    @if($c['unit'])
                                        <span class="metric-unit">{{ $c['unit'] }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="metric-period">Periode: {{ $c['periode'] }} {{ $c['tahun'] }}</div>
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Dots indicator -->
            <div class="dots-container">
                <template x-for="i in Array.from({ length: maxIndex + 1 })" :key="Math.random()">
                    <button class="dot" :class="currentIndex === arguments[1] ? 'active' : ''" @click="currentIndex = arguments[1]"></button>
                </template>
            </div>
        </section>

        <!-- Information Cards -->
        <section class="portal-info">
            <div class="info-card">
                <h3 class="info-title">
                    <svg width="24" height="24" fill="none" stroke="#2563eb" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Misi Portal Integrasi Data
                </h3>
                <p class="info-text">Tugas akhir ini bertujuan untuk merangkum dan menyajikan berbagai indikator makroekonomi dan sosial BPS ke dalam satu wadah visual terpadu. Hal ini dilakukan demi mengatasi beberapa poin kendala berikut:</p>
                <ul class="info-list">
                    <li>Menjelaskan secara eksplisit arti/meaning dari angka statistik makro.</li>
                    <li>Menyederhanakan antarmuka visual agar pembaca awam dapat menarik kesimpulan dengan cepat.</li>
                    <li>Menggunakan representasi geospasial (peta wilayah) untuk membandingkan indikator antar provinsi.</li>
                </ul>
            </div>
            <div class="info-card">
                <h3 class="info-title">
                    <svg width="24" height="24" fill="none" stroke="#2563eb" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Update & Validasi Data
                </h3>
                <p class="info-text">Seluruh data yang disajikan di portal ini diintegrasikan langsung dari repositori data mentah BPS API WebService yang dicakup mulai tahun 2010 hingga update berkala tahun 2026. Anda dapat mengeklik masing-masing kartu indikator di atas untuk melihat detail tren tahunan, diagram batang sektoral, persebaran spasial, serta mengunduh dataset lengkap dalam format CSV.</p>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-inner">
            <div class="footer-logo">
                <img src="{{ asset('LOGOBPS.svg') }}" alt="BPS logo" style="height:32px; filter: brightness(0) invert(1);">
                Portal Integrasi Indikator BPS
            </div>
            <div style="font-size:12px;">
                © 2026 Tugas Akhir Mahasiswa - Universitas/Studi Kasus BPS. All Rights Reserved.
            </div>
        </div>
    </footer>

    <!-- Script for handling Carousel and Map -->
    <script>
        // Province matcher helper dictionary
        function getProvinceCodeByName(provName) {
            if (!provName) return null;
            const norm = provName.toUpperCase()
                .replace("PROVINSI ", "")
                .replace("DKI ", "")
                .replace("DI ", "")
                .replace("KEP. ", "KEPULAUAN ")
                .replace("YOGYAKARTA", "DI YOGYAKARTA")
                .trim();
            
            const mapping = {
                "1100": "ACEH",
                "1200": "SUMATERA UTARA",
                "1300": "SUMATERA BARAT",
                "1400": "RIAU",
                "1500": "JAMBI",
                "1600": "SUMATERA SELATAN",
                "1700": "BENGKULU",
                "1800": "LAMPUNG",
                "1900": "KEPULAUAN BANGKA BELITUNG",
                "2100": "KEPULAUAN RIAU",
                "3100": "DKI JAKARTA",
                "3200": "JAWA BARAT",
                "3300": "JAWA TENGAH",
                "3400": "DI YOGYAKARTA",
                "3500": "JAWA TIMUR",
                "3600": "BANTEN",
                "5100": "BALI",
                "5200": "NUSA TENGGARA BARAT",
                "5300": "NUSA TENGGARA TIMUR",
                "6100": "KALIMANTAN BARAT",
                "6200": "KALIMANTAN TENGAH",
                "6300": "KALIMANTAN SELATAN",
                "6400": "KALIMANTAN TIMUR",
                "6500": "KALIMANTAN UTARA",
                "7100": "SULAWESI UTARA",
                "7200": "SULAWESI TENGAH",
                "7300": "SULAWESI SELATAN",
                "7400": "SULAWESI TENGGARA",
                "7500": "GORONTALO",
                "7600": "SULAWESI BARAT",
                "8100": "MALUKU",
                "8200": "MALUKU UTARA",
                "9100": "PAPUA BARAT",
                "9200": "PAPUA BARAT DAYA",
                "9400": "PAPUA",
                "9500": "PAPUA SELATAN",
                "9600": "PAPUA TENGAH",
                "9700": "PAPUA PEGUNUNGAN"
            };

            for (let code in mapping) {
                const mapName = mapping[code];
                if (norm.includes(mapName) || mapName.includes(norm)) {
                    return code;
                }
            }
            return null;
        }

        function carouselSlider() {
            return {
                currentIndex: 0,
                cardsCount: {{ count($cards) }},
                cardWidth: 0,
                gap: 20,
                trackWidth: 0,
                containerWidth: 0,
                cardOffset: 0,
                maxIndex: 0,
                searchQuery: '',
                suggestions: [],

                // Map state variables
                provincesData: @json($provincesData),
                miniMapInstance: null,
                miniGeoJsonLayer: null,
                activeIndicator: 'tpt',
                hoveredProvince: null,

                init() {
                    this.calculateMetrics();
                    window.addEventListener('resize', () => this.calculateMetrics());
                    
                    // Watch screen sizes
                    this.$watch('currentIndex', value => {
                        if (value < 0) this.currentIndex = 0;
                        if (value > this.maxIndex) this.currentIndex = this.maxIndex;
                    });

                    // Initialize Map
                    setTimeout(() => this.initMiniMap(), 100);
                },

                calculateMetrics() {
                    const container = document.querySelector('.carousel-container');
                    if (!container) return;
                    
                    this.containerWidth = container.offsetWidth;
                    const screenWidth = window.innerWidth;
                    
                    let visibleCards = 3;
                    if (screenWidth <= 640) {
                        visibleCards = 1;
                    } else if (screenWidth <= 1024) {
                        visibleCards = 2;
                    }

                    this.cardWidth = (this.containerWidth - (this.gap * (visibleCards - 1))) / visibleCards;
                    this.cardOffset = this.cardWidth + this.gap;
                    this.maxIndex = Math.max(0, this.cardsCount - visibleCards);
                    
                    // Adjust track cards sizes dynamically
                    const cards = document.querySelectorAll('.metric-card');
                    cards.forEach(card => {
                        card.style.flex = `0 0 ${this.cardWidth}px`;
                    });
                },

                next() {
                    if (this.currentIndex < this.maxIndex) {
                        this.currentIndex++;
                    }
                },

                prev() {
                    if (this.currentIndex > 0) {
                        this.currentIndex--;
                    }
                },

                searchMetric() {
                    const query = this.searchQuery.toLowerCase().trim();
                    if (!query) return;

                    const cards = document.querySelectorAll('.metric-card');
                    let foundUrl = null;
                    
                    cards.forEach(card => {
                        const label = card.getAttribute('data-label');
                        if (label.includes(query)) {
                            foundUrl = card.getAttribute('href');
                        }
                    });

                    if (foundUrl) {
                        window.location.href = foundUrl;
                    } else {
                        alert('Indikator data tidak ditemukan. Silakan cari TPT, kemiskinan, gini ratio, inflasi, atau PDB.');
                    }
                },

                async fetchSuggestions() {
                    const q = this.searchQuery.trim();
                    if (q.length < 2) {
                        this.suggestions = [];
                        return;
                    }
                    try {
                        const res = await fetch(`/api/search-suggestions?q=${encodeURIComponent(q)}`);
                        if (res.ok) {
                            this.suggestions = await res.json();
                        }
                    } catch(e) {
                        console.error(e);
                    }
                },

                async initMiniMap() {
                    const mapContainer = document.getElementById('miniMap');
                    if (!mapContainer) return;

                    this.miniMapInstance = L.map('miniMap', {
                        center: [-2.5, 118],
                        zoom: 5,
                        zoomControl: true,
                        scrollWheelZoom: false
                    });

                    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                        maxZoom: 20
                    }).addTo(this.miniMapInstance);

                    await this.updateMiniMapData();
                },

                async updateMiniMapData() {
                    if (this.miniGeoJsonLayer) {
                        this.miniMapInstance.removeLayer(this.miniGeoJsonLayer);
                    }

                    try {
                        const geoUrl = 'https://raw.githubusercontent.com/superpikar/indonesia-geojson/master/indonesia-province.json';
                        const geoRes = await fetch(geoUrl);
                        const geoJson = await geoRes.json();

                        this.miniGeoJsonLayer = L.geoJson(geoJson, {
                            style: (feature) => {
                                const provName = feature.properties.Propinsi || feature.properties.NAME_1 || "";
                                const code = getProvinceCodeByName(provName);
                                
                                let value = 0;
                                if (code && this.provincesData[code]) {
                                    value = parseFloat(this.provincesData[code][this.activeIndicator]) || 0;
                                }

                                return {
                                    fillColor: this.getColorForChoropleth(value, this.activeIndicator),
                                    weight: 1,
                                    opacity: 1,
                                    color: '#cbd5e1',
                                    fillOpacity: 0.8
                                };
                            },
                            onEachFeature: (feature, layer) => {
                                const provName = feature.properties.Propinsi || feature.properties.NAME_1 || "";
                                const code = getProvinceCodeByName(provName);

                                layer.on('mouseover', () => {
                                    layer.setStyle({
                                        fillOpacity: 1,
                                        weight: 2,
                                        color: '#64748b'
                                    });
                                    if (code && this.provincesData[code]) {
                                        this.hoveredProvince = {
                                            code: code,
                                            name: this.provincesData[code].name,
                                            tpt: this.provincesData[code].tpt,
                                            gini_ratio: this.provincesData[code].gini_ratio,
                                            kemiskinan: this.provincesData[code].kemiskinan,
                                            inflasi_tahunan: this.provincesData[code].inflasi_tahunan
                                        };
                                    } else {
                                        this.hoveredProvince = {
                                            code: code,
                                            name: provName,
                                            tpt: null,
                                            gini_ratio: null,
                                            kemiskinan: null,
                                            inflasi_tahunan: null
                                        };
                                    }
                                });

                                layer.on('mouseout', () => {
                                    layer.setStyle({
                                        fillOpacity: 0.8,
                                        weight: 1,
                                        color: '#cbd5e1'
                                    });
                                });

                                layer.on('click', () => {
                                    if (code) {
                                        window.location.href = `{{ url('/metric') }}/${this.activeIndicator}?provinsi=${code}`;
                                    }
                                });
                            }
                        }).addTo(this.miniMapInstance);
                    } catch (e) {
                        console.error("Error loading geojson in mini map", e);
                    }
                },

                getColorForChoropleth(val, metric) {
                    if (!val || val === 0) return '#f1f5f9';
                    
                    if (metric === 'tpt') {
                        if (val >= 7) return '#b91c1c';
                        if (val >= 5) return '#f97316';
                        if (val >= 3) return '#fbbf24';
                        return '#10b981';
                    }
                    if (metric === 'gini_ratio') {
                        if (val >= 0.40) return '#b91c1c';
                        if (val >= 0.37) return '#f97316';
                        if (val >= 0.32) return '#fbbf24';
                        return '#10b981';
                    }
                    if (metric === 'kemiskinan') {
                        if (val >= 18) return '#b91c1c';
                        if (val >= 12) return '#f97316';
                        if (val >= 7) return '#fbbf24';
                        return '#10b981';
                    }
                    if (metric === 'inflasi_tahunan') {
                        if (val >= 4.5) return '#b91c1c';
                        if (val >= 3) return '#f97316';
                        if (val >= 1.5) return '#fbbf24';
                        return '#10b981';
                    }
                    return '#3b82f6';
                }
            }
        }
    </script>
</body>
</html>
