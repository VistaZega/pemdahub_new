<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, viewport-fit=cover">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ isset($targetSchool) ? 'Live Monitoring Kehadiran ' . $targetSchool->name . ' – Perguruan PEMBDA' : 'Live Monitoring Kehadiran – Perguruan PEMBDA' }}</title>
    <meta name="description" content="Papan informasi kehadiran siswa dan guru Perguruan PEMBDA secara real-time.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        /* ============================================================
           RESET & BASE
        ============================================================ */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg-base:        #f3f4f6; /* Abu-abu terang bersih */
            --bg-panel:       #ffffff; /* Panel putih bersih */
            --bg-card:        #f9fafb; /* Card abu-abu sangat muda */
            --bg-card2:       #f3f4f6; /* Card abu-abu sekunder */
            --border:         #e5e7eb; /* Border abu-abu halus */
            --border-bright:  #cbd5e1; /* Border abu-abu lebih kontras */

            --text-primary:   #0f172a; /* Slate 900 gelap */
            --text-secondary: #475569; /* Slate 600 sedang */
            --text-dim:       #64748b; /* Slate 500 redup */

            --green:          #16a34a; /* Hijau terang */
            --green-dim:      #dcfce7; /* Hijau muda */
            --green-glow:     rgba(22,163,74,0.15);

            --yellow:         #d97706; /* Kuning/Amber */
            --yellow-dim:     #fef3c7; /* Kuning muda */
            --yellow-glow:    rgba(217,119,6,0.15);

            --blue:           #2563eb; /* Biru */
            --blue-dim:       #dbeafe; /* Biru muda */
            --blue-glow:      rgba(37,99,235,0.15);

            --red:            #dc2626; /* Merah */
            --red-dim:        #fee2e2; /* Merah muda */
            --red-glow:       rgba(220,38,38,0.15);

            --purple:         #7c3aed; /* Ungu */
            --purple-dim:     #f3e8ff; /* Ungu muda */

            --cyan:           #0891b2; /* Cyan */
            --cyan-dim:       #ecfeff; /* Cyan muda */
        }

        html, body {
            width: 100%; height: 100%;
            background: var(--bg-base);
            color: var(--text-primary);
            font-family: 'Inter', sans-serif;
            overflow: hidden;
            -webkit-font-smoothing: antialiased;
        }

        /* ============================================================
           LAYOUT UTAMA
        ============================================================ */
        .display-wrapper {
            display: grid;
            grid-template-rows: 76px 1fr;
            height: 100vh;
            padding: 10px;
            gap: 8px;
        }
        .display-wrapper > .header {
            grid-row: 1;
        }
        .display-wrapper > .body-grid {
            grid-row: 2;
        }

        /* ============================================================
           HEADER
        ============================================================ */
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: var(--bg-panel);
            border: 1px solid var(--border-bright);
            border-radius: 12px;
            padding: 0 20px;
            position: relative;
            overflow: hidden;
            height: 76px;
        }
        .header::before {
            content: '';
            position: absolute; inset: 0;
            background: linear-gradient(135deg, rgba(59,130,246,0.04) 0%, transparent 60%);
            pointer-events: none;
        }
        .header-left {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-shrink: 0;
        }
        .school-logo {
            width: 48px; height: 48px;
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .school-logo img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        .school-info h1 {
            font-size: 17px;
            font-weight: 800;
            color: var(--text-primary);
            letter-spacing: 0.02em;
            text-transform: uppercase;
            line-height: 1.15;
        }
        .school-info p {
            font-size: 11px;
            color: var(--text-secondary);
            letter-spacing: 0.05em;
            text-transform: uppercase;
            font-weight: 600;
        }
        .header-center {
            display: flex;
            align-items: center;
            justify-content: center;
            flex: 1;
            padding: 0 14px;
        }
        .header-right {
            display: flex;
            align-items: center;
            gap: 18px;
            text-align: right;
            flex-shrink: 0;
        }
        .header-meta {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            justify-content: center;
            gap: 2px;
        }
        .header-clock-group {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            justify-content: center;
        }
        .live-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(34,197,94,0.12);
            border: 1px solid rgba(34,197,94,0.35);
            border-radius: 20px;
            padding: 3px 10px;
            font-size: 10px;
            font-weight: 700;
            color: var(--green);
            letter-spacing: 0.1em;
            text-transform: uppercase;
            margin-bottom: 2px;
        }
        .live-dot {
            width: 6px; height: 6px;
            background: var(--green);
            border-radius: 50%;
            animation: pulse-dot 1.5s ease infinite;
        }
        @keyframes pulse-dot {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(0.7); }
        }
        .header-date {
            font-size: 12px;
            color: var(--text-secondary);
            font-weight: 600;
            letter-spacing: 0.02em;
            white-space: nowrap;
        }
        .clock {
            font-family: 'JetBrains Mono', monospace;
            font-size: 28px;
            font-weight: 700;
            color: var(--text-primary);
            letter-spacing: 0.04em;
            line-height: 1;
        }
        .clock-label {
            font-size: 9.5px;
            color: var(--text-secondary);
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-top: 2px;
            font-weight: 600;
            white-space: nowrap;
        }
        .header-mobile-meta {
            display: none; /* Disembunyikan di desktop */
        }

        /* ============================================================
           BODY LAYOUT: KIRI (Live Absensi) | KANAN (Live Rekapitulasi)
        ============================================================ */
        .body-grid {
            display: grid;
            grid-template-columns: 1fr 540px;
            gap: 10px;
            min-height: 0;
            height: 100%;
        }

        @media (max-width: 1440px) {
            .body-grid {
                grid-template-columns: 1fr 490px;
            }
        }

        @media (max-width: 1024px) {
            .body-grid {
                grid-template-columns: 1fr;
                overflow-y: auto;
            }
            html, body {
                overflow: auto;
            }
        }

        /* ============================================================
           MODE EXPANDED (FULL WIDTH / LAYAR PENUH)
        ============================================================ */
        body.is-feed-expanded .body-grid {
            grid-template-columns: 1fr !important;
        }
        body.is-feed-expanded .right-col {
            display: none !important;
        }
        body.is-feed-expanded .left-col {
            width: 100% !important;
        }
        body.is-feed-expanded #feed-count {
            display: none;
        }

        /* ============================================================
           KOLOM KIRI: LIVE ABSENSI FEED (FULL HEIGHT)
        ============================================================ */
        .left-col {
            display: flex;
            flex-direction: column;
            height: 100%;
            min-height: 0;
            overflow: hidden;
        }

        /* ============================================================
           FEED AKTIVITAS
        ============================================================ */
        .feed-panel {
            background: var(--bg-panel);
            border: 1px solid var(--border);
            border-radius: 14px;
            display: flex;
            flex-direction: column;
            min-height: 0;
            overflow: hidden;
        }
        .feed-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 20px 10px;
            border-bottom: 1px solid var(--border);
            flex-shrink: 0;
            background: var(--bg-panel);
            z-index: 10;
        }
        .feed-title {
            font-size: 13px;
            font-weight: 700;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }
        .feed-count {
            font-size: 11px;
            color: var(--text-dim);
        }

        /* ── KONTROL ZOOM AREA FEED ── */
        .feed-zoom-controls {
            display: flex;
            align-items: center;
            gap: 6px;
            background: #ffffff;
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 3px 10px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }
        .feed-zoom-btn {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: var(--bg-card);
            border: 1px solid var(--border-bright);
            color: var(--text-primary);
            font-size: 11px;
            font-weight: 900;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .feed-zoom-btn:hover {
            background: #2563eb;
            color: #ffffff;
            border-color: #2563eb;
        }
        .feed-zoom-btn:active {
            transform: scale(0.9);
        }
        .feed-zoom-label {
            font-family: 'JetBrains Mono', monospace;
            font-size: 11px;
            font-weight: 800;
            color: var(--text-secondary);
            min-width: 38px;
            text-align: center;
            cursor: pointer;
            user-select: none;
            padding: 2px 4px;
            border-radius: 4px;
        }
        .feed-zoom-label:hover {
            background: rgba(37,99,235,0.08);
            color: #2563eb;
        }

        /* ── HEADER ACTIONS & STATS DALAM FEED HEADER ── */
        .feed-header-left {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            min-width: 0;
        }
        .feed-header-right {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }

        /* ── TOTAL YANG ABSEN SAJA (MUNCUL HANYA SAAT DIPERLEBAR) ── */
        .expanded-stats-bar {
            display: none;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            animation: fadeIn 0.3s ease forwards;
        }
        body.is-feed-expanded .expanded-stats-bar {
            display: inline-flex;
        }
        .exp-stat-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.02em;
            box-shadow: 0 1px 2px rgba(0,0,0,0.04);
            white-space: nowrap;
        }
        .exp-stat-lbl {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            opacity: 0.85;
        }
        .exp-stat-val {
            font-family: 'JetBrains Mono', monospace;
            font-size: 13px;
            font-weight: 800;
        }
        .exp-stat-total {
            background: #0f172a;
            color: #ffffff;
            border: 1px solid #1e293b;
        }
        .exp-stat-total i {
            color: #4ade80;
        }
        .exp-stat-total .exp-stat-val {
            color: #4ade80;
            font-size: 14px;
        }
        .exp-stat-siswa {
            background: #eff6ff;
            color: #1e40af;
            border: 1px solid #bfdbfe;
        }
        .exp-stat-siswa i {
            color: #2563eb;
        }
        .exp-stat-pegawai {
            background: #faf5ff;
            color: #6b21a8;
            border: 1px solid #e9d5ff;
        }
        .exp-stat-pegawai i {
            color: #7c3aed;
        }
        .exp-stat-units {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .exp-pill-unit {
            padding: 3px 8px;
            font-size: 11px;
        }
        .exp-unit-code {
            font-weight: 800;
        }
        .unit-pill-smp {
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        .unit-pill-sma {
            background: #fffbeb;
            color: #92400e;
            border: 1px solid #fde68a;
        }
        .unit-pill-smk {
            background: #f5f3ff;
            color: #5b21b6;
            border: 1px solid #ddd6fe;
        }

        /* ── TOMBOL MELEBARKAN / MEMPERKECIL TAMPILAN FEED ── */
        .feed-expand-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #ffffff;
            border: 1px solid var(--border-bright);
            color: var(--text-primary);
            font-size: 11px;
            font-weight: 700;
            padding: 4px 12px;
            border-radius: 20px;
            cursor: pointer;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            user-select: none;
        }
        .feed-expand-btn:hover {
            background: #2563eb;
            color: #ffffff;
            border-color: #2563eb;
            transform: translateY(-1px);
            box-shadow: 0 2px 6px rgba(37,99,235,0.25);
        }
        .feed-expand-btn:active {
            transform: scale(0.96);
        }
        .feed-expand-btn.active {
            background: #0f172a;
            color: #ffffff;
            border-color: #0f172a;
        }
        .feed-expand-btn.active:hover {
            background: #dc2626;
            border-color: #dc2626;
            box-shadow: 0 2px 6px rgba(220,38,38,0.25);
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-3px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* ── WRAPPER ZOOM FEED ── */
        .feed-zoom-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 0;
            overflow: hidden;
            transform-origin: top left;
        }
        .feed-list {
            overflow-y: auto;
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 10px;
            padding: 14px;
            background: #ffffff;
            scroll-behavior: smooth;
        }
        .feed-list::-webkit-scrollbar {
            width: 6px;
        }
        .feed-list::-webkit-scrollbar-track {
            background: rgba(0,0,0,0.03);
            border-radius: 10px;
        }
        .feed-list::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }
        .feed-list::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* ── KARTU FEED (Kiosk Table Column Layout) ── */
        .feed-item {
            display: grid;
            grid-template-columns: 45px 50px 2fr 0.9fr 1.1fr 1.5fr 150px;
            align-items: center;
            padding: 10px 20px;
            border-radius: 14px;
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            color: #000000 !important;
            column-gap: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            animation: slide-in 0.4s ease forwards;
        }
        .feed-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
        }

        /* ── TABLE HEADER UNTUK FEED ── */
        .feed-table-header {
            display: grid;
            grid-template-columns: 45px 50px 2fr 0.9fr 1.1fr 1.5fr 150px;
            align-items: center;
            padding: 8px 34px;
            background: #f1f5f9;
            color: #475569;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            column-gap: 16px;
            border-bottom: 2px solid #cbd5e1;
            flex-shrink: 0;
        }

        /* ── BADGE CARA ABSEN ── */
        .feed-method-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 800;
            padding: 5px 12px;
            border-radius: 20px;
            letter-spacing: 0.02em;
            white-space: nowrap;
            width: fit-content;
        }
        .method-rfid {
            background: #eff6ff !important;
            color: #1d4ed8 !important;
            border: 1.5px solid #bfdbfe !important;
        }
        .method-qr {
            background: #faf5ff !important;
            color: #7c3aed !important;
            border: 1.5px solid #e9d5ff !important;
        }
        .method-mobile {
            background: #f0fdf4 !important;
            color: #15803d !important;
            border: 1.5px solid #bbf7d0 !important;
        }
        .method-manual {
            background: #fffbeb !important;
            color: #b45309 !important;
            border: 1.5px solid #fde68a !important;
        }
        .feed-item-newest .method-rfid,
        .feed-item-newest .method-qr,
        .feed-item-newest .method-mobile,
        .feed-item-newest .method-manual {
            background: rgba(255,255,255,0.95) !important;
            border-color: rgba(0,0,0,0.15) !important;
        }

        /* ── KARTU TERBARU (Baris Pertama / Blok Hijau) ── */
        .feed-item-newest {
            background: linear-gradient(135deg, #4ade80 0%, #22c55e 100%) !important;
            border: 2px solid #16a34a !important;
            box-shadow: 0 8px 24px -4px rgba(34, 197, 94, 0.5) !important;
            animation: newest-pulse 2s infinite alternate !important;
            padding: 12px 20px;
        }
        @keyframes newest-pulse {
            0% { box-shadow: 0 8px 24px -4px rgba(34, 197, 94, 0.5); }
            100% { box-shadow: 0 8px 28px 0px rgba(34, 197, 94, 0.65); }
        }

        /* ── Nomor Urut ── */
        .feed-num {
            font-family: 'JetBrains Mono', monospace;
            font-size: 19px;
            font-weight: 900;
            color: #94a3b8;
            text-align: center;
            line-height: 1;
        }
        .feed-item-newest .feed-num {
            color: rgba(0,0,0,0.45) !important;
        }

        /* ── Avatar ── */
        .feed-avatar-container {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            overflow: hidden;
            border: 2.5px solid #cbd5e1;
            background: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
            flex-shrink: 0;
        }
        .feed-avatar {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .feed-item-newest .feed-avatar-container {
            border-color: rgba(0,0,0,0.4) !important;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }

        /* ── Nama Lengkap ── */
        .feed-nama {
            font-size: 21px;
            font-weight: 900;
            color: #000000 !important;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* ── Waktu / Jam ── */
        .feed-time {
            font-family: 'JetBrains Mono', monospace;
            font-size: 16px;
            font-weight: 800;
            color: #000000 !important;
        }

        /* ── Status Badge ── */
        .feed-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 14px;
            font-weight: 900;
            padding: 6px 18px;
            border-radius: 30px;
            justify-content: center;
            letter-spacing: 0.03em;
            color: #000000 !important;
            border: 2.5px solid rgba(0,0,0,0.2) !important;
            background: rgba(255,255,255,0.75) !important;
            white-space: nowrap;
            width: 100%;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .feed-item-newest .feed-badge {
            background: rgba(0,0,0,0.1) !important;
            border-color: rgba(0,0,0,0.25) !important;
        }

        /* ── Kelas/Info ── */
        .feed-info {
            font-size: 18px;
            font-weight: 800;
            color: #000000 !important;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .feed-item-newest .feed-info {
            color: #000000 !important;
        }

        @keyframes slide-in {
            from { opacity: 0; transform: translateX(-16px); }
            to   { opacity: 1; transform: translateX(0); }
        }
        @keyframes blinker {
            50% { opacity: 0; }
        }

        /* ============================================================
           KOLOM KANAN: LIVE REKAPITULASI (3 UNIT SEKOLAH)
        ============================================================ */
        .right-col {
            display: flex;
            flex-direction: column;
            gap: 10px;
            min-height: 0;
            height: 100%;
            overflow: hidden;
        }

        .rekap-header-bar {
            background: var(--bg-panel);
            border: 1px solid var(--border-bright);
            border-radius: 14px;
            padding: 10px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-shrink: 0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        }
        .rekap-title-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .rekap-icon-box {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: #eff6ff;
            color: #2563eb;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            flex-shrink: 0;
        }
        .rekap-title {
            font-size: 13px;
            font-weight: 800;
            color: var(--text-primary);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            line-height: 1.2;
        }
        .rekap-subtitle {
            font-size: 10px;
            color: var(--text-dim);
            font-weight: 500;
        }
        .rekap-live-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(34,197,94,0.1);
            border: 1px solid rgba(34,197,94,0.3);
            border-radius: 20px;
            padding: 4px 10px;
            font-size: 10px;
            font-weight: 800;
            color: var(--green);
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .unit-panels-container {
            display: flex;
            flex-direction: column;
            gap: 8px;
            flex: 1;
            min-height: 0;
            overflow-y: auto;
            padding-right: 2px;
        }
        .unit-panels-container::-webkit-scrollbar {
            width: 4px;
        }
        .unit-panels-container::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        /* ── KARTU UNIT SEKOLAH INDIVIDUAL ── */
        .unit-card {
            background: var(--bg-panel);
            border-radius: 12px;
            padding: 8px 12px 9px;
            border: 1.5px solid var(--border);
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
            display: flex;
            flex-direction: column;
            gap: 6px;
            transition: all 0.2s ease;
        }
        .unit-card.unit-smp {
            border-color: #bfdbfe;
            border-left: 6px solid #2563eb;
        }
        .unit-card.unit-sma {
            border-color: #bbf7d0;
            border-left: 6px solid #16a34a;
        }
        .unit-card.unit-smk {
            border-color: #fed7aa;
            border-left: 6px solid #ea580c;
        }
        .unit-card.unit-yayasan {
            border-color: #fbcfe8;
            border-left: 6px solid #e11d48;
        }

        /* Header dalam unit card */
        .unit-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-bottom: 4px;
            border-bottom: 1px solid var(--border);
        }
        .unit-badge-name {
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 0;
        }
        .unit-type-pill {
            padding: 2px 7px;
            border-radius: 5px;
            font-size: 10px;
            font-weight: 900;
            letter-spacing: 0.04em;
            color: #ffffff;
            flex-shrink: 0;
        }
        .unit-smp .unit-type-pill { background: #2563eb; }
        .unit-sma .unit-type-pill { background: #16a34a; }
        .unit-smk .unit-type-pill { background: #ea580c; }
        .unit-yayasan .unit-type-pill { background: #e11d48; }

        .unit-school-name {
            font-size: 12px;
            font-weight: 900;
            color: var(--text-primary);
            text-transform: uppercase;
            letter-spacing: 0.02em;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* ── GRID 2 KOLOM (Siswa di Kiri, Guru di Kanan) ── */
        .unit-body-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        /* ── BLOCK SISWA (Aksen Segar: Background Hijau Muda / Mint, Text Hijau Tua) ── */
        .unit-block-siswa {
            background: #f0fdf4;
            border: 1.5px solid #bbf7d0;
            border-radius: 9px;
            padding: 5px 8px 6px;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .block-header-siswa {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .block-title-siswa {
            font-size: 10.5px;
            font-weight: 900;
            color: #15803d;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .badge-rate-siswa {
            font-size: 9.5px;
            font-weight: 800;
            padding: 1px 6px;
            border-radius: 10px;
            background: #dcfce7;
            color: #15803d;
            border: 1px solid #86efac;
        }

        /* ── BLOCK GURU & PEGAWAI (Aksen Berbeda: Background Ungu/Violet Muda, Text Ungu Tua) ── */
        .unit-block-pegawai {
            background: #faf5ff;
            border: 1.5px solid #e9d5ff;
            border-radius: 9px;
            padding: 5px 8px 6px;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .block-header-pegawai {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .block-title-pegawai {
            font-size: 10.5px;
            font-weight: 900;
            color: #7e22ce;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .badge-rate-pegawai {
            font-size: 9.5px;
            font-weight: 800;
            padding: 1px 6px;
            border-radius: 10px;
            background: #f3e8ff;
            color: #7e22ce;
            border: 1px solid #d8b4fe;
        }

        /* Grid Nilai Statistik 2x2 di dalam Blok */
        .stat-subgrid-2x2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4px;
        }
        .stat-box {
            background: #ffffff;
            border-radius: 6px;
            padding: 3px 4px 2px;
            text-align: center;
            box-shadow: 0 1px 2px rgba(0,0,0,0.03);
            border: 1px solid rgba(0,0,0,0.06);
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .stat-box-lbl {
            font-size: 8.5px;
            font-weight: 800;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            color: #64748b;
            line-height: 1.1;
            margin-bottom: 1px;
            white-space: nowrap;
        }
        .stat-box-val {
            font-family: 'JetBrains Mono', monospace;
            font-size: 15px;
            font-weight: 900;
            line-height: 1.1;
        }

        /* Nilai Statistik Siswa */
        .stat-val-siswa-total { color: #0f172a; }
        .stat-val-siswa-tap   { color: #15803d; }
        .stat-val-siswa-tepat { color: #0284c7; }
        .stat-val-siswa-lambat{ color: #dc2626; }

        /* Nilai Statistik Pegawai */
        .stat-val-peg-total   { color: #0f172a; }
        .stat-val-peg-tap     { color: #7e22ce; }
        .stat-val-peg-tepat   { color: #15803d; }
        .stat-val-peg-lambat  { color: #dc2626; }

        /* Progress Bars */
        .progress-bar-mini {
            height: 3px;
            background: rgba(0,0,0,0.06);
            border-radius: 3px;
            overflow: hidden;
            margin-top: 1px;
        }
        .progress-fill-mini {
            height: 100%;
            border-radius: 3px;
            transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* ── PANEL REKAPITULASI PER ROMBEL (Khusus Display Unit) ── */
        .rombel-panel {
            background: var(--bg-panel);
            border-radius: 12px;
            padding: 8px 12px;
            border: 1.5px solid var(--border);
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
            display: flex;
            flex-direction: column;
            gap: 6px;
            flex: 1;
            min-height: 0;
            overflow-y: auto;
        }
        .rombel-panel::-webkit-scrollbar {
            width: 4px;
        }
        .rombel-panel::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        .rombel-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-bottom: 4px;
            border-bottom: 1px solid var(--border);
        }
        .rombel-title {
            font-size: 11px;
            font-weight: 800;
            color: var(--text-primary);
            text-transform: uppercase;
            letter-spacing: 0.04em;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .rombel-total-badge {
            font-size: 9.5px;
            font-weight: 800;
            padding: 1px 6px;
            border-radius: 10px;
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #cbd5e1;
        }
        .rombel-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 6px;
        }
        .rombel-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 7px;
            padding: 4px 6px;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        .rombel-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .rombel-name {
            font-size: 11px;
            font-weight: 800;
            color: #0f172a;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .rombel-pct {
            font-family: 'JetBrains Mono', monospace;
            font-size: 10px;
            font-weight: 900;
            color: #15803d;
        }
        .rombel-numbers {
            display: flex;
            justify-content: space-between;
            font-size: 9px;
            color: #64748b;
            font-weight: 700;
        }

        /* Status update */
        .status-bar {
            background: var(--bg-card2);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 8px 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-shrink: 0;
        }
        .status-bar-text {
            font-size: 11px;
            color: var(--text-dim);
            font-weight: 600;
        }
        .status-bar-time {
            font-family: 'JetBrains Mono', monospace;
            font-size: 11px;
            color: var(--text-secondary);
            font-weight: 700;
        }

        /* ============================================================
           NOTIFIKASI POP-UP (scan baru)
        ============================================================ */
        .notif-wrapper {
            position: fixed;
            top: 110px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 9999;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            pointer-events: none;
            width: 90%;
            max-width: 650px;
        }
        .notif {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 16px;
            padding: 20px 24px;
            width: 100%;
            display: flex;
            align-items: center;
            gap: 20px;
            border: 2px solid var(--border-bright);
            border-left: 8px solid var(--green);
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25), 0 0 40px rgba(34,197,94,0.15);
            animation: notif-in 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
            transition: all 0.3s;
        }
        .notif.terlambat {
            border-left-color: var(--yellow);
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25), 0 0 40px rgba(217,119,6,0.15);
        }
        .notif.pulang {
            border-left-color: var(--blue);
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25), 0 0 40px rgba(37,99,235,0.15);
        }
        .notif-icon-circle {
            width: 54px;
            height: 54px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            flex-shrink: 0;
        }
        .notif.masuk .notif-icon-circle { background: var(--green-dim); color: var(--green); }
        .notif.terlambat .notif-icon-circle { background: var(--yellow-dim); color: var(--yellow); }
        .notif.pulang .notif-icon-circle { background: var(--blue-dim); color: var(--blue); }
        
        .notif-body {
            flex: 1;
            min-width: 0;
        }
        .notif-title-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 4px;
            flex-wrap: wrap;
        }
        .notif-nama {
            font-size: 22px;
            font-weight: 800;
            color: var(--text-primary);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 360px;
        }
        .notif-detail {
            font-size: 14px;
            color: var(--text-secondary);
            font-weight: 500;
        }
        .notif-status-badge {
            margin-left: auto;
            font-size: 14px;
            font-weight: 800;
            padding: 6px 14px;
            border-radius: 30px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .notif.masuk .notif-status-badge { background: var(--green-dim); color: var(--green); }
        .notif.terlambat .notif-status-badge { background: var(--yellow-dim); color: var(--yellow); }
        .notif.pulang .notif-status-badge { background: var(--blue-dim); color: var(--blue); }

        @keyframes notif-in {
            from { opacity: 0; transform: translateY(-40px) scale(0.9); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }
        @keyframes notif-out {
            to { opacity: 0; transform: translateY(-20px) scale(0.9); }
        }

        /* ============================================================
           ANIMASI GLOW UNTUK SCAN BARU
        ============================================================ */
        @keyframes glow-flash-masuk {
            0% { background-color: rgba(22, 163, 74, 0.35); box-shadow: inset 0 0 20px rgba(22, 163, 74, 0.4); }
            100% { background-color: transparent; }
        }
        @keyframes glow-flash-terlambat {
            0% { background-color: rgba(217, 119, 6, 0.35); box-shadow: inset 0 0 20px rgba(217, 119, 6, 0.4); }
            100% { background-color: transparent; }
        }
        @keyframes glow-flash-pulang {
            0% { background-color: rgba(37, 99, 235, 0.35); box-shadow: inset 0 0 20px rgba(37, 99, 235, 0.4); }
            100% { background-color: transparent; }
        }
        .glow-masuk { animation: glow-flash-masuk 4s cubic-bezier(0.25, 1, 0.5, 1) forwards; border-left: 6px solid var(--green) !important; }
        .glow-terlambat { animation: glow-flash-terlambat 4s cubic-bezier(0.25, 1, 0.5, 1) forwards; border-left: 6px solid var(--yellow) !important; }
        .glow-pulang { animation: glow-flash-pulang 4s cubic-bezier(0.25, 1, 0.5, 1) forwards; border-left: 6px solid var(--blue) !important; }

        /* ============================================================
           UNIT STYLING (SMP, SMK, PEGAWAI)
        ============================================================ */
        .unit-tag {
            display: inline-block;
            padding: 4px 8px;
            font-size: 11px;
            font-weight: 800;
            border-radius: 6px;
            margin-right: 8px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            line-height: 1.2;
            background: #475569 !important;
            color: #ffffff !important;
        }
        
        /* Siswa SMP (Biru Gelap Solid) */
        .siswa-smp .unit-tag { background: #1d4ed8 !important; }
        .siswa-smp .feed-nama, .siswa-smp .notif-nama { color: #0f172a !important; font-weight: 800; }
        
        /* Siswa SMK (Orange Gelap Solid) */
        .siswa-smk .unit-tag { background: #ea580c !important; }
        .siswa-smk .feed-nama, .siswa-smk .notif-nama { color: #0f172a !important; font-weight: 800; }
        
        /* Guru/Staf SMP (Ungu Gelap Solid) */
        .pegawai-smp .unit-tag { background: #7c3aed !important; }
        .pegawai-smp .feed-nama, .pegawai-smp .notif-nama { color: #0f172a !important; font-weight: 800; }
        
        /* Guru/Staf SMK (Teal Gelap Solid) */
        .pegawai-smk .unit-tag { background: #0d9488 !important; }
        .pegawai-smk .feed-nama, .pegawai-smk .notif-nama { color: #0f172a !important; font-weight: 800; }
        
        /* Staf Yayasan (Rose Gelap Solid) */
        .pegawai-yayasan .unit-tag { background: #e11d48 !important; }
        .pegawai-yayasan .feed-nama, .pegawai-yayasan .notif-nama { color: #0f172a !important; font-weight: 800; }

        /* Default / Fallback */
        .siswa-default .unit-tag, .pegawai-default .unit-tag { background: #475569 !important; }
        .siswa-default .feed-nama, .pegawai-default .feed-nama { color: #0f172a !important; font-weight: 800; }

        /* ============================================================
           LOADING / ERROR STATE
        ============================================================ */
        /* ============================================================
           LOADING / ERROR STATE (Premium Glassmorphic Dark)
        ============================================================ */
        .offline-overlay {
            display: none;
            position: fixed; inset: 0;
            background: rgba(15, 23, 42, 0.95); /* Deep dark background */
            backdrop-filter: blur(12px);
            z-index: 99999; /* Pastikan di atas notif popup */
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 20px;
            color: #ffffff;
            transition: all 0.5s ease;
        }
        .offline-overlay.show { display: flex; }
        
        .offline-box {
            background: rgba(30, 41, 59, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 40px 60px;
            border-radius: 24px;
            display: flex;
            flex-direction: column;
            align-items: center;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);
            max-width: 550px;
            text-align: center;
        }
        
        .offline-icon { 
            font-size: 64px; 
            margin-bottom: 10px;
            animation: pulse-signal 2s infinite ease-in-out;
        }
        
        @keyframes pulse-signal {
            0%, 100% { transform: scale(1); opacity: 0.6; filter: drop-shadow(0 0 5px rgba(239, 68, 68, 0.2)); }
            50% { transform: scale(1.08); opacity: 1; filter: drop-shadow(0 0 25px rgba(239, 68, 68, 0.7)); }
        }
        
        .offline-text { 
            font-size: 28px; 
            font-weight: 800; 
            color: #ef4444; /* Bright red */
            letter-spacing: 0.05em;
            text-transform: uppercase;
            margin-bottom: 6px;
        }
        
        .offline-sub { 
            font-size: 15px; 
            color: #94a3b8; 
            line-height: 1.5;
        }
        
        .offline-status-badge {
            margin-top: 15px;
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
            padding: 6px 16px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.05em;
        }

        /* ============================================================
           UNIT SWITCHER NAVIGATION BAR (SEMUA, SMP, SMA, SMK)
        ============================================================ */
        .unit-switcher-nav {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: #f1f5f9;
            padding: 4px;
            border-radius: 30px;
            border: 1.5px solid var(--border-bright);
            box-shadow: inset 0 1px 2px rgba(0,0,0,0.04);
            flex-shrink: 0;
        }
        .unit-switcher-nav::-webkit-scrollbar {
            display: none;
        }
        .unit-switch-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 14px;
            border-radius: 20px;
            background: transparent;
            border: 1px solid transparent;
            color: var(--text-secondary);
            font-size: 11.5px;
            font-weight: 800;
            text-decoration: none;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            white-space: nowrap;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            user-select: none;
        }
        .unit-switch-pill i {
            font-size: 12px;
            opacity: 0.85;
        }
        .unit-switch-pill:hover {
            background: #ffffff;
            color: #2563eb;
            border-color: #cbd5e1;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        }
        .unit-switch-pill.active {
            background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%);
            border-color: #1d4ed8;
            color: #ffffff;
            box-shadow: 0 2px 6px rgba(37,99,235,0.28);
        }
        .unit-switch-pill.active i {
            color: #bfdbfe;
            opacity: 1;
        }

        /* Container Pembungkus Tab & Stats Mobile (Disembunyikan di Desktop) */
        .mobile-top-bar {
            display: none !important;
        }

        /* Penyesuaian Responsif untuk Layar Laptop Sedang (901px - 1150px) */
        @media (min-width: 901px) and (max-width: 1150px) {
            .header {
                padding: 0 14px;
            }
            .header-left {
                gap: 8px;
            }
            .school-logo {
                width: 40px;
                height: 40px;
            }
            .school-info h1 {
                font-size: 14px;
            }
            .school-info p {
                font-size: 9.5px;
            }
            .unit-switch-pill {
                padding: 4px 10px;
                font-size: 10.5px;
                gap: 4px;
            }
            .clock {
                font-size: 24px;
            }
            .header-right {
                gap: 12px;
            }
        }

        /* ============================================================
           MOBILE VIEW TABS (LIVE ABSENSI vs REKAPITULASI)
        ============================================================ */
        .mobile-view-tabs {
            display: none; /* Desktop default: disembunyikan */
            align-items: center;
            background: #e2e8f0;
            padding: 3px;
            border-radius: 12px;
            gap: 4px;
            flex-shrink: 0;
        }
        .view-tab-btn {
            flex: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 9px 14px;
            border-radius: 9px;
            border: none;
            background: transparent;
            color: #475569;
            font-size: 13px;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.2s ease;
            white-space: nowrap;
            font-family: inherit;
        }
        .view-tab-btn.active {
            background: #ffffff;
            color: #0f172a;
            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
        }
        .view-tab-btn.active i {
            color: #2563eb;
        }

        /* ============================================================
           MOBILE STATS STRIP (TOTAL HADIR, SISWA, GURU, TERLAMBAT)
        ============================================================ */
        .mobile-stats-strip {
            display: none; /* Desktop default: disembunyikan */
            grid-template-columns: repeat(4, 1fr);
            gap: 6px;
            flex-shrink: 0;
        }
        .m-stat-card {
            background: #ffffff;
            border-radius: 10px;
            padding: 8px 6px 7px;
            text-align: center;
            border: 1.5px solid var(--border);
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 2px;
        }
        .m-stat-label {
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            color: #64748b;
            display: flex;
            align-items: center;
            gap: 4px;
            white-space: nowrap;
        }
        .m-stat-value {
            font-family: 'JetBrains Mono', monospace;
            font-size: 18px;
            font-weight: 900;
            line-height: 1;
        }
        .m-stat-total { border-color: #cbd5e1; }
        .m-stat-total .m-stat-value { color: #0f172a; }
        .m-stat-siswa { border-color: #bfdbfe; background: #f0fdf4; }
        .m-stat-siswa .m-stat-value { color: #16a34a; }
        .m-stat-guru { border-color: #e9d5ff; background: #faf5ff; }
        .m-stat-guru .m-stat-value { color: #7c3aed; }
        .m-stat-lambat { border-color: #fed7aa; background: #fffbeb; }
        .m-stat-lambat .m-stat-value { color: #ea580c; }

        /* ============================================================
           MOBILE CONTROLS CONTAINER (SEARCH + FILTER CHIPS + ACTIONS)
        ============================================================ */
        .mobile-controls-container {
            display: none; /* Desktop default: disembunyikan */
            flex-direction: column;
            gap: 8px;
            padding: 10px 12px;
            background: #f8fafc;
            border-bottom: 1px solid var(--border);
            flex-shrink: 0;
        }
        .mobile-controls-bar {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .mobile-search-wrap {
            flex: 1;
            position: relative;
            display: flex;
            align-items: center;
        }
        .mobile-search-wrap .search-icon {
            position: absolute;
            left: 11px;
            color: #94a3b8;
            font-size: 13px;
            pointer-events: none;
        }
        .mobile-search-wrap input {
            width: 100%;
            padding: 8px 30px 8px 32px;
            border-radius: 20px;
            border: 1.5px solid var(--border-bright);
            background: #ffffff;
            font-family: inherit;
            font-size: 13px;
            color: #0f172a;
            outline: none;
            transition: all 0.2s ease;
        }
        .mobile-search-wrap input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37,99,235,0.12);
        }
        .mobile-search-wrap .search-clear-btn {
            position: absolute;
            right: 8px;
            background: #e2e8f0;
            border: none;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #475569;
            font-size: 10px;
            cursor: pointer;
        }
        .mobile-action-btns {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-shrink: 0;
        }
        .mobile-ctrl-btn {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border: 1.5px solid var(--border-bright);
            background: #ffffff;
            color: var(--text-secondary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            cursor: pointer;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
            transition: all 0.15s ease;
        }
        .mobile-ctrl-btn:hover {
            border-color: #2563eb;
            color: #2563eb;
        }
        .mobile-ctrl-btn:active {
            transform: scale(0.92);
        }
        .mobile-ctrl-btn.muted {
            background: #fef2f2;
            border-color: #fecaca;
            color: #dc2626;
        }

        .mobile-filter-chips-scroll {
            display: flex;
            align-items: center;
            gap: 6px;
            overflow-x: auto;
            scrollbar-width: none;
            -ms-overflow-style: none;
            padding-bottom: 2px;
        }
        .mobile-filter-chips-scroll::-webkit-scrollbar {
            display: none;
        }
        .filter-chip {
            padding: 5px 12px;
            border-radius: 16px;
            border: 1px solid var(--border-bright);
            background: #ffffff;
            color: var(--text-secondary);
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.15s ease;
            font-family: inherit;
        }
        .filter-chip:hover {
            border-color: #2563eb;
            color: #2563eb;
        }
        .filter-chip.active {
            background: #0f172a;
            border-color: #0f172a;
            color: #ffffff;
        }

        /* ============================================================
           MOBILE FEED CARD STYLING (.mobile-feed-card)
        ============================================================ */
        .mobile-feed-card {
            background: #ffffff;
            border: 1px solid var(--border-bright);
            border-radius: 14px;
            padding: 12px;
            display: flex;
            flex-direction: column;
            gap: 9px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
            position: relative;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            animation: slide-in 0.3s ease forwards;
        }
        .mobile-feed-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        .mobile-feed-card.feed-item-newest {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%) !important;
            border: 2px solid #22c55e !important;
            box-shadow: 0 6px 18px rgba(34, 197, 94, 0.25) !important;
        }

        .mfc-top {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }
        .mfc-avatar-container {
            position: relative;
            width: 44px;
            height: 44px;
            flex-shrink: 0;
        }
        .mfc-avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            object-fit: cover;
            background: #e2e8f0;
            border: 2px solid #cbd5e1;
            display: block;
        }
        .mfc-seq {
            position: absolute;
            bottom: -3px;
            right: -3px;
            background: #0f172a;
            color: #ffffff;
            font-family: 'JetBrains Mono', monospace;
            font-size: 9px;
            font-weight: 900;
            padding: 1px 5px;
            border-radius: 6px;
            line-height: 1.1;
            border: 1.5px solid #ffffff;
            box-shadow: 0 1px 2px rgba(0,0,0,0.2);
        }
        .feed-item-newest .mfc-seq {
            background: #15803d;
        }

        .mfc-info-group {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 3px;
        }
        .mfc-name-row {
            display: flex;
            align-items: center;
            gap: 6px;
            min-width: 0;
        }
        .mfc-name {
            font-size: 14px;
            font-weight: 800;
            color: #0f172a;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.2;
            margin: 0;
        }
        .mfc-tags-row {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }
        .mfc-unit-tag {
            font-size: 9.5px;
            font-weight: 800;
            padding: 2px 6px;
            border-radius: 5px;
            text-transform: uppercase;
            color: #ffffff;
            line-height: 1.1;
            white-space: nowrap;
            background: #475569;
        }
        .mfc-class-tag {
            font-size: 11px;
            font-weight: 700;
            color: #475569;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .mfc-status-group {
            flex-shrink: 0;
        }
        .mfc-status-pill {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.02em;
            white-space: nowrap;
        }
        .mfc-status-pill.status-masuk {
            background: #dcfce7;
            color: #15803d;
            border: 1px solid #86efac;
        }
        .mfc-status-pill.status-terlambat {
            background: #fef3c7;
            color: #b45309;
            border: 1px solid #fde68a;
        }
        .mfc-status-pill.status-pulang {
            background: #dbeafe;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
        }

        .mfc-bottom {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            padding-top: 8px;
            border-top: 1px dashed #e2e8f0;
            flex-wrap: wrap;
        }
        .mfc-times {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .mfc-time-badge {
            font-family: 'JetBrains Mono', monospace;
            font-size: 11px;
            font-weight: 800;
            padding: 2px 7px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            white-space: nowrap;
        }
        .mfc-time-badge.time-in {
            background: #f0fdf4;
            color: #16a34a;
            border: 1px solid #bbf7d0;
        }
        .mfc-time-badge.time-out {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }
        .mfc-method {
            margin-left: auto;
        }
        .mfc-method-badge {
            font-size: 10px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            white-space: nowrap;
        }

        /* Warna tag unit khusus mobile cards */
        .siswa-smp .mfc-unit-tag { background: #1d4ed8 !important; }
        .siswa-sma .mfc-unit-tag { background: #16a34a !important; }
        .siswa-smk .mfc-unit-tag { background: #ea580c !important; }
        .pegawai-smp .mfc-unit-tag { background: #7c3aed !important; }
        .pegawai-sma .mfc-unit-tag { background: #0284c7 !important; }
        .pegawai-smk .mfc-unit-tag { background: #0d9488 !important; }
        .pegawai-yayasan .mfc-unit-tag { background: #e11d48 !important; }

        /* ============================================================
           MEDIA QUERIES: LAYAR MOBILE (< 900px) ATAU .is-mobile-device
        ============================================================ */
        @media (max-width: 900px), (pointer: coarse) and (max-width: 1024px) {
            html, body {
                overflow-x: hidden !important;
                overflow-y: auto !important;
                height: auto !important;
                min-height: 100vh !important;
                -webkit-overflow-scrolling: touch !important;
            }

            .display-wrapper {
                height: auto !important;
                min-height: 100vh !important;
                grid-template-rows: auto !important;
                display: flex !important;
                flex-direction: column !important;
                padding: 8px 8px 24px !important;
                gap: 8px !important;
            }
            .display-wrapper > .header,
            .display-wrapper > .body-grid {
                grid-row: auto !important;
            }

            /* Header Mobile */
            .header {
                padding: 10px 12px !important;
                gap: 8px !important;
                flex-wrap: wrap !important;
                height: auto !important;
                min-height: auto !important;
                display: flex !important;
                align-items: center !important;
                justify-content: space-between !important;
            }
            .header-left {
                gap: 8px !important;
                min-width: 0 !important;
                flex: 1 !important;
            }
            .school-logo {
                width: 36px !important;
                height: 36px !important;
            }
            .school-info h1 {
                font-size: 13px !important;
                line-height: 1.2 !important;
            }
            .school-info p {
                font-size: 9.5px !important;
            }
            .header-right {
                flex-shrink: 0 !important;
                gap: 0 !important;
            }
            .header-meta {
                display: none !important;
            }
            .header-clock-group {
                align-items: flex-end !important;
            }
            .clock {
                font-size: 20px !important;
            }
            .clock-label {
                display: none !important;
            }

            /* Unit Switcher Mobile */
            .header-center {
                order: 2 !important;
                width: 100% !important;
                padding: 0 !important;
                margin: 2px 0 !important;
                display: block !important;
            }
            .unit-switcher-nav {
                width: 100% !important;
                display: flex !important;
                justify-content: space-between !important;
                gap: 5px !important;
                padding: 3px !important;
                background: #f1f5f9 !important;
                border-radius: 12px !important;
                overflow-x: auto !important;
                box-shadow: none !important;
            }
            .unit-switch-pill {
                flex: 1 !important;
                justify-content: center !important;
                padding: 6px 4px !important;
                font-size: 11px !important;
                border-radius: 8px !important;
                gap: 4px !important;
            }
            .unit-switch-pill span {
                font-size: 10.5px !important;
            }
            .unit-switch-pill i {
                font-size: 11px !important;
            }

            /* Mobile Meta (Live + Date) */
            .header-mobile-meta {
                order: 3 !important;
                width: 100% !important;
                display: flex !important;
                justify-content: space-between !important;
                align-items: center !important;
                border-top: 1px solid var(--border) !important;
                padding-top: 6px !important;
                margin-top: 2px !important;
            }
            .header-mobile-meta .live-badge {
                margin-bottom: 0 !important;
            }
            .header-mobile-meta .header-date {
                font-size: 11px !important;
            }

            /* Tampilkan elemen mobile */
            .mobile-top-bar {
                display: flex !important;
                flex-direction: column !important;
                gap: 8px !important;
                flex-shrink: 0 !important;
            }

            /* Tampilkan elemen mobile */
            .mobile-view-tabs {
                display: flex !important;
            }
            .mobile-stats-strip {
                display: grid !important;
            }
            .mobile-controls-container {
                display: flex !important;
            }

            /* Sembunyikan kontrol desktop */
            .feed-zoom-controls {
                display: none !important;
            }
            .feed-expand-btn {
                display: none !important;
            }
            .feed-table-header {
                display: none !important;
            }

            /* Feed Panel Mobile */
            .body-grid {
                display: flex !important;
                flex-direction: column !important;
                grid-template-columns: 1fr !important;
                height: auto !important;
                min-height: auto !important;
                overflow: visible !important;
                gap: 8px !important;
            }
            .left-col {
                height: auto !important;
                min-height: auto !important;
                overflow: visible !important;
                width: 100% !important;
            }
            .feed-panel {
                height: auto !important;
                min-height: auto !important;
                overflow: visible !important;
            }
            .feed-header {
                padding: 10px 12px 8px !important;
            }
            .feed-title {
                font-size: 12px !important;
            }
            .feed-count {
                font-size: 10px !important;
            }
            .feed-zoom-wrapper {
                height: auto !important;
                min-height: auto !important;
                overflow: visible !important;
                zoom: 1 !important;
            }
            .feed-list {
                height: auto !important;
                min-height: auto !important;
                max-height: none !important;
                overflow: visible !important;
                padding: 8px !important;
                gap: 8px !important;
            }

            /* LOGIKA REKAPITULASI PADA MOBILE:
               Secara default DIHILANGKAN sesuai permintaan pengguna.
               Hanya muncul jika tab [Rekapitulasi] diaktifkan (.show-rekap-view). */
            body:not(.show-rekap-view) .right-col {
                display: none !important;
            }
            body.show-rekap-view .left-col {
                display: none !important;
            }
            body.show-rekap-view .right-col {
                display: flex !important;
                width: 100% !important;
                height: auto !important;
                min-height: auto !important;
                overflow: visible !important;
            }
            .unit-panels-container {
                overflow: visible !important;
                height: auto !important;
                max-height: none !important;
            }
            .unit-card {
                padding: 10px !important;
            }
            .unit-body-grid {
                grid-template-columns: 1fr !important;
                gap: 8px !important;
            }
            .rombel-grid {
                grid-template-columns: 1fr !important;
            }

            /* Notifikasi Pop-up Toast di Layar HP */
            .notif-wrapper {
                top: 10px !important;
                width: 94% !important;
                max-width: 420px !important;
            }
            .notif {
                padding: 10px 12px !important;
                gap: 10px !important;
                border-radius: 12px !important;
                box-shadow: 0 10px 25px -5px rgba(0,0,0,0.25) !important;
            }
            .notif-icon-circle {
                width: 38px !important;
                height: 38px !important;
                font-size: 18px !important;
            }
            .notif-nama {
                font-size: 15px !important;
                max-width: 180px !important;
            }
            .notif-detail {
                font-size: 11px !important;
            }
            .notif-status-badge {
                font-size: 10.5px !important;
                padding: 4px 8px !important;
            }
        }
    </style>
</head>
<body class="{{ (!empty($isMobile)) ? 'is-mobile-device' : '' }}">

<div class="display-wrapper">

    <!-- ── HEADER ─────────────────────────────────────────────── -->
    <header class="header">
        <div class="header-left">
            <div class="school-logo">
                <img src="{{ isset($targetSchool) && $targetSchool->logo_url ? $targetSchool->logo_url : asset('images/logo-pembda.png') }}" alt="Logo">
            </div>
            <div class="school-info">
                <h1>{{ isset($targetSchool) ? $targetSchool->name : 'Perguruan PEMBDA' }}</h1>
                <p>{{ isset($targetSchool) ? 'Live Monitoring Kehadiran Unit ' . $targetSchool->type . ' Real-Time' : 'Sistem Monitoring Kehadiran Real-Time' }}</p>
            </div>
        </div>

        <div class="header-center">
            <!-- ── UNIT SWITCHER BAR (SEMUA, SMP, SMA, SMK) ── -->
            <nav class="unit-switcher-nav" aria-label="Pilih Unit Sekolah">
                <a href="{{ route('display.index') }}" class="unit-switch-pill {{ empty($unitNumber) ? 'active' : '' }}" title="Tampilkan Semua Unit">
                    <i class="fa-solid fa-layer-group"></i> <span>SEMUA</span>
                </a>
                <a href="{{ route('display.unit1') }}" class="unit-switch-pill {{ ($unitNumber == 1) ? 'active' : '' }}" title="Unit SMP (SMPS Pembda 2)">
                    <i class="fa-solid fa-school"></i> <span>SMP</span>
                </a>
                <a href="{{ route('display.unit2') }}" class="unit-switch-pill {{ ($unitNumber == 2) ? 'active' : '' }}" title="Unit SMA (SMAS Pembda 1)">
                    <i class="fa-solid fa-building-columns"></i> <span>SMA</span>
                </a>
                <a href="{{ route('display.unit3') }}" class="unit-switch-pill {{ ($unitNumber == 3) ? 'active' : '' }}" title="Unit SMK (SMKS Pembda)">
                    <i class="fa-solid fa-graduation-cap"></i> <span>SMK</span>
                </a>
            </nav>
        </div>

        <div class="header-right">
            <div class="header-meta">
                <div class="live-badge">
                    <span class="live-dot"></span>
                    LIVE
                </div>
                <div class="header-date" id="header-date">Memuat...</div>
            </div>
            <div class="header-clock-group">
                <div class="clock" id="clock">--:--:--</div>
                <div class="clock-label">Waktu Saat Ini</div>
            </div>
        </div>

        <!-- Mobile-only meta row: Live badge & Tanggal (Muncul hanya di layar HP) -->
        <div class="header-mobile-meta">
            <div class="live-badge">
                <span class="live-dot"></span>
                LIVE
            </div>
            <div class="header-date" id="header-date-mobile">Memuat...</div>
        </div>
    </header>

    <!-- ── CONTAINER KHUSUS MOBILE: TABS & STATS STRIP (Disembunyikan total di Desktop) ── -->
    <div class="mobile-top-bar" id="mobile-top-bar">
        <!-- ── MOBILE VIEW TABS (TOGGLE LIVE ABSENSI vs REKAPITULASI) ── -->
        <div class="mobile-view-tabs" id="mobile-view-tabs">
            <button type="button" class="view-tab-btn active" id="tab-btn-feed" onclick="switchMobileView('feed')">
                <i class="fa-solid fa-bolt"></i> Live Absensi
            </button>
            <button type="button" class="view-tab-btn" id="tab-btn-rekap" onclick="switchMobileView('rekap')">
                <i class="fa-solid fa-chart-pie"></i> Rekapitulasi
            </button>
        </div>

        <!-- ── MOBILE SUMMARY STATS STRIP ── -->
        <div class="mobile-stats-strip" id="mobile-stats-strip">
            <div class="m-stat-card m-stat-total" title="Total Siswa & Pegawai Hadir Hari Ini">
                <div class="m-stat-label"><i class="fa-solid fa-users"></i> Hadir</div>
                <div class="m-stat-value" id="m-stat-total">0</div>
            </div>
            <div class="m-stat-card m-stat-siswa" title="Siswa Hadir Hari Ini">
                <div class="m-stat-label"><i class="fa-solid fa-user-graduate"></i> Siswa</div>
                <div class="m-stat-value" id="m-stat-siswa">0</div>
            </div>
            <div class="m-stat-card m-stat-guru" title="Guru & Pegawai Hadir Hari Ini">
                <div class="m-stat-label"><i class="fa-solid fa-chalkboard-user"></i> Guru/Staf</div>
                <div class="m-stat-value" id="m-stat-guru">0</div>
            </div>
            <div class="m-stat-card m-stat-lambat" title="Total Hadir Terlambat Hari Ini">
                <div class="m-stat-label"><i class="fa-solid fa-clock"></i> Lambat</div>
                <div class="m-stat-value" id="m-stat-lambat">0</div>
            </div>
        </div>
    </div>

    <!-- ── BODY: 2 KOLOM (Kiri: Live Absensi, Kanan: Live Rekapitulasi) ── -->
    <div class="body-grid">

        <!-- KOLOM KIRI: LIVE FEED ABSENSI (FULL HEIGHT / FULL WIDTH DI HP) -->
        <div class="left-col">
            <div class="feed-panel">
                <div class="feed-header">
                    <div class="feed-header-left">
                        <span class="feed-title">⚡ Live Absensi {{ isset($targetSchool) ? $targetSchool->type : 'Real-Time' }}</span>
                        <span class="feed-count" id="feed-count">–</span>

                        <!-- Total Absen Saat Tampilan Diperlebar (Hanya muncul saat diperlebar) -->
                        <div class="expanded-stats-bar" id="expanded-stats-bar">
                            <div class="exp-stat-pill exp-stat-total" title="Total Seluruh Kehadiran Hari Ini">
                                <i class="fa-solid fa-users"></i>
                                <span class="exp-stat-lbl">Total Absen:</span>
                                <span class="exp-stat-val" id="exp-total-all">0</span>
                            </div>
                            <div class="exp-stat-pill exp-stat-siswa" title="Total Siswa Hadir">
                                <i class="fa-solid fa-user-graduate"></i>
                                <span class="exp-stat-lbl">Siswa:</span>
                                <span class="exp-stat-val" id="exp-total-siswa">0</span>
                            </div>
                            <div class="exp-stat-pill exp-stat-pegawai" title="Total Guru & Pegawai Hadir">
                                <i class="fa-solid fa-chalkboard-user"></i>
                                <span class="exp-stat-lbl">Guru/Staf:</span>
                                <span class="exp-stat-val" id="exp-total-pegawai">0</span>
                            </div>
                            <div class="exp-stat-units" id="exp-stat-units"></div>
                        </div>
                    </div>

                    <div class="feed-header-right">
                        <!-- Kontrol Zoom Khusus Area Tabel Feed -->
                        <div class="feed-zoom-controls" title="Zoom & Scroll khusus area tabel ini">
                            <button type="button" class="feed-zoom-btn" onclick="zoomFeed(-0.1)" title="Perkecil Teks / Zoom Out (Ctrl + Scroll Down)">
                                <i class="fa-solid fa-minus"></i>
                            </button>
                            <span class="feed-zoom-label" id="feed-zoom-val" onclick="resetFeedZoom()" title="Klik untuk reset zoom ke 100%">100%</span>
                            <button type="button" class="feed-zoom-btn" onclick="zoomFeed(0.1)" title="Perbesar Teks / Zoom In (Ctrl + Scroll Up)">
                                <i class="fa-solid fa-plus"></i>
                            </button>
                        </div>

                        <!-- Tombol Melebarkan / Memperkecil Tampilan Feed -->
                        <button type="button" class="feed-expand-btn" id="feed-expand-btn" onclick="toggleExpandFeed()" title="Lebarkan Tampilan Live Absensi ke Layar Penuh">
                            <i class="fa-solid fa-expand" id="feed-expand-icon"></i>
                            <span id="feed-expand-label">Lebarkan</span>
                        </button>
                    </div>
                </div>

                <!-- MOBILE CONTROLS & FILTER BAR -->
                <div class="mobile-controls-container" id="mobile-controls-container">
                    <div class="mobile-controls-bar">
                        <div class="mobile-search-wrap">
                            <i class="fa-solid fa-magnifying-glass search-icon"></i>
                            <input type="text" id="mobile-search-input" placeholder="Cari nama siswa, guru, kelas..." oninput="handleSearchInput(this.value)" autocomplete="off">
                            <button type="button" class="search-clear-btn" id="search-clear-btn" onclick="clearSearch()" style="display:none;" title="Hapus pencarian">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                        <div class="mobile-action-btns">
                            <button type="button" class="mobile-ctrl-btn" id="btn-sound-toggle" onclick="toggleMuteSound()" title="Aktifkan/Bisukan Suara Notifikasi">
                                <i class="fa-solid fa-volume-high" id="sound-icon"></i>
                            </button>
                            <button type="button" class="mobile-ctrl-btn" id="btn-refresh-manual" onclick="manualRefresh()" title="Refresh Data Sekarang">
                                <i class="fa-solid fa-rotate" id="refresh-icon"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mobile-filter-chips-scroll" id="mobile-filter-chips">
                        <button type="button" class="filter-chip active" data-filter="all" onclick="setStatusFilter('all', this)">Semua</button>
                        <button type="button" class="filter-chip" data-filter="masuk" onclick="setStatusFilter('masuk', this)">Hadir Tepat</button>
                        <button type="button" class="filter-chip" data-filter="terlambat" onclick="setStatusFilter('terlambat', this)">Terlambat</button>
                        <button type="button" class="filter-chip" data-filter="pulang" onclick="setStatusFilter('pulang', this)">Pulang</button>
                        <button type="button" class="filter-chip" data-filter="siswa" onclick="setStatusFilter('siswa', this)">Siswa</button>
                        <button type="button" class="filter-chip" data-filter="pegawai" onclick="setStatusFilter('pegawai', this)">Guru/Staf</button>
                    </div>
                </div>

                <!-- Zoom Container: Hanya area tabel & data feed ini yang terkena Zoom / Scale -->
                <div class="feed-zoom-wrapper" id="feed-zoom-container">
                    <!-- Table Header Kolom -->
                    <div class="feed-table-header">
                        <span>NO</span>
                        <span>FOTO</span>
                        <span>NAMA &amp; UNIT</span>
                        <span>KELAS / INFO</span>
                        <span>WAKTU (IN/OUT)</span>
                        <span>CARA ABSEN</span>
                        <span style="text-align:center;">STATUS</span>
                    </div>
                    <div class="feed-list" id="feed-list">
                        <div style="padding:20px;text-align:center;color:var(--text-dim);font-size:14px;">
                            Memuat data absensi real-time...
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- KOLOM KANAN: LIVE REKAPITULASI -->
        <div class="right-col">
            <!-- Header Bar Rekapitulasi -->
            <div class="rekap-header-bar">
                <div class="rekap-title-group">
                    <div class="rekap-icon-box">
                        <i class="fa-solid fa-chart-pie"></i>
                    </div>
                    <div>
                        <div class="rekap-title">{{ isset($targetSchool) ? 'Live Rekapitulasi ' . $targetSchool->type : 'Live Rekapitulasi' }}</div>
                        <div class="rekap-subtitle">{{ isset($targetSchool) ? 'Kehadiran Siswa & Guru ' . $targetSchool->name : 'Kehadiran Siswa & Guru per Unit Sekolah' }}</div>
                    </div>
                </div>
                <div class="rekap-live-pill">
                    <span class="live-dot-mini"></span>
                    SYNC
                </div>
            </div>

            <!-- Container 3 Unit Sekolah (SMP, SMA, SMK) -->
            <div class="unit-panels-container" id="unit-panels">
                <div style="padding:20px;text-align:center;color:var(--text-dim);font-size:14px;background:var(--bg-panel);border-radius:14px;border:1px solid var(--border);">
                    Memuat rekapitulasi unit sekolah...
                </div>
            </div>

            <!-- Status Bar Bawah -->
            <div class="status-bar">
                <span class="status-bar-text">🔄 Auto-refresh setiap 5 detik</span>
                <span class="status-bar-time" id="last-updated">–</span>
            </div>
        </div>

    </div>
</div>

<!-- NOTIFIKASI POP-UP -->
<div class="notif-wrapper" id="notif-wrapper"></div>

<!-- OFFLINE OVERLAY -->
<div class="offline-overlay" id="offline-overlay">
    <div class="offline-box">
        <div class="offline-icon">📡</div>
        <div class="offline-text">Koneksi Terputus</div>
        <div class="offline-sub">Browser kehilangan koneksi ke server. Sedang memantau jaringan untuk menghubungkan kembali...</div>
        <div class="offline-status-badge" id="offline-status-attempts">Mencoba menghubungkan ulang...</div>
    </div>
</div>

<script>
// ============================================================
//  KONFIGURASI
// ============================================================
const UNIT_PARAM    = "{{ $unitNumber ?? ($targetType ?? '') }}";
const API_BASE_URL  = "{{ route('display.live-data') }}";
const API_URL       = API_BASE_URL + (UNIT_PARAM ? '?unit=' + encodeURIComponent(UNIT_PARAM) : '');
const POLL_INTERVAL = 5000;  // 5 detik
const NOTIF_DURATION= 5000;  // Notifikasi hilang setelah 5 detik

// ============================================================
//  STATE
// ============================================================
let lastFeedHash       = '';
let failCount          = 0;
let prevStats          = {};
let currentFeedData    = [];
let currentTotalAbsen  = 0;
let currentSearchQuery = '';
let currentStatusFilter= 'all';
let soundMuted         = localStorage.getItem('pembda_display_muted') === '1';
let mobileViewMode     = 'feed'; // 'feed' (default) or 'rekap'

// ============================================================
//  DETEKSI PERANGKAT (MOBILE vs DESKTOP)
// ============================================================
function checkDevice() {
    const isMobile = window.innerWidth <= 900 || /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    document.body.classList.toggle('is-mobile-device', isMobile);
    return isMobile;
}
function isMobileView() {
    return window.innerWidth <= 900 || document.body.classList.contains('is-mobile-device');
}

// ============================================================
//  JAM LOKAL (diperbarui setiap detik via setInterval)
// ============================================================
function tickClock() {
    const now = new Date();
    const hh  = String(now.getHours()).padStart(2, '0');
    const mm  = String(now.getMinutes()).padStart(2, '0');
    const ss  = String(now.getSeconds()).padStart(2, '0');
    document.getElementById('clock').textContent = `${hh}:${mm}:${ss}`;
}
setInterval(tickClock, 1000);
tickClock();

// ============================================================
//  POLLING DATA DARI SERVER
// ============================================================
async function fetchData() {
    try {
        const sep = API_URL.includes('?') ? '&' : '?';
        const res  = await fetch(API_URL + sep + 't=' + Date.now());
        if (!res.ok) throw new Error('HTTP ' + res.status);
        const data = await res.json();
        failCount  = 0;
        document.getElementById('offline-overlay').classList.remove('show');
        updateDisplay(data);
    } catch (e) {
        failCount++;
        console.warn('Fetch gagal:', e.message, '(attempt', failCount, ')');
        
        // Update teks status percobaan
        const statusBadge = document.getElementById('offline-status-attempts');
        if (statusBadge) {
            statusBadge.textContent = `Percobaan menghubungkan kembali: ${failCount}`;
        }
        
        if (failCount >= 3 || !navigator.onLine) {
            document.getElementById('offline-overlay').classList.add('show');
        }
    }
}

// ============================================================
//  EVENT LISTENERS UNTUK STATUS JARINGAN BROWSER
// ============================================================
window.addEventListener('offline', () => {
    console.warn('Jaringan terdeteksi offline oleh browser.');
    document.getElementById('offline-overlay').classList.add('show');
    const statusBadge = document.getElementById('offline-status-attempts');
    if (statusBadge) {
        statusBadge.textContent = 'Browser Offline (Cek Wi-Fi)';
    }
});

window.addEventListener('online', () => {
    console.log('Jaringan terdeteksi online kembali. Mengambil data...');
    const statusBadge = document.getElementById('offline-status-attempts');
    if (statusBadge) {
        statusBadge.textContent = 'Menghubungkan kembali ke server...';
    }
    // Langsung picu fetch data tanpa menunggu interval berikutnya
    fetchData();
});

// ============================================================
//  UPDATE TAMPILAN
// ============================================================
function updateDisplay(data) {
    // ─ Tanggal & Update Time dari server ─
    const dateEl = document.getElementById('header-date');
    if (dateEl && data.tanggal) {
        dateEl.textContent = data.tanggal;
    }
    const dateMobileEl = document.getElementById('header-date-mobile');
    if (dateMobileEl && data.tanggal) {
        dateMobileEl.textContent = data.tanggal;
    }
    const lastUpEl = document.getElementById('last-updated');
    if (lastUpEl && data.last_updated) {
        lastUpEl.textContent = '✓ Update: ' + data.last_updated;
    }

    // ─ Rekapitulasi Per Unit Sekolah & Rombel ─
    renderUnitPanels(data.rekap_unit, data.rombel_stats);

    // ─ Update Angka Total Absen untuk Tampilan Diperlebar & Mobile Stats Strip ─
    updateExpandedStats(data);
    updateMobileStats(data);

    // ─ Feed Aktivitas ─
    currentTotalAbsen = data.statistik?.total_absen ?? ((data.statistik?.total_siswa_absen ?? 0) + (data.statistik?.total_pegawai_absen ?? 0));
    currentFeedData = data.feed || [];
    const feedHash = JSON.stringify(currentFeedData.slice(0, 3));
    let isNewScan = false;
    if (feedHash !== lastFeedHash) {
        // Ada data baru – tampilkan notifikasi
        if (lastFeedHash !== '' && currentFeedData.length > 0) {
            const newest = currentFeedData[0];
            showNotif(newest);
            isNewScan = true;
        }
        lastFeedHash = feedHash;
    }

    applyFeedFilters(isNewScan);

    const countEl = document.getElementById('feed-count');
    if (countEl) {
        countEl.textContent = currentTotalAbsen + ' aktivitas hari ini';
    }
}

// ============================================================
//  UPDATE TOTAL ABSEN SAJA SAAT MODE DIPERLEBAR (EXPANDED)
// ============================================================
function updateExpandedStats(data) {
    if (!data) return;

    // Hitung total siswa & guru yang sudah absen hari ini
    const sHadir = data.statistik?.siswa_hadir || 0;
    const sTerlambat = data.statistik?.siswa_terlambat || 0;
    const totalSiswa = data.statistik?.total_siswa_absen ?? (sHadir + sTerlambat);

    const totalPegawai = data.statistik?.total_pegawai_absen ?? (data.statistik?.pegawai_hadir || 0);
    const totalAll = data.statistik?.total_absen ?? (totalSiswa + totalPegawai);

    const elTotalAll = document.getElementById('exp-total-all');
    if (elTotalAll) elTotalAll.textContent = totalAll.toLocaleString('id-ID');

    const elTotalSiswa = document.getElementById('exp-total-siswa');
    if (elTotalSiswa) elTotalSiswa.textContent = totalSiswa.toLocaleString('id-ID');

    const elTotalPegawai = document.getElementById('exp-total-pegawai');
    if (elTotalPegawai) elTotalPegawai.textContent = totalPegawai.toLocaleString('id-ID');

    // Unit pills jika rekap_unit > 1 (seperti di /display umum dengan SMP, SMA, SMK)
    const unitsContainer = document.getElementById('exp-stat-units');
    if (unitsContainer) {
        if (data.rekap_unit && data.rekap_unit.length > 1) {
            unitsContainer.innerHTML = data.rekap_unit.map(unit => {
                const unitType = (unit.type || '').toUpperCase();
                const sTap = unit.siswa?.tap_hadir ?? ((unit.siswa?.hadir || 0) + (unit.siswa?.terlambat || 0));
                const gTap = unit.pegawai?.tap_hadir ?? (unit.pegawai?.hadir || 0);
                const uTotal = sTap + gTap;
                return `
                    <div class="exp-stat-pill exp-pill-unit unit-pill-${unitType.toLowerCase()}" title="${escHtml(unit.name)}: ${uTotal} hadir (${sTap} siswa, ${gTap} guru/staf)">
                        <span class="exp-unit-code">${escHtml(unitType)}:</span>
                        <span class="exp-stat-val">${uTotal}</span>
                    </div>
                `;
            }).join('');
            unitsContainer.style.display = 'flex';
        } else {
            unitsContainer.innerHTML = '';
            unitsContainer.style.display = 'none';
        }
    }
}

// ============================================================
//  UPDATE RINGKASAN STATISTIK KHUSUS MOBILE
// ============================================================
function updateMobileStats(data) {
    if (!data) return;

    const sHadir     = data.statistik?.siswa_hadir || 0;
    const sTerlambat = data.statistik?.siswa_terlambat || 0;
    const totalSiswa = data.statistik?.total_siswa_absen ?? (sHadir + sTerlambat);

    const totalPegawai = data.statistik?.total_pegawai_absen ?? (data.statistik?.pegawai_hadir || 0);
    const totalAll     = data.statistik?.total_absen ?? (totalSiswa + totalPegawai);

    const elTotal  = document.getElementById('m-stat-total');
    if (elTotal) elTotal.textContent = totalAll.toLocaleString('id-ID');

    const elSiswa  = document.getElementById('m-stat-siswa');
    if (elSiswa) elSiswa.textContent = totalSiswa.toLocaleString('id-ID');

    const elGuru   = document.getElementById('m-stat-guru');
    if (elGuru) elGuru.textContent = totalPegawai.toLocaleString('id-ID');

    const elLambat = document.getElementById('m-stat-lambat');
    if (elLambat) elLambat.textContent = sTerlambat.toLocaleString('id-ID');
}

// ============================================================
//  PENGATURAN SUARA NOTIFIKASI (MUTE / UNMUTE)
// ============================================================
function updateSoundButton() {
    const icon = document.getElementById('sound-icon');
    const btn  = document.getElementById('btn-sound-toggle');
    if (!icon || !btn) return;
    if (soundMuted) {
        icon.className = 'fa-solid fa-volume-xmark';
        btn.classList.add('muted');
        btn.title = 'Suara Notifikasi: Nonaktif (Klik untuk bunyikan)';
    } else {
        icon.className = 'fa-solid fa-volume-high';
        btn.classList.remove('muted');
        btn.title = 'Suara Notifikasi: Aktif (Klik untuk bisukan)';
    }
}

function toggleMuteSound() {
    soundMuted = !soundMuted;
    localStorage.setItem('pembda_display_muted', soundMuted ? '1' : '0');
    updateSoundButton();
}

// ============================================================
//  TOGGLE TAMPILAN MOBILE (LIVE ABSENSI vs REKAPITULASI)
// ============================================================
function switchMobileView(mode) {
    mobileViewMode = mode;
    const btnFeed  = document.getElementById('tab-btn-feed');
    const btnRekap = document.getElementById('tab-btn-rekap');

    if (mode === 'rekap') {
        if (btnFeed)  btnFeed.classList.remove('active');
        if (btnRekap) btnRekap.classList.add('active');
        document.body.classList.add('show-rekap-view');
    } else {
        if (btnFeed)  btnFeed.classList.add('active');
        if (btnRekap) btnRekap.classList.remove('active');
        document.body.classList.remove('show-rekap-view');
    }
}

// ============================================================
//  KONTROL FILTER & PENCARIAN FEED
// ============================================================
function handleSearchInput(val) {
    currentSearchQuery = (val || '').trim().toLowerCase();
    const clearBtn = document.getElementById('search-clear-btn');
    if (clearBtn) {
        clearBtn.style.display = currentSearchQuery ? 'inline-flex' : 'none';
    }
    applyFeedFilters(false);
}

function clearSearch() {
    const inp = document.getElementById('mobile-search-input');
    if (inp) inp.value = '';
    currentSearchQuery = '';
    const clearBtn = document.getElementById('search-clear-btn');
    if (clearBtn) clearBtn.style.display = 'none';
    applyFeedFilters(false);
}

function setStatusFilter(filter, el) {
    currentStatusFilter = filter;
    document.querySelectorAll('.filter-chip').forEach(btn => btn.classList.remove('active'));
    if (el) el.classList.add('active');
    applyFeedFilters(false);
}

function applyFeedFilters(isNewScan = false) {
    let filtered = currentFeedData;

    if (currentSearchQuery) {
        filtered = filtered.filter(item => {
            const nama   = (item.nama || '').toLowerCase();
            const info   = (item.info || '').toLowerCase();
            const school = (item.school_name || '').toLowerCase();
            const unit   = (item.unit || '').toLowerCase();
            return nama.includes(currentSearchQuery) || 
                   info.includes(currentSearchQuery) || 
                   school.includes(currentSearchQuery) || 
                   unit.includes(currentSearchQuery);
        });
    }

    if (currentStatusFilter !== 'all') {
        if (currentStatusFilter === 'masuk') {
            filtered = filtered.filter(item => item.tipe === 'masuk');
        } else if (currentStatusFilter === 'terlambat') {
            filtered = filtered.filter(item => item.tipe === 'terlambat');
        } else if (currentStatusFilter === 'pulang') {
            filtered = filtered.filter(item => item.tipe === 'pulang');
        } else if (currentStatusFilter === 'siswa') {
            filtered = filtered.filter(item => item.kategori === 'siswa');
        } else if (currentStatusFilter === 'pegawai') {
            filtered = filtered.filter(item => item.kategori === 'pegawai');
        }
    }

    renderFeed(filtered, isNewScan);
}

function manualRefresh() {
    const icon = document.getElementById('refresh-icon');
    if (icon) icon.classList.add('fa-spin');
    fetchData().finally(() => {
        setTimeout(() => {
            if (icon) icon.classList.remove('fa-spin');
        }, 600);
    });
}

// ============================================================
//  RENDER UNIT PANELS (3 Unit Sekolah atau Khusus Unit Terpilih)
// ============================================================
function renderUnitPanels(rekapUnit, rombelStats) {
    const container = document.getElementById('unit-panels');
    if (!container) return;

    if (!rekapUnit || rekapUnit.length === 0) {
        container.innerHTML = `<div style="padding:20px;text-align:center;color:var(--text-dim);font-size:14px;background:var(--bg-panel);border-radius:14px;border:1px solid var(--border);">Tidak ada data unit sekolah.</div>`;
        return;
    }

    const cardsHtml = rekapUnit.map(unit => {
        const isYayasan = unit.is_yayasan;
        const unitType = (unit.type || 'UNIT').toUpperCase();
        const unitClass = `unit-${unitType.toLowerCase()}`;

        // 1. Kalkulasi Siswa
        const sTotal     = unit.siswa?.total || 0;
        const sTapHadir  = unit.siswa?.tap_hadir ?? ((unit.siswa?.hadir || 0) + (unit.siswa?.terlambat || 0));
        const sTepat     = unit.siswa?.tepat_waktu ?? (unit.siswa?.hadir || 0);
        const sTerlambat = unit.siswa?.terlambat || 0;
        const sHadirPct  = sTotal > 0 ? Math.round((sTapHadir / sTotal) * 100) : 0;

        // 2. Kalkulasi Guru & Pegawai
        const gTotal     = unit.pegawai?.total || 0;
        const gTapHadir  = unit.pegawai?.tap_hadir ?? (unit.pegawai?.hadir || 0);
        const gTepat     = unit.pegawai?.tepat_waktu ?? (unit.pegawai?.hadir || 0);
        const gTerlambat = unit.pegawai?.terlambat || 0;
        const gHadirPct  = gTotal > 0 ? Math.round((gTapHadir / gTotal) * 100) : 0;

        return `
            <div class="unit-card ${unitClass}">
                <!-- Header Unit Sekolah -->
                <div class="unit-card-header">
                    <div class="unit-badge-name">
                        <span class="unit-type-pill">${escHtml(unitType)}</span>
                        <span class="unit-school-name" title="${escHtml(unit.name)}">${escHtml(unit.name)}</span>
                    </div>
                </div>

                <!-- Body 2 Kolom Berdampingan: Siswa (Kiri) & Guru (Kanan) -->
                <div class="unit-body-grid">
                    <!-- 1. BLOCK SISWA (Aksen Hijau Segar) -->
                    <div class="unit-block-siswa">
                        <div class="block-header-siswa">
                            <span class="block-title-siswa">
                                <i class="fa-solid fa-user-graduate"></i> Siswa
                            </span>
                            <span class="badge-rate-siswa">${sHadirPct}%</span>
                        </div>
                        <div class="stat-subgrid-2x2">
                            <div class="stat-box" title="Jumlah total siswa terdaftar aktif">
                                <span class="stat-box-lbl">Jml Siswa</span>
                                <span class="stat-box-val stat-val-siswa-total">${sTotal}</span>
                            </div>
                            <div class="stat-box" title="Total siswa yang sudah tap masuk">
                                <span class="stat-box-lbl">Tap Hadir</span>
                                <span class="stat-box-val stat-val-siswa-tap">${sTapHadir}</span>
                            </div>
                            <div class="stat-box" title="Siswa hadir tepat waktu">
                                <span class="stat-box-lbl">Tepat</span>
                                <span class="stat-box-val stat-val-siswa-tepat">${sTepat}</span>
                            </div>
                            <div class="stat-box" title="Siswa hadir terlambat">
                                <span class="stat-box-lbl">Lambat</span>
                                <span class="stat-box-val stat-val-siswa-lambat">${sTerlambat}</span>
                            </div>
                        </div>
                        <div class="progress-bar-mini" title="Persentase Kehadiran Siswa: ${sHadirPct}%">
                            <div class="progress-fill-mini fill-green" style="width: ${sHadirPct}%;"></div>
                        </div>
                    </div>

                    <!-- 2. BLOCK GURU & PEGAWAI (Aksen Ungu Elegan) -->
                    <div class="unit-block-pegawai">
                        <div class="block-header-pegawai">
                            <span class="block-title-pegawai">
                                <i class="fa-solid fa-chalkboard-user"></i> Guru/Peg
                            </span>
                            <span class="badge-rate-pegawai">${gHadirPct}%</span>
                        </div>
                        <div class="stat-subgrid-2x2">
                            <div class="stat-box" title="Jumlah total guru & pegawai wajib hadir">
                                <span class="stat-box-lbl">Jml Guru</span>
                                <span class="stat-box-val stat-val-peg-total">${gTotal}</span>
                            </div>
                            <div class="stat-box" title="Total guru/pegawai yang sudah tap masuk">
                                <span class="stat-box-lbl">Tap Hadir</span>
                                <span class="stat-box-val stat-val-peg-tap">${gTapHadir}</span>
                            </div>
                            <div class="stat-box" title="Guru/pegawai hadir tepat waktu">
                                <span class="stat-box-lbl">Tepat</span>
                                <span class="stat-box-val stat-val-peg-tepat">${gTepat}</span>
                            </div>
                            <div class="stat-box" title="Guru/pegawai terlambat">
                                <span class="stat-box-lbl">Lambat</span>
                                <span class="stat-box-val stat-val-peg-lambat">${gTerlambat}</span>
                            </div>
                        </div>
                        <div class="progress-bar-mini" title="Persentase Kehadiran Guru & Pegawai: ${gHadirPct}%">
                            <div class="progress-fill-mini fill-purple" style="width: ${gHadirPct}%;"></div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }).join('');

    let rombelHtml = '';
    if (rombelStats && rombelStats.length > 0) {
        rombelHtml = `
            <div class="rombel-panel">
                <div class="rombel-header">
                    <span class="rombel-title">
                        <i class="fa-solid fa-layer-group"></i> Rekapitulasi per Kelas / Rombel
                    </span>
                    <span class="rombel-total-badge">${rombelStats.length} Kelas</span>
                </div>
                <div class="rombel-grid">
                    ${rombelStats.map(r => `
                        <div class="rombel-card">
                            <div class="rombel-card-header">
                                <span class="rombel-name" title="${escHtml(r.name)}">${escHtml(r.name)}</span>
                                <span class="rombel-pct">${r.pct}%</span>
                            </div>
                            <div class="rombel-numbers">
                                <span>👥 ${r.total}</span>
                                <span style="color:#15803d;font-weight:800;" title="Hadir">✓ ${r.hadir}</span>
                                <span style="color:#b91c1c;font-weight:800;" title="Belum Absen">✗ ${r.belum}</span>
                            </div>
                            <div class="progress-bar-mini" title="${r.pct}% Hadir">
                                <div class="progress-fill-mini fill-green" style="width: ${r.pct}%;"></div>
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
    }

    container.innerHTML = cardsHtml + rombelHtml;
}

// ============================================================
//  CHIME GENERATOR (Web Audio API)
// ============================================================
function playChime(type) {
    if (soundMuted) return;

    try {
        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        
        const osc1 = audioCtx.createOscillator();
        const osc2 = audioCtx.createOscillator();
        const gainNode = audioCtx.createGain();
        
        osc1.connect(gainNode);
        osc2.connect(gainNode);
        gainNode.connect(audioCtx.destination);
        
        if (type === 'terlambat') {
            // Amber alert: low chime warning
            osc1.frequency.setValueAtTime(440, audioCtx.currentTime);     // A4
            osc2.frequency.setValueAtTime(554.37, audioCtx.currentTime);  // C#5
        } else if (type === 'pulang') {
            // Checkout chime: descending tone
            osc1.frequency.setValueAtTime(523.25, audioCtx.currentTime);  // C5
            osc2.frequency.setValueAtTime(392.00, audioCtx.currentTime);  // G4
        } else {
            // Checkin chime: ascending happy tone
            osc1.frequency.setValueAtTime(523.25, audioCtx.currentTime);  // C5
            osc2.frequency.setValueAtTime(659.25, audioCtx.currentTime);  // E5
        }
        
        osc1.type = 'sine';
        osc2.type = 'sine';
        
        gainNode.gain.setValueAtTime(0.15, audioCtx.currentTime);
        gainNode.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.8);
        
        osc1.start();
        osc2.start();
        
        osc1.stop(audioCtx.currentTime + 0.8);
        osc2.stop(audioCtx.currentTime + 0.8);
    } catch (e) {
        console.warn('Audio Context error / blocked:', e);
    }
}

// ============================================================
//  ZOOM & SCROLL DALAM AREA FEED AKTIVITAS
// ============================================================
let feedZoomLevel = parseFloat(localStorage.getItem('pembda_feed_zoom') || '1.0');

function applyFeedZoom(val) {
    feedZoomLevel = Math.min(1.8, Math.max(0.6, Math.round(val * 100) / 100));
    localStorage.setItem('pembda_feed_zoom', feedZoomLevel.toFixed(2));

    const container = document.getElementById('feed-zoom-container');
    if (container && !isMobileView()) {
        container.style.zoom = feedZoomLevel;
    }
    const lbl = document.getElementById('feed-zoom-val');
    if (lbl) {
        lbl.textContent = Math.round(feedZoomLevel * 100) + '%';
    }
}

function zoomFeed(delta) {
    applyFeedZoom(feedZoomLevel + delta);
}

function resetFeedZoom() {
    applyFeedZoom(1.0);
}

// Inisialisasi Zoom saat script dimuat
applyFeedZoom(feedZoomLevel);

// Event Listener: Tangkap event Ctrl + Scroll / Wheel hanya saat cursor berada di atas .feed-panel
document.addEventListener('DOMContentLoaded', () => {
    const feedPanel = document.querySelector('.feed-panel');
    if (feedPanel) {
        feedPanel.addEventListener('wheel', (e) => {
            if (e.ctrlKey) {
                // Cegah browser zoom seluruh halaman, hanya zoom area feed ini
                e.preventDefault();
                e.stopPropagation();
                const step = e.deltaY < 0 ? 0.05 : -0.05;
                applyFeedZoom(feedZoomLevel + step);
            }
        }, { passive: false });

        // Touch gesture pinch untuk monitor touchscreen
        let touchDist = null;
        let startZoom = feedZoomLevel;

        feedPanel.addEventListener('touchstart', (e) => {
            if (e.touches.length === 2) {
                const dx = e.touches[0].clientX - e.touches[1].clientX;
                const dy = e.touches[0].clientY - e.touches[1].clientY;
                touchDist = Math.hypot(dx, dy);
                startZoom = feedZoomLevel;
            }
        }, { passive: true });

        feedPanel.addEventListener('touchmove', (e) => {
            if (e.touches.length === 2 && touchDist) {
                e.preventDefault();
                const dx = e.touches[0].clientX - e.touches[1].clientX;
                const dy = e.touches[0].clientY - e.touches[1].clientY;
                const currentDist = Math.hypot(dx, dy);
                const factor = currentDist / touchDist;
                applyFeedZoom(startZoom * factor);
            }
        }, { passive: false });

        feedPanel.addEventListener('touchend', () => {
            touchDist = null;
        });
    }
});

// ============================================================
//  LEBARKAN / PERKECIL TAMPILAN FEED (FULL WIDTH TOGGLE)
// ============================================================
function toggleExpandFeed() {
    const isExpanded = document.body.classList.toggle('is-feed-expanded');
    localStorage.setItem('pembda_feed_expanded', isExpanded ? '1' : '0');
    updateExpandButtonState(isExpanded);
}

function updateExpandButtonState(isExpanded) {
    const btn   = document.getElementById('feed-expand-btn');
    const icon  = document.getElementById('feed-expand-icon');
    const label = document.getElementById('feed-expand-label');
    if (!btn || !icon || !label) return;

    if (isExpanded) {
        icon.className = 'fa-solid fa-compress';
        label.textContent = 'Perkecil';
        btn.title = 'Kembalikan Tampilan Normal (Tampilkan Rekapitulasi)';
        btn.classList.add('active');
    } else {
        icon.className = 'fa-solid fa-expand';
        label.textContent = 'Lebarkan';
        btn.title = 'Lebarkan Tampilan Live Absensi ke Layar Penuh (Sembunyikan Rekapitulasi)';
        btn.classList.remove('active');
    }
}

// Inisialisasi state preferensi tampilan full-width
(function initFeedExpandState() {
    const savedExpand = localStorage.getItem('pembda_feed_expanded') === '1';
    if (savedExpand && !isMobileView()) {
        document.body.classList.add('is-feed-expanded');
    }
    document.addEventListener('DOMContentLoaded', () => {
        updateExpandButtonState(document.body.classList.contains('is-feed-expanded'));

        // Shortcut keyboard: 'f' atau 'F' untuk toggle, 'Escape' untuk keluar mode full-width
        document.addEventListener('keydown', (e) => {
            if (e.target && ['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName)) return;
            if (e.key === 'f' || e.key === 'F') {
                e.preventDefault();
                toggleExpandFeed();
            } else if (e.key === 'Escape' && document.body.classList.contains('is-feed-expanded')) {
                e.preventDefault();
                toggleExpandFeed();
            }
        });
    });
})();

// ============================================================
//  RENDER FEED (DUAL MODE: MOBILE CARDS vs DESKTOP TABLE)
// ============================================================
function renderFeed(feed, isNewScan) {
    const list = document.getElementById('feed-list');
    if (!list) return;

    const items = feed;

    if (!items || items.length === 0) {
        const msg = (currentSearchQuery || currentStatusFilter !== 'all') 
            ? 'Tidak ada data kehadiran yang sesuai filter pencarian.'
            : 'Belum ada aktivitas absensi tercatat hari ini.';
        list.innerHTML = `
            <div style="padding:36px 20px;text-align:center;color:var(--text-dim);font-size:14px;background:#ffffff;border-radius:12px;">
                <i class="fa-solid fa-clipboard-user" style="font-size:36px;opacity:0.3;margin-bottom:12px;display:block;"></i>
                ${escHtml(msg)}
            </div>
        `;
        return;
    }

    const DEFAULT_PHOTO = "{{ asset('images/default-student.jpg') }}";
    const useMobileCards = isMobileView();

    list.innerHTML = items.map((item, idx) => {
        const icon        = item.tipe === 'terlambat' ? '🕐'
                          : item.tipe === 'pulang'    ? '🚪'
                          : item.kategori === 'pegawai'? '👔'
                          : '✅';
        const delay = Math.min(idx * 30, 400);

        // Tentukan Unit Sekolah & kelas CSS
        const roleName   = item.kategori === 'pegawai' ? 'GURU/STAF' : 'SISWA';
        const schoolName = item.school_name ? item.school_name.toUpperCase() : '';
        const badgeText  = schoolName ? `${roleName} · ${schoolName}` : roleName;

        const unitClass  = `${item.kategori}-${item.unit ? item.unit.toLowerCase() : 'default'}`;

        // Glow class jika item pertama dan ini scan baru
        let glowClass = '';
        if (idx === 0 && isNewScan) {
            glowClass = item.tipe === 'terlambat' ? 'glow-terlambat'
                      : item.tipe === 'pulang'    ? 'glow-pulang'
                      : 'glow-masuk';
        }

        // Kelas khusus untuk item paling baru
        const newestClass = idx === 0 ? 'feed-item-newest' : '';
        const fotoUrl     = item.foto || DEFAULT_PHOTO;
        const nomor       = currentTotalAbsen - idx;

        // Indicator "TERBARU" berkedip untuk item teratas
        let newLabelHtml = '';
        if (idx === 0) {
            newLabelHtml = `<span style="font-size: 10px; background: #000; color: #4ade80; padding: 1px 7px; border-radius: 10px; font-weight: 900; animation: blinker 1s linear infinite; border: 1px solid #4ade80; letter-spacing: 0.04em; flex-shrink: 0;">TERBARU</span>`;
        }

        // Tentukan IN & OUT times
        const inTime  = item.jam_masuk || '--:--';
        const outTime = item.jam_keluar || '--:--';

        // Tentukan Cara Absen
        const caraAbsen     = item.cara_absen || 'Manual';
        const caraAbsenTipe = item.cara_absen_tipe || 'manual';
        const caraAbsenIcon = item.cara_absen_icon || 'fa-solid fa-clipboard-user';

        // ── FORMAT KHUSUS MOBILE PHONE: TAMPILAN KARTU MODERN ──
        if (useMobileCards) {
            return `
                <div class="mobile-feed-card ${unitClass} ${glowClass} ${newestClass}" style="animation-delay:${delay}ms">
                    <div class="mfc-top">
                        <div class="mfc-avatar-container">
                            <img src="${escHtml(fotoUrl)}" alt="Foto" class="mfc-avatar" onerror="this.onerror=null; this.src='${DEFAULT_PHOTO}'">
                            <span class="mfc-seq">#${nomor}</span>
                        </div>
                        <div class="mfc-info-group">
                            <div class="mfc-name-row">
                                <h3 class="mfc-name">${escHtml(item.nama)}</h3>
                                ${newLabelHtml}
                            </div>
                            <div class="mfc-tags-row">
                                <span class="mfc-unit-tag">${escHtml(badgeText)}</span>
                                <span class="mfc-class-tag">${escHtml(item.info)}</span>
                            </div>
                        </div>
                        <div class="mfc-status-group">
                            <span class="mfc-status-pill status-${escHtml(item.tipe)}">${icon} ${escHtml(item.aksi)}</span>
                        </div>
                    </div>
                    <div class="mfc-bottom">
                        <div class="mfc-times">
                            <span class="mfc-time-badge time-in" title="Jam Masuk">
                                <i class="fa-solid fa-arrow-right-to-bracket"></i> IN ${escHtml(inTime)}
                            </span>
                            ${outTime !== '--:--' ? `
                                <span class="mfc-time-badge time-out" title="Jam Keluar">
                                    <i class="fa-solid fa-arrow-right-from-bracket"></i> OUT ${escHtml(outTime)}
                                </span>
                            ` : ''}
                        </div>
                        <div class="mfc-method">
                            <span class="mfc-method-badge method-${escHtml(caraAbsenTipe)}">
                                <i class="${escHtml(caraAbsenIcon)}"></i> ${escHtml(caraAbsen)}
                            </span>
                        </div>
                    </div>
                </div>
            `;
        }

        // ── FORMAT KHUSUS DESKTOP / MONITOR KIOSK TV: TABEL 7 KOLOM ──
        return `
            <div class="feed-item ${unitClass} ${glowClass} ${newestClass}" style="animation-delay:${delay}ms">
                <!-- 1. Kolom Nomor -->
                <span class="feed-num">${nomor}</span>
                
                <!-- 2. Kolom Avatar -->
                <div class="feed-avatar-container">
                    <img src="${escHtml(fotoUrl)}" alt="Foto" class="feed-avatar" onerror="this.onerror=null; this.src='${DEFAULT_PHOTO}'">
                </div>
                
                <!-- 3. Kolom Nama & Unit Sekolah (Vertikal Stack) -->
                <div style="display: flex; flex-direction: column; gap: 3px; min-width: 0;">
                    <div style="display: flex; align-items: center; gap: 8px; min-width: 0;">
                        <span class="feed-nama">${escHtml(item.nama)}</span>
                        ${newLabelHtml}
                    </div>
                    <div style="display: flex; align-items: center;">
                        <span class="unit-tag" style="margin: 0; font-size: 10px; padding: 2px 6px;">${escHtml(badgeText)}</span>
                    </div>
                </div>
                
                <!-- 4. Kolom Kelas -->
                <span class="feed-info">${escHtml(item.info)}</span>
                
                <!-- 5. Kolom Jam Masuk / Keluar (IN/OUT Stack) -->
                <div style="display: flex; flex-direction: column; gap: 2px;">
                    <span style="font-family: 'JetBrains Mono', monospace; font-size: 13px; font-weight: 900; color: #16a34a !important;">IN: ${escHtml(inTime)}</span>
                    <span style="font-family: 'JetBrains Mono', monospace; font-size: 13px; font-weight: 900; color: #dc2626 !important;">OUT: ${escHtml(outTime)}</span>
                </div>

                <!-- 6. Kolom Cara Absen -->
                <div>
                    <span class="feed-method-badge method-${escHtml(caraAbsenTipe)}">
                        <i class="${escHtml(caraAbsenIcon)}"></i>
                        <span>${escHtml(caraAbsen)}</span>
                    </span>
                </div>
                
                <!-- 7. Kolom Status Badge -->
                <div>
                    <span class="feed-badge">${icon} ${escHtml(item.aksi)}</span>
                </div>
            </div>
        `;
    }).join('');
}

// ============================================================
//  NOTIFIKASI POP-UP
// ============================================================
function showNotif(item) {
    const wrapper = document.getElementById('notif-wrapper');
    if (!wrapper) return;

    const el = document.createElement('div');
    
    const roleName   = item.kategori === 'pegawai' ? 'GURU/STAF' : 'SISWA';
    const schoolName = item.school_name ? item.school_name.toUpperCase() : '';
    const badgeText  = schoolName ? `${roleName} - ${schoolName}` : roleName;

    const unitClass  = `${item.kategori}-${item.unit ? item.unit.toLowerCase() : 'default'}`;
    const statusClass= item.tipe || 'masuk';
    
    const icon = item.tipe === 'terlambat' ? '🕐'
               : item.tipe === 'pulang'    ? '🚪'
               : '✅';

    const caraAbsen = item.cara_absen || 'Manual';

    el.className  = `notif ${statusClass} ${unitClass}`;
    el.innerHTML  = `
        <div class="notif-icon-circle">
            ${icon}
        </div>
        <div class="notif-body">
            <div class="notif-title-row">
                <span class="unit-tag">${escHtml(badgeText)}</span>
                <span class="notif-nama">${escHtml(item.nama)}</span>
            </div>
            <div class="notif-detail">${escHtml(item.info)} · ${escHtml(item.waktu)} · <strong>${escHtml(caraAbsen)}</strong></div>
        </div>
        <div class="notif-status-badge">
            ${escHtml(item.aksi)}
        </div>
    `;
    
    // Mainkan sound chime (jika tidak dimute)
    playChime(item.tipe);
    
    wrapper.appendChild(el);

    setTimeout(() => {
        el.style.animation = 'notif-out 0.35s ease forwards';
        setTimeout(() => el.remove(), 350);
    }, NOTIF_DURATION);
}

// ============================================================
//  UTILITAS
// ============================================================
function escHtml(str) {
    return String(str ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}

// ============================================================
//  INISIALISASI PERANGKAT & SUARA
// ============================================================
checkDevice();
updateSoundButton();

let resizeDebounceTimer;
window.addEventListener('resize', () => {
    clearTimeout(resizeDebounceTimer);
    resizeDebounceTimer = setTimeout(() => {
        checkDevice();
        applyFeedFilters(false);
    }, 150);
});

// ============================================================
//  MULAI POLLING
// ============================================================
fetchData();
setInterval(fetchData, POLL_INTERVAL);
</script>
</body>
</html>
