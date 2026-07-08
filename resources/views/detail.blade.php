<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $config['label'] }} — Detail Indikator BPS</title>
    <meta name="description" content="Detail Statistik {{ $config['label'] }} dari BPS API untuk rentang tahun 2010 hingga 2026.">
    <link rel="icon" type="image/svg+xml" href="{{ asset('LOGOBPS.svg') }}">
    <link rel="shortcut icon" type="image/svg+xml" href="{{ asset('LOGOBPS.svg') }}">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
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

        /* Top Header */
        .header-bg {
            background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%);
            color: #fff;
            position: sticky;
            top: 0;
            z-index: 999;
            box-shadow: 0 4px 20px rgba(15, 23, 42, 0.08);
        }
        .nav-inner {
            max-width: 1280px;
            margin: 0 auto;
            padding: 16px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn-back {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 8px 16px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }
        .btn-back:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(-2px);
        }

        /* Container & Layout */
        .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 24px;
            width: 100%;
        }
        .metric-title-section {
            background: #fff;
            border-radius: 20px;
            padding: 28px;
            margin-bottom: 24px;
            box-shadow: 0 4px 20px rgba(15, 23, 42, 0.02);
            border: 1px solid #f1f5f9;
        }
        .metric-title {
            font-size: 28px;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 8px;
            letter-spacing: -0.02em;
        }
        .metric-desc {
            font-size: 14px;
            color: #64748b;
            line-height: 1.6;
            max-width: 900px;
        }

        /* Filter Panel */
        .filter-panel {
            background: #fff;
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 24px;
            box-shadow: 0 4px 20px rgba(15, 23, 42, 0.02);
            border: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
        }
        .filter-group {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }
        .filter-label {
            font-size: 11px;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.05em;
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
            min-width: 140px;
        }
        .select-filter:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .btn-export {
            background: #10b981;
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 8px 16px;
            font-size: 13px;
            font-family: inherit;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }
        .btn-export:hover {
            background: #059669;
        }

        /* Glassmorphism Styles */
        .glass-panel {
            background: rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.6);
            box-shadow: 0 10px 30px -10px rgba(31, 38, 135, 0.05);
            border-radius: 20px;
            padding: 24px;
        }

        /* Summary Cards Grid */
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }
        .stat-card {
            background: #fff;
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 4px 20px rgba(15, 23, 42, 0.02);
            border: 1px solid #f1f5f9;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: all 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(15, 23, 42, 0.05);
        }
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
        }
        .stat-card.blue::before { background: #2563eb; }
        .stat-card.red::before { background: #ef4444; }
        .stat-card.green::before { background: #10b981; }
        .stat-card.orange::before { background: #f59e0b; }

        .stat-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            margin-bottom: 8px;
        }
        .stat-value {
            font-size: 28px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.02em;
        }
        .stat-unit {
            font-size: 16px;
            font-weight: 600;
            color: #64748b;
        }
        .stat-meta {
            font-size: 11px;
            color: #94a3b8;
            margin-top: 6px;
            font-weight: 500;
        }
        .trend-badge {
            font-size: 11px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 100px;
            display: inline-flex;
            align-items: center;
        }
        .trend-badge.up { background: #fee2e2; color: #b91c1c; }
        .trend-badge.down { background: #dcfce7; color: #15803d; }
        .trend-badge.neutral { background: #f1f5f9; color: #64748b; }

        /* Visualization Layouts */
        .visual-row {
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            gap: 20px;
            margin-bottom: 24px;
        }
        .panel {
            background: #fff;
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 4px 20px rgba(15, 23, 42, 0.02);
            border: 1px solid #f1f5f9;
        }
        .panel-header {
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .panel-title {
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
        }
        .panel-sub {
            font-size: 12px;
            color: #64748b;
            margin-top: 2px;
        }

        /* Map styling */
        .map-wrapper {
            height: 400px;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            position: relative;
            z-index: 1;
        }

        /* Table panel */
        .table-panel {
            margin-bottom: 24px;
            overflow: hidden;
        }
        .table-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 16px;
        }
        .table-toolbar-left {
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }
        .search-box {
            position: relative;
            display: flex;
            align-items: center;
        }
        .search-box svg {
            position: absolute;
            left: 12px;
            color: #94a3b8;
            pointer-events: none;
        }
        .search-table-input {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 10px 16px 10px 40px;
            font-size: 13px;
            font-family: inherit;
            outline: none;
            width: 280px;
            transition: all 0.3s;
            background: #f8fafc;
        }
        .search-table-input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.08);
            background: #fff;
        }
        .page-size-selector {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #64748b;
        }
        .page-size-select {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 6px 10px;
            font-size: 13px;
            font-family: inherit;
            outline: none;
            background: #f8fafc;
            color: #334155;
            cursor: pointer;
            transition: all 0.2s;
        }
        .page-size-select:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.08);
        }

        .table-wrap {
            overflow-x: auto;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            text-align: left;
        }
        .data-table th {
            background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
            padding: 14px 16px;
            font-weight: 700;
            color: #475569;
            border-bottom: 2px solid #e2e8f0;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            white-space: nowrap;
        }
        .data-table td {
            padding: 13px 16px;
            border-bottom: 1px solid #f1f5f9;
            color: #1e293b;
            transition: background 0.15s;
        }
        .data-table tbody tr {
            transition: all 0.15s;
        }
        .data-table tbody tr:hover {
            background: linear-gradient(90deg, #eff6ff 0%, #f0f7ff 100%);
        }
        .data-table tbody tr:hover td:first-child {
            box-shadow: inset 3px 0 0 #2563eb;
        }
        .badge-status {
            font-size: 11px;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            white-space: nowrap;
        }
        .badge-status.red { background: #fee2e2; color: #b91c1c; }
        .badge-status.orange { background: #fef3c7; color: #d97706; }
        .badge-status.green { background: #dcfce7; color: #15803d; }
        .badge-status.blue { background: #dbeafe; color: #2563eb; }

        /* Premium Pagination */
        .pagination-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 0 0;
            margin-top: 16px;
            border-top: 1px solid #f1f5f9;
            flex-wrap: wrap;
            gap: 12px;
        }
        .pagination-info {
            font-size: 13px;
            color: #64748b;
        }
        .pagination-info strong {
            color: #1e293b;
            font-weight: 700;
        }
        .pagination-controls {
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .pg-btn {
            min-width: 36px;
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            background: #fff;
            color: #475569;
            font-size: 13px;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.2s;
            padding: 0 6px;
        }
        .pg-btn:hover:not(:disabled):not(.active) {
            background: #f1f5f9;
            border-color: #cbd5e1;
            color: #1e293b;
        }
        .pg-btn.active {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            border-color: #2563eb;
            color: #fff;
            box-shadow: 0 2px 8px rgba(37, 99, 235, 0.3);
        }
        .pg-btn:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }
        .pg-btn.nav-btn {
            padding: 0 12px;
            gap: 4px;
            font-weight: 500;
        }
        .pg-ellipsis {
            min-width: 36px;
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #94a3b8;
            font-size: 14px;
            letter-spacing: 2px;
        }
        .table-empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 48px 24px;
            color: #94a3b8;
            gap: 12px;
        }
        .table-empty-state svg {
            color: #cbd5e1;
        }
        .table-row-number {
            width: 36px;
            height: 24px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #f1f5f9;
            border-radius: 6px;
            font-weight: 600;
            font-size: 11px;
            color: #64748b;
        }

        /* Loader */
        .loader-overlay {
            position: fixed;
            inset: 0;
            z-index: 9999;
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(2px);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .spinner {
            width: 48px;
            height: 48px;
            border: 4px solid #dbeafe;
            border-top-color: #2563eb;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* Footer */
        .footer {
            margin-top: auto;
            background: #0f172a;
            color: #94a3b8;
            padding: 30px 24px;
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

        /* ============================================
           RESPONSIVE DESIGN — Desktop / Tablet / HP
           ============================================ */

        /* TABLET (768px - 1024px) */
        @media(max-width: 1024px) {
            .summary-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .visual-row {
                grid-template-columns: 1fr !important;
            }
            .visual-row .panel[style*="grid-column"] {
                grid-column: span 1 !important;
            }
            .nav-inner {
                padding: 12px 16px;
            }
            .container {
                padding: 16px;
            }
        }

        /* SMALL TABLET / LARGE PHONE (641px - 768px) */
        @media(max-width: 768px) {
            .metric-title {
                font-size: 22px;
            }
            .metric-desc {
                font-size: 13px;
            }
            .metric-title-section {
                padding: 20px;
                border-radius: 14px;
            }
            .summary-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 10px;
            }
            .stat-card {
                padding: 14px;
                border-radius: 12px;
            }
            .stat-value {
                font-size: 22px;
            }
            .panel {
                padding: 16px;
                border-radius: 14px;
            }
            .panel-title {
                font-size: 14px;
            }
            .filter-panel {
                flex-direction: column;
                align-items: stretch;
                padding: 14px;
                gap: 12px;
            }
            .filter-group {
                flex-direction: column;
                align-items: stretch;
            }
            .select-filter {
                width: 100% !important;
                min-width: unset !important;
                max-width: unset !important;
            }
            .map-wrapper {
                height: 280px;
            }
            .search-table-input {
                width: 100% !important;
            }
            .table-toolbar {
                flex-direction: column;
                align-items: stretch;
            }
            .table-toolbar-left {
                flex-direction: column;
                align-items: stretch;
            }
            .compare-grid {
                grid-template-columns: 1fr !important;
            }
            .compare-grid > div h4 {
                font-size: 18px !important;
            }
            .detail-breadcrumb {
                padding: 0 14px 10px 14px !important;
                font-size: 11px !important;
            }
            .header-logo-img {
                height: 28px !important;
            }
            .header-title-text {
                font-size: 14px !important;
            }
            .header-subtitle-text {
                font-size: 9px !important;
            }
            .academic-section {
                padding: 16px !important;
            }
            .academic-section .academic-text {
                font-size: 13px !important;
            }
            .wilayah-badge {
                font-size: 12px !important;
                padding: 0 12px !important;
                height: 36px !important;
            }
        }

        /* MOBILE PHONE (max 640px) */
        @media(max-width: 640px) {
            .nav-inner {
                padding: 10px 12px;
                flex-direction: column;
                align-items: flex-start;
                gap: 4px;
            }
            .btn-back {
                padding: 6px 10px;
                font-size: 11px;
            }
            .container {
                padding: 12px 10px;
            }
            .metric-title-section {
                padding: 16px;
                border-radius: 12px;
                margin-bottom: 16px;
            }
            .metric-title {
                font-size: 20px;
            }
            .metric-desc {
                font-size: 12px;
            }
            .summary-grid {
                grid-template-columns: 1fr 1fr;
                gap: 8px;
                margin-bottom: 16px;
            }
            .stat-card {
                padding: 12px;
                border-radius: 12px;
            }
            .stat-label {
                font-size: 9px;
                margin-bottom: 4px;
            }
            .stat-value {
                font-size: 20px;
            }
            .stat-unit {
                font-size: 12px;
            }
            .stat-meta {
                font-size: 9px;
            }
            .visual-row {
                grid-template-columns: 1fr !important;
                gap: 14px;
            }
            .visual-row .panel[style*="grid-column"] {
                grid-column: span 1 !important;
            }
            .panel {
                padding: 14px;
                border-radius: 12px;
            }
            .map-wrapper {
                height: 220px;
            }
            .data-table th {
                padding: 10px 8px;
                font-size: 10px;
            }
            .data-table td {
                padding: 10px 8px;
                font-size: 12px;
            }
            .pg-btn {
                min-width: 28px;
                height: 28px;
                font-size: 11px;
                border-radius: 8px;
            }
            .pg-btn.nav-btn {
                padding: 0 6px;
            }
            .pagination-bar {
                flex-direction: column;
                align-items: center;
                gap: 8px;
            }
            .pagination-info {
                font-size: 11px;
                text-align: center;
            }
            .footer-inner {
                flex-direction: column;
                text-align: center;
                gap: 10px;
            }
            .btn-export {
                width: 100%;
                justify-content: center;
            }
            .compare-grid {
                grid-template-columns: 1fr !important;
                gap: 10px !important;
            }
            .compare-grid > div h4 {
                font-size: 16px !important;
            }
        }

        /* VERY SMALL PHONE (max 380px) */
        @media(max-width: 380px) {
            .summary-grid {
                grid-template-columns: 1fr;
            }
            .metric-title {
                font-size: 18px;
            }
            .stat-value {
                font-size: 18px;
            }
        }
    </style>
</head>
<body x-data="detailDashboard()">

    <!-- Loader -->
    <div x-show="isLoading" class="loader-overlay" x-cloak x-transition>
        <div class="spinner"></div>
    </div>

    <!-- Top Header with Integrated Breadcrumb -->
    <header class="header-bg">
        <div class="nav-inner">
            <a href="{{ route('home') }}" style="display:flex; align-items:center; gap:10px; text-decoration:none; color:#fff;">
                <img class="header-logo-img" src="{{ asset('LOGOBPS.svg') }}" alt="BPS logo" style="height:36px; filter: brightness(0) invert(1);">
                <div>
                    <h2 class="header-title-text" style="font-size:16px; font-weight:700; line-height:1.2;">Portal Data BPS</h2>
                    <p class="header-subtitle-text" style="font-size:10px; color:#93c5fd;">Integrasi Indikator Makro Indonesia</p>
                </div>
            </a>
            <div style="display:flex; align-items:center; gap:6px;">
                <a href="{{ route('home') }}" class="btn-back" style="background:rgba(255,255,255,0.05); border-color:rgba(255,255,255,0.15);">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    Beranda
                </a>
            </div>
        </div>
        <!-- Breadcrumb (integrated into header) -->
        <div class="detail-breadcrumb" style="max-width:1280px; margin:0 auto; padding:0 24px 12px 24px; display:flex; align-items:center; gap:8px; font-size:12px; color:rgba(148,163,184,0.8); flex-wrap:wrap;">
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            <a href="{{ route('home') }}" style="color:#93c5fd; text-decoration:none; font-weight:600;">Beranda</a>
            <span style="color:rgba(148,163,184,0.5);">›</span>
            <span style="color:#fff; font-weight:700;">{{ $config['label'] }}</span>
        </div>
    </header>

    <!-- Main Container -->
    <main class="container">
        
        <!-- Metric Slogan and Title Card -->
        <section class="metric-title-section">
            <h1 class="metric-title">{{ $config['label'] }}</h1>
            <p class="metric-desc">{{ $config['description'] }}</p>
        </section>

        <!-- Dynamic Filter Panel -->
        <section class="filter-panel">
            <div class="filter-group">
                <div style="display:flex; flex-direction:column; gap:4px;">
                    <span class="filter-label" style="color: #0f172a;">Pilih Indikator</span>
                    <select class="select-filter" style="border-color: #94a3b8; font-weight:600; color:#0f172a; min-width: 200px;" @change="window.location.href = '{{ url('/metric') }}/' + $event.target.value + (filters.provinsi !== 'all' ? '?provinsi=' + filters.provinsi : '')">
                        <option value="tpt" {{ $metric == 'tpt' ? 'selected' : '' }}>Tingkat Pengangguran Terbuka</option>
                        <option value="gini_ratio" {{ $metric == 'gini_ratio' ? 'selected' : '' }}>Gini Ratio</option>
                        <option value="kemiskinan" {{ $metric == 'kemiskinan' ? 'selected' : '' }}>Persentase Penduduk Miskin</option>
                        <option value="inflasi_tahunan" {{ $metric == 'inflasi_tahunan' ? 'selected' : '' }}>Inflasi (Tahunan)</option>
                        <option value="inflasi_bulanan" {{ $metric == 'inflasi_bulanan' ? 'selected' : '' }}>Inflasi (Bulanan)</option>
                        <option value="pdb_growth" {{ $metric == 'pdb_growth' ? 'selected' : '' }}>Laju Pertumbuhan PDB</option>
                        <option value="ihpb" {{ $metric == 'ihpb' ? 'selected' : '' }}>Indeks Harga Perdagangan Besar</option>
                    </select>
                </div>
                <div style="display:flex; flex-direction:column; gap:4px;">
                    <span class="filter-label">Tahun</span>
                    <select class="select-filter" x-model="filters.tahun" @change="updateDashboard()">
                        <option value="all">Semua Tahun</option>
                        @foreach($tahuns as $t)
                            <option value="{{ $t->tahun }}">{{ $t->tahun }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="display:flex; flex-direction:column; gap:4px;">
                    <span class="filter-label">Periode</span>
                    <select class="select-filter" x-model="filters.periode" @change="updateDashboard()">
                        <option value="all">Semua Periode</option>
                        @foreach($periodes as $p)
                            <option value="{{ $p->periode }}">{{ $p->periode }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="display:flex; flex-direction:column; gap:4px;">
                    <span class="filter-label">Wilayah Aktif</span>
                    <div style="display:flex; align-items:center; gap:8px;">
                        <template x-if="filters.provinsi === 'all'">
                            <span class="wilayah-badge" style="display:inline-flex; align-items:center; gap:6px; height:42px; padding:0 16px; border-radius:12px; background:#f8fafc; border:1px solid #cbd5e1; font-size:14px; color:#64748b; font-weight:600;">
                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Seluruh Wilayah (Nasional)
                            </span>
                        </template>
                        <template x-if="filters.provinsi !== 'all'">
                            <span class="wilayah-badge" style="display:inline-flex; align-items:center; gap:8px; height:42px; padding:0 16px; border-radius:12px; background:#eff6ff; border:2px solid #3b82f6; font-size:14px; color:#1d4ed8; font-weight:700; box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.1), 0 2px 4px -1px rgba(59, 130, 246, 0.06);">
                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span x-text="getWilayahDisplayName(filters.provinsi)"></span>
                                <button @click="filters.provinsi = 'all'; updateDashboard()" style="background:rgba(219, 234, 254, 0.5); border:1px solid #bfdbfe; border-radius:6px; cursor:pointer; color:#1e40af; display:flex; align-items:center; justify-content:center; padding:4px; margin-left:4px; transition:all 0.2s;" onmouseover="this.style.background='#bfdbfe'" onmouseout="this.style.background='rgba(219, 234, 254, 0.5)'" title="Reset ke Nasional">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </span>
                        </template>
                        <span style="font-size:11px; color:#94a3b8; margin-left:4px; font-style:italic;">*Klik peta untuk mengganti</span>
                    </div>
                </div>

                <!-- Compare Mode Selector -->
                <div style="display:flex; flex-direction:column; gap:4px;">
                    <span class="filter-label" style="color: #2563eb;">Bandingkan Wilayah/Kategori</span>
                    <select class="select-filter" x-model="compare.targetCode" @change="updateComparison()" style="max-width: 250px; border-color: rgba(37, 99, 235, 0.4);">
                        <option value="">-- Pilih Pembanding --</option>
                        @foreach($provinsis as $pr)
                            <option value="{{ $pr->kode_wilayah }}">{{ $pr->nama_wilayah }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            
            <button @click="exportCSV()" class="btn-export">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Ekspor data CSV
            </button>
        </section>

        <!-- Compare Mode Summary Row -->
        <template x-if="compare.active && compare.data">
            <div class="panel" style="margin-bottom: 24px; border: 1px dashed #2563eb; background: rgba(37, 99, 235, 0.02);">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 16px;">
                    <h3 class="panel-title" style="color: #2563eb; display:flex; align-items:center; gap:8px;">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        Hasil Perbandingan Makro
                    </h3>
                    <button class="btn-back" @click="clearCompare()" style="background:#ef4444; border:none; padding:4px 10px; font-size:11px; cursor:pointer;">
                        Hapus Bandingkan
                    </button>
                </div>
                <div class="compare-grid" style="display:grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
                    <div style="background:#fff; padding:16px; border-radius:12px; border: 1px solid #e2e8f0; text-align:center;">
                        <span style="font-size:11px; font-weight:600; color:#64748b;" x-text="compare.data.base.name"></span>
                        <h4 style="font-size:24px; font-weight:800; color:#1F3C88; margin-top:8px;" x-text="compare.data.base.value + ' ' + metricUnit()"></h4>
                    </div>
                    <div style="background:#fff; padding:16px; border-radius:12px; border: 1px solid #e2e8f0; text-align:center;">
                        <span style="font-size:11px; font-weight:600; color:#64748b;" x-text="compare.data.compare.name"></span>
                        <h4 style="font-size:24px; font-weight:800; color:#ef4444; margin-top:8px;" x-text="compare.data.compare.value + ' ' + metricUnit()"></h4>
                    </div>
                    <div style="background:#fff; padding:16px; border-radius:12px; border: 1px solid #e2e8f0; text-align:center; display:flex; flex-direction:column; justify-content:center; align-items:center;">
                        <span style="font-size:11px; font-weight:600; color:#64748b;">Selisih (Delta)</span>
                        <div style="display:flex; align-items:center; gap:6px; margin-top:8px;">
                            <span class="trend-badge" :class="compare.data.delta < 0 ? 'down' : (compare.data.delta > 0 ? 'up' : 'neutral')" style="font-size:18px; padding:6px 12px;">
                                <span x-text="(compare.data.delta > 0 ? '▲ +' : '') + compare.data.delta"></span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <!-- Card summary statistics -->
        <section class="summary-grid">
            <!-- Overall card value -->
            <div class="stat-card blue">
                <div>
                    <div style="display:flex; justify-content:space-between; align-items:start;">
                        <p class="stat-label" x-text="filters.provinsi === 'all' ? 'Nilai Nasional (BPS)' : ('Nilai ' + getWilayahDisplayName(filters.provinsi))"></p>
                        <template x-if="summary.trend !== null && summary.trend !== undefined">
                            <span class="trend-badge" :class="summary.trend < 0 ? 'down' : 'up'">
                                <span x-text="(summary.trend > 0 ? '▲ +' : '▼ ') + summary.trend + '%'"></span>
                            </span>
                        </template>
                    </div>
                    <p class="stat-value">
                        <span x-text="summary.value">-</span><span class="stat-unit">{{ $config['unit'] }}</span>
                    </p>
                </div>
                <p class="stat-meta">Tahun: <span x-text="summary.current_year">-</span> (<span x-text="summary.current_periode">-</span>)</p>
            </div>

            <!-- Average value -->
            <div class="stat-card orange">
                <div>
                    <p class="stat-label">Rata-rata Seluruh Provinsi</p>
                    <p class="stat-value">
                        <span x-text="summary.avg_val">-</span><span class="stat-unit">{{ $config['unit'] }}</span>
                    </p>
                </div>
                <p class="stat-meta">Rata-rata hitung (unweighted) dari semua data provinsi</p>
            </div>

            <!-- Maximum Value -->
            <div class="stat-card red">
                <div>
                    <p class="stat-label">Nilai Tertinggi</p>
                    <p class="stat-value">
                        <span x-text="summary.max_val">-</span><span class="stat-unit">{{ $config['unit'] }}</span>
                    </p>
                </div>
                <p class="stat-meta" x-text="summary.max_name">-</p>
            </div>

            <!-- Minimum Value -->
            <div class="stat-card green">
                <div>
                    <p class="stat-label">Nilai Terendah</p>
                    <p class="stat-value">
                        <span x-text="summary.min_val">-</span><span class="stat-unit">{{ $config['unit'] }}</span>
                    </p>
                </div>
                <p class="stat-meta" x-text="summary.min_name">-</p>
            </div>
        </section>

        <!-- Academic Insights Panel -->
        <section class="panel glass-panel academic-section" style="margin-bottom: 24px; padding: 24px; border-left: 5px solid #2563eb; background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(248, 250, 252, 0.9) 100%);">
            <div style="display:flex; align-items:center; gap:10px; margin-bottom:12px;">
                <div style="background: rgba(37, 99, 235, 0.1); color: #2563eb; width: 36px; height: 36px; border-radius: 10px; display:flex; align-items:center; justify-content:center;">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </div>
                <div>
                    <h3 style="font-size:16px; font-weight:700; color:#0f172a;">Analisis & Interpretasi Akademik</h3>
                    <p style="font-size:11px; color:#64748b;">Interpretasi data komprehensif berdasarkan tren historis (skripsi-ready)</p>
                </div>
            </div>
            <div class="academic-text" style="font-size:14px; color:#334155; line-height:1.75;" x-html="getAcademicInsights()">
                Membuat interpretasi data akademik...
            </div>
        </section>

        <!-- Visualization panels -->
        <section class="visual-row">
            
            <!-- Map panel -->
            <div class="panel">
                <div class="panel-header">
                    <div>
                        <h3 class="panel-title">Persebaran Spasial Geografis</h3>
                        <p class="panel-sub">Peta choropleth persebaran nilai indikator antar provinsi</p>
                    </div>
                </div>
                <div class="map-wrapper" id="leafletMap">
                    <!-- Leaflet map container -->
                </div>
                <!-- Map Legend -->
                <div style="display:flex; align-items:center; gap:10px; margin-top:14px; padding:10px 14px; background:#f8fafc; border-radius:10px; border:1px solid #e2e8f0; flex-wrap:wrap; font-size:11px;">
                    <span style="font-weight:700; color:#475569;">Legenda:</span>
                    <template x-if="metric === 'tpt'">
                        <div style="display:flex; gap:10px; flex-wrap:wrap;">
                            <span style="display:flex;align-items:center;gap:4px;"><span style="width:12px;height:12px;border-radius:3px;background:#10b981;"></span> Baik (&lt;3%)</span>
                            <span style="display:flex;align-items:center;gap:4px;"><span style="width:12px;height:12px;border-radius:3px;background:#fbbf24;"></span> Sedang (3–5%)</span>
                            <span style="display:flex;align-items:center;gap:4px;"><span style="width:12px;height:12px;border-radius:3px;background:#f97316;"></span> Waspada (5–7%)</span>
                            <span style="display:flex;align-items:center;gap:4px;"><span style="width:12px;height:12px;border-radius:3px;background:#b91c1c;"></span> Kritis (≥7%)</span>
                        </div>
                    </template>
                    <template x-if="metric === 'gini_ratio'">
                        <div style="display:flex; gap:10px; flex-wrap:wrap;">
                            <span style="display:flex;align-items:center;gap:4px;"><span style="width:12px;height:12px;border-radius:3px;background:#10b981;"></span> Merata (&lt;0.32)</span>
                            <span style="display:flex;align-items:center;gap:4px;"><span style="width:12px;height:12px;border-radius:3px;background:#fbbf24;"></span> Sedang (0.32–0.37)</span>
                            <span style="display:flex;align-items:center;gap:4px;"><span style="width:12px;height:12px;border-radius:3px;background:#f97316;"></span> Timpang (0.37–0.40)</span>
                            <span style="display:flex;align-items:center;gap:4px;"><span style="width:12px;height:12px;border-radius:3px;background:#b91c1c;"></span> Sangat Timpang (≥0.40)</span>
                        </div>
                    </template>
                    <template x-if="metric === 'kemiskinan'">
                        <div style="display:flex; gap:10px; flex-wrap:wrap;">
                            <span style="display:flex;align-items:center;gap:4px;"><span style="width:12px;height:12px;border-radius:3px;background:#10b981;"></span> Rendah (&lt;7%)</span>
                            <span style="display:flex;align-items:center;gap:4px;"><span style="width:12px;height:12px;border-radius:3px;background:#fbbf24;"></span> Sedang (7–12%)</span>
                            <span style="display:flex;align-items:center;gap:4px;"><span style="width:12px;height:12px;border-radius:3px;background:#f97316;"></span> Tinggi (12–18%)</span>
                            <span style="display:flex;align-items:center;gap:4px;"><span style="width:12px;height:12px;border-radius:3px;background:#b91c1c;"></span> Sangat Tinggi (≥18%)</span>
                        </div>
                    </template>
                    <template x-if="metric === 'inflasi_tahunan' || metric === 'inflasi_bulanan'">
                        <div style="display:flex; gap:10px; flex-wrap:wrap;">
                            <span style="display:flex;align-items:center;gap:4px;"><span style="width:12px;height:12px;border-radius:3px;background:#10b981;"></span> Rendah (&lt;1.5%)</span>
                            <span style="display:flex;align-items:center;gap:4px;"><span style="width:12px;height:12px;border-radius:3px;background:#fbbf24;"></span> Terkendali (1.5–3%)</span>
                            <span style="display:flex;align-items:center;gap:4px;"><span style="width:12px;height:12px;border-radius:3px;background:#f97316;"></span> Tinggi (3–4.5%)</span>
                            <span style="display:flex;align-items:center;gap:4px;"><span style="width:12px;height:12px;border-radius:3px;background:#b91c1c;"></span> Sangat Tinggi (≥4.5%)</span>
                        </div>
                    </template>
                    <template x-if="metric === 'pdb_growth'">
                        <div style="display:flex; gap:10px; flex-wrap:wrap;">
                            <span style="display:flex;align-items:center;gap:4px;"><span style="width:12px;height:12px;border-radius:3px;background:#ef4444;"></span> Negatif (&lt;0%)</span>
                            <span style="display:flex;align-items:center;gap:4px;"><span style="width:12px;height:12px;border-radius:3px;background:#f97316;"></span> Lambat (0–2%)</span>
                            <span style="display:flex;align-items:center;gap:4px;"><span style="width:12px;height:12px;border-radius:3px;background:#eab308;"></span> Sedang (2–4%)</span>
                            <span style="display:flex;align-items:center;gap:4px;"><span style="width:12px;height:12px;border-radius:3px;background:#22c55e;"></span> Baik (4–6%)</span>
                            <span style="display:flex;align-items:center;gap:4px;"><span style="width:12px;height:12px;border-radius:3px;background:#15803d;"></span> Kuat (≥6%)</span>
                        </div>
                    </template>
                    <template x-if="metric === 'ihpb'">
                        <div style="display:flex; gap:10px; flex-wrap:wrap;">
                            <span style="display:flex;align-items:center;gap:4px;"><span style="width:12px;height:12px;border-radius:3px;background:#93c5fd;"></span> &lt;100</span>
                            <span style="display:flex;align-items:center;gap:4px;"><span style="width:12px;height:12px;border-radius:3px;background:#60a5fa;"></span> 100–110</span>
                            <span style="display:flex;align-items:center;gap:4px;"><span style="width:12px;height:12px;border-radius:3px;background:#3b82f6;"></span> 110–120</span>
                            <span style="display:flex;align-items:center;gap:4px;"><span style="width:12px;height:12px;border-radius:3px;background:#2563eb;"></span> 120–130</span>
                            <span style="display:flex;align-items:center;gap:4px;"><span style="width:12px;height:12px;border-radius:3px;background:#1e3a8a;"></span> ≥130</span>
                        </div>
                    </template>
                    <span style="display:flex;align-items:center;gap:4px;margin-left:auto;"><span style="width:12px;height:12px;border-radius:3px;background:#f1f5f9;border:1px solid #e2e8f0;"></span><span style="color:#94a3b8;">Tidak ada data</span></span>
                </div>
            </div>

            <!-- Line trend panel -->
            <div class="panel">
                <div class="panel-header">
                    <div>
                        <h3 class="panel-title">Tren Perkembangan Historis</h3>
                        <p class="panel-sub">Tren pergerakan nilai indikator utama dari tahun ke tahun</p>
                    </div>
                </div>
                <div>
                    <canvas id="lineChartCanvas" style="max-height:350px;"></canvas>
                </div>
            </div>

            <!-- Bar Chart panel -->
            <div class="panel" style="grid-column: span 2;">
                <div class="panel-header">
                    <div>
                        <h3 class="panel-title">Komparasi dan Peringkat Kategori</h3>
                        <p class="panel-sub">Perbandingan nilai indikator untuk tahun dan periode aktif</p>
                    </div>
                </div>
                <div>
                    <canvas id="barChartCanvas" style="max-height:350px;"></canvas>
                </div>
            </div>
        </section>

        <!-- Data table panel -->
        <section class="panel table-panel">
            <!-- Toolbar: Title + Search + Page Size -->
            <div class="table-toolbar">
                <div>
                    <h3 class="panel-title" style="display:flex; align-items:center; gap:8px;">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        Rincian Tabular Dataset
                    </h3>
                    <p class="panel-sub">Tabel rincian angka statistik sesuai filter yang diterapkan</p>
                </div>
                <div class="table-toolbar-left">
                    <div class="page-size-selector">
                        <span>Tampilkan</span>
                        <select class="page-size-select" x-model.number="pageSize" @change="currentPage = 1">
                            <option value="5">5</option>
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span>baris</span>
                    </div>
                    <div class="search-box">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                        <input type="text" placeholder="Cari wilayah/kategori..." class="search-table-input" x-model="tableSearchQuery">
                    </div>
                </div>
            </div>

            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width:60px; text-align:center;">No</th>
                            <th>Wilayah/Kategori</th>
                            <th>Kode</th>
                            <th>Tahun</th>
                            <th>Periode</th>
                            @if($metric === 'pdb_growth' || $metric === 'kemiskinan')
                                <th>Sub Kategori</th>
                            @endif
                            <th style="text-align: right;">Nilai ({{ $config['unit'] ?: 'Rasio' }})</th>
                            <th style="text-align:center;">Status/Evaluasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(row, idx) in paginatedTableData" :key="row.kode + '-' + row.tahun + '-' + row.periode + '-' + idx">
                            <tr>
                                <td style="text-align:center;"><span class="table-row-number" x-text="(currentPage - 1) * pageSize + idx + 1"></span></td>
                                <td style="font-weight:500;" x-text="row.nama"></td>
                                <td style="font-family:'Courier New',monospace; color:#64748b; font-size:12px;" x-text="row.kode"></td>
                                <td>
                                    <span style="background:#f1f5f9; padding:2px 8px; border-radius:6px; font-size:12px; font-weight:600;" x-text="row.tahun"></span>
                                </td>
                                <td>
                                    <span style="background:#eff6ff; color:#2563eb; padding:2px 8px; border-radius:6px; font-size:12px; font-weight:500;" x-text="row.periode"></span>
                                </td>
                                @if($metric === 'pdb_growth' || $metric === 'kemiskinan')
                                    <td style="font-size:12px; max-width:220px;" x-text="row.sub_kategori || 'Keseluruhan'"></td>
                                @endif
                                <td style="text-align: right; font-weight:800; font-size:14px; font-family:'Outfit',sans-serif;" x-text="row.value"></td>
                                <td style="text-align:center;">
                                    <span class="badge-status" :class="getStatusClass(row.value)">
                                        <span x-text="getStatusLabel(row.value)"></span>
                                    </span>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
                <!-- Empty State -->
                <template x-if="filteredTableData().length === 0">
                    <div class="table-empty-state">
                        <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                        <p style="font-weight:600; color:#64748b;">Tidak ada data yang ditemukan</p>
                        <p style="font-size:12px;">Coba ubah filter atau kata kunci pencarian Anda.</p>
                    </div>
                </template>
            </div>

            <!-- Premium Pagination Bar -->
            <div class="pagination-bar" x-show="filteredTableData().length > 0">
                <div class="pagination-info">
                    Menampilkan <strong x-text="Math.min((currentPage - 1) * pageSize + 1, filteredTableData().length)"></strong> — <strong x-text="Math.min(currentPage * pageSize, filteredTableData().length)"></strong> dari <strong x-text="filteredTableData().length"></strong> entri
                </div>
                <div class="pagination-controls">
                    <!-- First -->
                    <button class="pg-btn nav-btn" @click="currentPage = 1" :disabled="currentPage === 1" title="Halaman Pertama">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.75 19.5l-7.5-7.5 7.5-7.5m-6 15L5.25 12l7.5-7.5"/></svg>
                    </button>
                    <!-- Prev -->
                    <button class="pg-btn nav-btn" @click="prevPage()" :disabled="currentPage === 1" title="Sebelumnya">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                    </button>
                    <!-- Page Numbers -->
                    <template x-for="(pg, pgIdx) in visiblePages" :key="'pg-'+pgIdx">
                        <span>
                            <span x-show="pg === '...'" class="pg-ellipsis">…</span>
                            <button x-show="pg !== '...'" class="pg-btn" :class="{ 'active': currentPage === pg }" @click="currentPage = pg" x-text="pg"></button>
                        </span>
                    </template>
                    <!-- Next -->
                    <button class="pg-btn nav-btn" @click="nextPage()" :disabled="currentPage === totalPages" title="Berikutnya">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                    </button>
                    <!-- Last -->
                    <button class="pg-btn nav-btn" @click="currentPage = totalPages" :disabled="currentPage === totalPages" title="Halaman Terakhir">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 4.5l7.5 7.5-7.5 7.5m6-15l7.5 7.5-7.5 7.5"/></svg>
                    </button>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-inner">
            <div style="display:flex; align-items:center; gap:8px;">
                <img src="{{ asset('LOGOBPS.svg') }}" alt="BPS logo" style="height:28px; filter: brightness(0) invert(1);">
                Portal Integrasi Indikator BPS
            </div>
            <div style="font-size:11px;">
                © 2026 Tugas Akhir Mahasiswa - Universitas/Studi Kasus BPS. All Rights Reserved.
            </div>
        </div>
    </footer>

    <!-- Script for handling dynamic data, charts, and Leaflet maps -->
    <script>
        const PROVINCE_NAMES_MAP = {
            "1100": "Aceh",
            "1200": "Sumatera Utara",
            "1300": "Sumatera Barat",
            "1400": "Riau",
            "1500": "Jambi",
            "1600": "Sumatera Selatan",
            "1700": "Bengkulu",
            "1800": "Lampung",
            "1900": "Kepulauan Bangka Belitung",
            "2100": "Kepulauan Riau",
            "3100": "DKI Jakarta",
            "3200": "Jawa Barat",
            "3300": "Jawa Tengah",
            "3400": "DI Yogyakarta",
            "3500": "Jawa Timur",
            "3600": "Banten",
            "5100": "Bali",
            "5200": "Nusa Tenggara Barat",
            "5300": "Nusa Tenggara Timur",
            "6100": "Kalimantan Barat",
            "6200": "Kalimantan Tengah",
            "6300": "Kalimantan Selatan",
            "6400": "Kalimantan Timur",
            "6500": "Kalimantan Utara",
            "7100": "Sulawesi Utara",
            "7200": "Sulawesi Tengah",
            "7300": "Sulawesi Selatan",
            "7400": "Sulawesi Tenggara",
            "7500": "Gorontalo",
            "7600": "Sulawesi Barat",
            "8100": "Maluku",
            "8200": "Maluku Utara",
            "9100": "Papua Barat",
            "9200": "Papua Barat Daya",
            "9400": "Papua",
            "9500": "Papua Selatan",
            "9600": "Papua Tengah",
            "9700": "Papua Pegunungan"
        };

        function getWilayahDisplayNameGlobal(code, tableData) {
            if (!code || code === 'all') return 'Seluruh Wilayah (Nasional)';
            if (PROVINCE_NAMES_MAP[code]) return PROVINCE_NAMES_MAP[code];
            if (tableData && tableData.length > 0) {
                const found = tableData.find(i => String(i.kode) === String(code));
                if (found && found.nama) return found.nama;
            }
            return code;
        }

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

        function detailDashboard() {
            return {
                metric: "{{ $metric }}",
                isProvincial: {{ $config['is_provincial'] ? 'true' : 'false' }},
                filters: {
                    tahun: 'all',
                    periode: 'all',
                    provinsi: 'all'
                },
                compare: {
                    active: false,
                    targetCode: '',
                    data: null
                },
                summary: {
                    value: '-',
                    avg_val: '-',
                    max_val: '-',
                    max_name: '-',
                    min_val: '-',
                    min_name: '-',
                    current_year: '-',
                    current_periode: '-',
                    trend: null
                },
                tableSearchQuery: '',
                currentPage: 1,
                pageSize: 10,
                tableData: [],
                mapData: {},
                isLoading: false,
                leafletMapInstance: null,
                geoJsonLayer: null,
                lineChartInstance: null,
                barChartInstance: null,

                init() {
                    // Read ?provinsi= from URL if navigating from homepage map click
                    const urlParams = new URLSearchParams(window.location.search);
                    const provFromUrl = urlParams.get('provinsi');
                    if (provFromUrl) {
                        this.filters.provinsi = provFromUrl;
                    }
                    this.updateDashboard();
                    this.$watch('tableSearchQuery', () => { this.currentPage = 1; });
                },

                getWilayahDisplayName(code) {
                    return getWilayahDisplayNameGlobal(code, this.tableData);
                },

                get paginatedTableData() {
                    const filtered = this.filteredTableData();
                    const start = (this.currentPage - 1) * this.pageSize;
                    return filtered.slice(start, start + this.pageSize);
                },
                get totalPages() {
                    return Math.ceil(this.filteredTableData().length / this.pageSize) || 1;
                },
                get visiblePages() {
                    const total = this.totalPages;
                    const current = this.currentPage;
                    const pages = [];
                    if (total <= 7) {
                        for (let i = 1; i <= total; i++) pages.push(i);
                    } else {
                        pages.push(1);
                        if (current > 3) pages.push('...');
                        const start = Math.max(2, current - 1);
                        const end = Math.min(total - 1, current + 1);
                        for (let i = start; i <= end; i++) pages.push(i);
                        if (current < total - 2) pages.push('...');
                        pages.push(total);
                    }
                    return pages;
                },
                nextPage() {
                    if (this.currentPage < this.totalPages) this.currentPage++;
                },
                prevPage() {
                    if (this.currentPage > 1) this.currentPage--;
                },

                async updateDashboard() {
                    this.isLoading = true;
                    try {
                        const url = `/api/data/${this.metric}?tahun=${encodeURIComponent(this.filters.tahun)}&periode=${encodeURIComponent(this.filters.periode)}&provinsi=${encodeURIComponent(this.filters.provinsi)}`;
                        const res = await fetch(url);
                        if (!res.ok) {
                            throw new Error(`HTTP error! status: ${res.status}`);
                        }
                        const data = await res.json();
                        
                        this.summary = data.summary || this.summary;
                        this.tableData = data.tableData || [];
                        this.mapData = data.mapData || {};

                        // 1. Draw charts safe check
                        if (this.compare.targetCode) {
                            await this.updateComparisonOnly();
                        } else {
                            try {
                                this.renderCharts(data.lineChart, data.barChart);
                            } catch (chartErr) {
                                console.warn("Chart render error:", chartErr);
                            }
                        }

                        // 2. Draw map safe check
                        setTimeout(() => {
                            try {
                                this.renderLeafletMap();
                            } catch (mapErr) {
                                console.warn("Map render error:", mapErr);
                            }
                        }, 100);

                    } catch (e) {
                        console.error("Error loading dashboard data:", e);
                    } finally {
                        this.isLoading = false;
                    }
                },

                async updateComparison() {
                    if (!this.compare.targetCode) {
                        this.clearCompare();
                        return;
                    }
                    this.isLoading = true;
                    try {
                        await this.updateComparisonOnly();
                    } catch (e) {
                        console.error(e);
                    } finally {
                        this.isLoading = false;
                    }
                },

                async updateComparisonOnly() {
                    const baseCode = this.filters.provinsi;
                    const compareCode = this.compare.targetCode;
                    const url = `/api/compare/${this.metric}?tahun=${encodeURIComponent(this.filters.tahun)}&periode=${encodeURIComponent(this.filters.periode)}&base_region=${encodeURIComponent(baseCode)}&compare_with=${encodeURIComponent(compareCode)}`;
                    const res = await fetch(url);
                    if (!res.ok) throw new Error("Gagal mengambil perbandingan");
                    const data = await res.json();
                    
                    this.compare.active = true;
                    this.compare.data = data;
                    
                    // Render comparison line chart trend safely
                    try {
                        this.renderLineChart(data.lineChart);
                    } catch (chartErr) {
                        console.warn("Chart render error:", chartErr);
                    }
                },

                clearCompare() {
                    this.compare.active = false;
                    this.compare.targetCode = '';
                    this.compare.data = null;
                    this.updateDashboard();
                },

                renderCharts(lineData, barData) {
                    this.renderLineChart(lineData);
                    this.renderBarChart(barData);
                },

                renderLineChart(lineData) {
                    if (typeof Chart === 'undefined' || !lineData) return;
                    const canvas = document.getElementById('lineChartCanvas');
                    if (!canvas) return;

                    if (this.lineChartInstance) this.lineChartInstance.destroy();
                    
                    const lineCtx = canvas.getContext('2d');
                    const fontStyle = {
                        family: "'Plus Jakarta Sans', sans-serif",
                        size: 11
                    };

                    // Customize colors for comparison datasets
                    if (lineData.datasets && lineData.datasets.length > 1) {
                        lineData.datasets[0].borderColor = '#1F3C88';
                        lineData.datasets[0].backgroundColor = 'rgba(31, 60, 136, 0.05)';
                        lineData.datasets[0].fill = true;
                        lineData.datasets[0].tension = 0.3;
                        lineData.datasets[0].pointBackgroundColor = '#3FA9F5';
                        
                        lineData.datasets[1].borderColor = '#ef4444';
                        lineData.datasets[1].backgroundColor = 'rgba(239, 68, 68, 0.05)';
                        lineData.datasets[1].fill = true;
                        lineData.datasets[1].tension = 0.3;
                        lineData.datasets[1].pointBackgroundColor = '#dc2626';
                    }

                    this.lineChartInstance = new Chart(lineCtx, {
                        type: 'line',
                        data: lineData,
                        options: {
                            responsive: true,
                            plugins: {
                                legend: { labels: { font: fontStyle } }
                            },
                            scales: {
                                x: { grid: { display: false }, ticks: { font: fontStyle } },
                                y: { ticks: { font: fontStyle } }
                            }
                        }
                    });
                },

                renderBarChart(barData) {
                    if (typeof Chart === 'undefined' || !barData) return;
                    const canvas = document.getElementById('barChartCanvas');
                    if (!canvas) return;

                    if (this.barChartInstance) this.barChartInstance.destroy();
                    
                    const barCtx = canvas.getContext('2d');
                    const fontStyle = {
                        family: "'Plus Jakarta Sans', sans-serif",
                        size: 11
                    };

                    this.barChartInstance = new Chart(barCtx, {
                        type: 'bar',
                        data: barData,
                        options: {
                            responsive: true,
                            plugins: {
                                legend: { display: false }
                            },
                            scales: {
                                x: { grid: { display: false }, ticks: { font: fontStyle, maxRotation: 45, minRotation: 45 } },
                                y: { ticks: { font: fontStyle } }
                            }
                        }
                    });
                },

                async renderLeafletMap() {
                    if (typeof L === 'undefined') return;
                    const mapContainer = document.getElementById('leafletMap');
                    if (!mapContainer) return;

                    // Initialize Map if not exist
                    if (!this.leafletMapInstance) {
                        this.leafletMapInstance = L.map('leafletMap', {
                            center: [-2.5, 118], // Center of Indonesia
                            zoom: 5,
                            zoomControl: true
                        });

                        // Base tile layer
                        L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
                            subdomains: 'abcd',
                            maxZoom: 20
                        }).addTo(this.leafletMapInstance);
                    }

                    // Remove existing geojson layer
                    if (this.geoJsonLayer) {
                        this.leafletMapInstance.removeLayer(this.geoJsonLayer);
                        this.geoJsonLayer = null;
                    }

                    const selectedProvCode = this.filters.provinsi;
                    const dashboardRef = this;

                    // Fetch simplified geojson for Indonesia Provinces
                    try {
                        const geoUrl = 'https://raw.githubusercontent.com/superpikar/indonesia-geojson/master/indonesia-province.json';
                        const geoRes = await fetch(geoUrl);
                        const geoJson = await geoRes.json();

                        this.geoJsonLayer = L.geoJson(geoJson, {
                            style: (feature) => {
                                const provName = feature.properties.Propinsi || feature.properties.NAME_1 || "";
                                const code = getProvinceCodeByName(provName);
                                const value = code && this.mapData[code] ? parseFloat(this.mapData[code]) : 0;
                                const isSelected = selectedProvCode !== 'all' && code === selectedProvCode;

                                if (isSelected) {
                                    return {
                                        fillColor: this.getColorForChoropleth(value),
                                        weight: 3,
                                        opacity: 1,
                                        color: '#1e3a8a',
                                        fillOpacity: 0.95,
                                        dashArray: ''
                                    };
                                }

                                return {
                                    fillColor: this.getColorForChoropleth(value),
                                    weight: 1,
                                    opacity: 1,
                                    color: '#cbd5e1',
                                    fillOpacity: selectedProvCode !== 'all' ? 0.35 : 0.8
                                };
                            },
                            onEachFeature: (feature, layer) => {
                                const provName = feature.properties.Propinsi || feature.properties.NAME_1 || "Provinsi";
                                const code = getProvinceCodeByName(provName);
                                const value = code && dashboardRef.mapData[code] ? parseFloat(dashboardRef.mapData[code]) : null;
                                const isSelected = selectedProvCode !== 'all' && code === selectedProvCode;

                                const selectedTag = isSelected ? ' <span style="background:#1e3a8a;color:#fff;padding:1px 6px;border-radius:4px;font-size:10px;margin-left:4px;">AKTIF</span>' : '';
                                const valText = value !== null ? `<b>${value.toFixed(dashboardRef.metric === 'gini_ratio' ? 3 : 2)} ${dashboardRef.metricUnit()}</b>` : '<span style="color:#888;">Tidak ada data</span>';
                                layer.bindTooltip(`<div><strong>${provName}</strong>${selectedTag}<br/>Nilai: ${valText}</div>`, {
                                    sticky: true
                                });

                                // Store default style for mouseout restoration
                                const defaultStyle = isSelected
                                    ? { fillOpacity: 0.95, weight: 3, color: '#1e3a8a', dashArray: '' }
                                    : { fillOpacity: selectedProvCode !== 'all' ? 0.35 : 0.8, weight: 1, color: '#cbd5e1' };

                                layer.on('mouseover', function () {
                                    this.setStyle({
                                        fillOpacity: 1,
                                        weight: isSelected ? 4 : 2,
                                        color: isSelected ? '#1e40af' : '#64748b'
                                    });
                                    if (!L.Browser.ie && !L.Browser.opera && !L.Browser.edge) {
                                        this.bringToFront();
                                    }
                                });

                                layer.on('mouseout', function () {
                                    this.setStyle(defaultStyle);
                                    // Keep selected province on top
                                    if (isSelected) {
                                        this.bringToFront();
                                    }
                                });

                                layer.on('click', () => {
                                    if (code) {
                                        // If clicking the already-selected province, deselect it
                                        if (dashboardRef.filters.provinsi === code) {
                                            dashboardRef.filters.provinsi = 'all';
                                        } else {
                                            dashboardRef.filters.provinsi = code;
                                        }
                                        dashboardRef.updateDashboard();
                                    }
                                });

                                // Bring selected province to front after rendering
                                if (isSelected) {
                                    setTimeout(() => layer.bringToFront(), 50);
                                }
                            }
                        }).addTo(this.leafletMapInstance);

                        // If a province is selected, zoom to its bounds
                        if (selectedProvCode !== 'all' && this.geoJsonLayer) {
                            let selectedLayer = null;
                            this.geoJsonLayer.eachLayer((layer) => {
                                const provName = layer.feature.properties.Propinsi || layer.feature.properties.NAME_1 || "";
                                const code = getProvinceCodeByName(provName);
                                if (code === selectedProvCode) {
                                    selectedLayer = layer;
                                }
                            });
                            if (selectedLayer) {
                                this.leafletMapInstance.fitBounds(selectedLayer.getBounds(), { padding: [50, 50], maxZoom: 8 });
                            }
                        } else {
                            // Reset to default Indonesia view
                            this.leafletMapInstance.setView([-2.5, 118], 5);
                        }

                    } catch (err) {
                        console.error("Gagal memuat peta GeoJSON Indonesia", err);
                    }
                },

                getColorForChoropleth(val) {
                    if (!val || val === 0) return '#f1f5f9';
                    
                    if (this.metric === 'tpt') {
                        if (val >= 7) return '#b91c1c'; // Red
                        if (val >= 5) return '#f97316'; // Orange
                        if (val >= 3) return '#fbbf24'; // Yellow
                        return '#10b981'; // Green
                    }
                    if (this.metric === 'gini_ratio') {
                        if (val >= 0.40) return '#b91c1c';
                        if (val >= 0.37) return '#f97316';
                        if (val >= 0.32) return '#fbbf24';
                        return '#10b981';
                    }
                    if (this.metric === 'kemiskinan') {
                        if (val >= 18) return '#b91c1c';
                        if (val >= 12) return '#f97316';
                        if (val >= 7) return '#fbbf24';
                        return '#10b981';
                    }
                    if (this.metric === 'inflasi_tahunan' || this.metric === 'inflasi_bulanan') {
                        if (val >= 4.5) return '#b91c1c';
                        if (val >= 3) return '#f97316';
                        if (val >= 1.5) return '#fbbf24';
                        return '#10b981';
                    }
                    if (this.metric === 'pdb_growth') {
                        if (val >= 6) return '#15803d'; // Green
                        if (val >= 4) return '#22c55e'; // Light green
                        if (val >= 2) return '#eab308'; // Yellow
                        if (val >= 0) return '#f97316'; // Orange
                        return '#ef4444'; // Red
                    }
                    if (this.metric === 'ihpb') {
                        if (val >= 130) return '#1e3a8a'; // Dark blue
                        if (val >= 120) return '#2563eb'; // Blue
                        if (val >= 110) return '#3b82f6'; // Light blue
                        if (val >= 100) return '#60a5fa'; // Very light blue
                        return '#93c5fd';
                    }
                    return '#3b82f6'; // Standard blue fallback
                },

                filteredTableData() {
                    const query = this.tableSearchQuery.toLowerCase().trim();
                    if (!query) return this.tableData;
                    
                    return this.tableData.filter(row => 
                        row.nama.toLowerCase().includes(query) || 
                        row.kode.toLowerCase().includes(query)
                    );
                },

                getStatusClass(val) {
                    val = parseFloat(val);
                    if (isNaN(val)) return 'blue';

                    if (this.metric === 'tpt') {
                        if (val >= 6) return 'red';
                        if (val >= 4) return 'orange';
                        return 'green';
                    }
                    if (this.metric === 'gini_ratio') {
                        if (val >= 0.38) return 'red';
                        if (val >= 0.34) return 'orange';
                        return 'green';
                    }
                    if (this.metric === 'kemiskinan') {
                        if (val >= 12) return 'red';
                        if (val >= 8) return 'orange';
                        return 'green';
                    }
                    if (this.metric === 'inflasi_tahunan' || this.metric === 'inflasi_bulanan') {
                        if (val >= 4) return 'red';
                        if (val >= 2) return 'orange';
                        return 'green';
                    }
                    if (this.metric === 'pdb_growth') {
                        if (val >= 5) return 'green';
                        if (val >= 3) return 'orange';
                        return 'red';
                    }
                    return 'blue';
                },

                getStatusLabel(val) {
                    val = parseFloat(val);
                    if (isNaN(val)) return 'Data Makro';

                    if (this.metric === 'tpt') {
                        if (val >= 6) return 'Tinggi (Perhatian)';
                        if (val >= 4) return 'Sedang';
                        return 'Rendah (Baik)';
                    }
                    if (this.metric === 'gini_ratio') {
                        if (val >= 0.38) return 'Ketimpangan Tinggi';
                        if (val >= 0.34) return 'Ketimpangan Sedang';
                        return 'Ketimpangan Rendah';
                    }
                    if (this.metric === 'kemiskinan') {
                        if (val >= 12) return 'Kemiskinan Tinggi';
                        if (val >= 8) return 'Kemiskinan Sedang';
                        return 'Kemiskinan Rendah';
                    }
                    if (this.metric === 'inflasi_tahunan' || this.metric === 'inflasi_bulanan') {
                        if (val >= 4) return 'Inflasi Tinggi';
                        if (val >= 2) return 'Inflasi Terkendali';
                        return 'Inflasi Rendah';
                    }
                    if (this.metric === 'pdb_growth') {
                        if (val >= 5) return 'Pertumbuhan Kuat';
                        if (val >= 3) return 'Pertumbuhan Lambat';
                        return 'Kontraksi/Sangat Rendah';
                    }
                    return 'Stabil';
                },

                metricLabel() {
                    const labels = {
                        'tpt': 'Tingkat Pengangguran Terbuka',
                        'gini_ratio': 'Gini Ratio',
                        'kemiskinan': 'Persentase Penduduk Miskin (P0)',
                        'inflasi_tahunan': 'Inflasi Tahunan (YoY)',
                        'inflasi_bulanan': 'Inflasi Bulanan (MtM)',
                        'pdb_growth': 'Laju Pertumbuhan PDB',
                        'ihpb': 'Indeks Harga Perdagangan Besar'
                    };
                    return labels[this.metric] || this.metric;
                },

                metricUnit() {
                    const units = {
                        'tpt': '%',
                        'gini_ratio': '',
                        'kemiskinan': '%',
                        'inflasi_tahunan': '%',
                        'inflasi_bulanan': '%',
                        'pdb_growth': '%',
                        'ihpb': ''
                    };
                    return units[this.metric] || '';
                },

                getAcademicInsights() {
                    if (!this.lineChartInstance || !this.lineChartInstance.data || !this.lineChartInstance.data.datasets[0]) {
                        return "Memuat analisis akademik...";
                    }
                    const dataset = this.lineChartInstance.data.datasets[0].data;
                    const labels = this.lineChartInstance.data.labels;
                    if (dataset.length === 0) {
                        return "Data historis tidak mencukupi untuk melakukan analisis tren akademik.";
                    }

                    let peakVal = -Infinity;
                    let peakYear = '';
                    let troughVal = Infinity;
                    let troughYear = '';
                    let sum = 0;

                    for (let i = 0; i < dataset.length; i++) {
                        const val = dataset[i];
                        const year = labels[i];
                        if (val > peakVal) {
                            peakVal = val;
                            peakYear = year;
                        }
                        if (val < troughVal) {
                            troughVal = val;
                            troughYear = year;
                        }
                        sum += val;
                    }

                    const avg = sum / dataset.length;
                    const firstVal = dataset[0];
                    const lastVal = dataset[dataset.length - 1];
                    const totalDiff = lastVal - firstVal;
                    
                    // Determine overall trend
                    let longTrendStr = '';
                    if (Math.abs(totalDiff) < 0.05) {
                        longTrendStr = `cenderung stabil dengan pergerakan marjinal (${totalDiff > 0 ? '+' : ''}${totalDiff.toFixed(2)})`;
                    } else if (totalDiff > 0) {
                        longTrendStr = `mengalami peningkatan bersih sebesar ${totalDiff.toFixed(2)} poin`;
                    } else {
                        longTrendStr = `mengalami penurunan bersih sebesar ${Math.abs(totalDiff).toFixed(2)} poin`;
                    }

                    // Determine condition evaluation (is increase good or bad?)
                    let evaluationStr = '';
                    const metric = this.metric;
                    const isIncreaseBad = ['tpt', 'gini_ratio', 'kemiskinan', 'inflasi_tahunan', 'inflasi_bulanan'].includes(metric);
                    
                    if (totalDiff > 0) {
                        evaluationStr = isIncreaseBad 
                            ? "Peningkatan ini mengindikasikan adanya tekanan makroekonomi yang memerlukan intervensi kebijakan yang lebih ketat untuk meredam dampak sosial negatif."
                            : "Kenaikan ini menunjukkan performa ekonomi yang positif dan ekspansi sektor yang sehat, memperkuat ketahanan pasar domestik.";
                    } else {
                        evaluationStr = isIncreaseBad
                            ? "Penurunan ini mencerminkan perbaikan kondisi kesejahteraan sosial atau stabilitas harga yang sangat kondusif bagi pertumbuhan berkelanjutan."
                            : "Kemunduran ini perlu diantisipasi secara komprehensif guna mencegah terjadinya perlambatan ekonomi jangka panjang atau kontraksi struktural.";
                    }

                    const activeRegionName = this.filters.provinsi !== 'all' 
                        ? (this.tableData.find(r => r.kode === this.filters.provinsi)?.nama || "Wilayah Terpilih")
                        : "Nasional (Rata-rata)";

                    return `Berdasarkan analisis dataset historis untuk indikator <strong>${this.metricLabel()}</strong> pada wilayah <strong>${activeRegionName}</strong>, tercatat dinamika perkembangan yang penting untuk dikaji. Nilai puncak tertinggi (peak) terpantau pada tahun <strong>${peakYear}</strong> sebesar <strong>${peakVal.toFixed(2)} ${this.metricUnit()}</strong>, sedangkan titik terendah (trough) terjadi pada tahun <strong>${troughYear}</strong> dengan nilai <strong>${troughVal.toFixed(2)} ${this.metricUnit()}</strong>. Secara rata-rata historis lintas periode, nilai berada pada tingkat <strong>${avg.toFixed(2)} ${this.metricUnit()}</strong>. Dalam jangka panjang (dari tahun ${labels[0]} ke ${labels[labels.length-1]}), indikator ini <strong>${longTrendStr}</strong>. ${evaluationStr} Disarankan bagi pemangku kebijakan untuk terus memantau fluktuasi periodik ini guna menyelaraskan rencana pembangunan daerah dengan target makro nasional.`;
                },

                exportCSV() {
                    const data = this.filteredTableData();
                    if (data.length === 0) return alert('Tidak ada data untuk diekspor!');

                    let csvContent = "data:text/csv;charset=utf-8,";
                    // Headers
                    csvContent += "No,Nama Wilayah/Kategori,Kode,Tahun,Periode,Nilai,Sub Kategori\n";

                    data.forEach((row, index) => {
                        const line = `${index + 1},"${row.nama}",${row.kode},${row.tahun},"${row.periode}",${row.value},"${row.sub_kategori || ''}"`;
                        csvContent += line + "\n";
                    });

                    const encodedUri = encodeURI(csvContent);
                    const link = document.createElement("a");
                    link.setAttribute("href", encodedUri);
                    link.setAttribute("download", `BPS_Export_${this.metric}_${this.filters.tahun}_${this.filters.periode}.csv`);
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                }
            }
        }
    </script>
</body>
</html>
