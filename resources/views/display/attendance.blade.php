<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=1920, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Live Monitoring Kehadiran – Perguruan PEMBDA</title>
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
            grid-template-rows: 90px 1fr;
            height: 100vh;
            padding: 12px;
            gap: 10px;
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
            border-radius: 14px;
            padding: 0 28px;
            position: relative;
            overflow: hidden;
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
            gap: 16px;
        }
        .school-logo {
            width: 60px; height: 60px;
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
            font-size: 20px;
            font-weight: 800;
            color: var(--text-primary);
            letter-spacing: 0.03em;
            text-transform: uppercase;
        }
        .school-info p {
            font-size: 12px;
            color: var(--text-secondary);
            letter-spacing: 0.08em;
            text-transform: uppercase;
            font-weight: 500;
        }
        .header-center {
            text-align: center;
        }
        .live-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(34,197,94,0.12);
            border: 1px solid rgba(34,197,94,0.35);
            border-radius: 20px;
            padding: 4px 12px;
            font-size: 11px;
            font-weight: 700;
            color: var(--green);
            letter-spacing: 0.1em;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .live-dot {
            width: 7px; height: 7px;
            background: var(--green);
            border-radius: 50%;
            animation: pulse-dot 1.5s ease infinite;
        }
        @keyframes pulse-dot {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(0.7); }
        }
        .header-date {
            font-size: 13px;
            color: var(--text-secondary);
            font-weight: 500;
            letter-spacing: 0.03em;
        }
        .header-right {
            text-align: right;
        }
        .clock {
            font-family: 'JetBrains Mono', monospace;
            font-size: 38px;
            font-weight: 600;
            color: var(--text-primary);
            letter-spacing: 0.04em;
            line-height: 1;
        }
        .clock-label {
            font-size: 11px;
            color: var(--text-secondary);
            letter-spacing: 0.1em;
            text-transform: uppercase;
            margin-top: 3px;
        }

        /* ============================================================
           BODY LAYOUT: KIRI (statistik + feed) | KANAN (stats detail)
        ============================================================ */
        .body-grid {
            display: grid;
            grid-template-columns: 1fr 340px;
            gap: 10px;
            min-height: 0;
        }

        /* ============================================================
           KOLOM KIRI
        ============================================================ */
        .left-col {
            display: grid;
            grid-template-rows: 160px 1fr;
            gap: 10px;
            min-height: 0;
        }

        /* STAT CARDS */
        .stat-cards {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
        }
        .stat-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 20px 20px 16px;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: border-color 0.3s;
        }
        .stat-card::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 3px;
            border-radius: 0 0 14px 14px;
        }
        .stat-card.hadir   { border-color: rgba(34,197,94,0.25); }
        .stat-card.hadir::after   { background: var(--green); }
        .stat-card.terlambat { border-color: rgba(245,158,11,0.25); }
        .stat-card.terlambat::after { background: var(--yellow); }
        .stat-card.pulang  { border-color: rgba(59,130,246,0.25); }
        .stat-card.pulang::after  { background: var(--blue); }
        .stat-card.belum   { border-color: rgba(239,68,68,0.20); }
        .stat-card.belum::after   { background: var(--red); }

        .stat-icon {
            font-size: 28px;
            line-height: 1;
            margin-bottom: 8px;
        }
        .stat-label {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }
        .stat-card.hadir   .stat-label { color: var(--green); }
        .stat-card.terlambat .stat-label { color: var(--yellow); }
        .stat-card.pulang  .stat-label { color: var(--blue); }
        .stat-card.belum   .stat-label { color: var(--red); }

        .stat-number {
            font-size: 56px;
            font-weight: 900;
            line-height: 1;
            letter-spacing: -0.02em;
        }
        .stat-card.hadir   .stat-number { color: var(--green); }
        .stat-card.terlambat .stat-number { color: var(--yellow); }
        .stat-card.pulang  .stat-number { color: var(--blue); }
        .stat-card.belum   .stat-number { color: var(--red); }

        .stat-sublabel {
            font-size: 11px;
            color: var(--text-dim);
            margin-top: 4px;
        }

        /* GLOW efek saat angka berubah */
        .stat-number.flash { animation: numflash 0.5s ease; }
        @keyframes numflash {
            0% { opacity: 0.4; transform: scale(0.95); }
            100% { opacity: 1; transform: scale(1); }
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
           KOLOM KANAN
        ============================================================ */
        .right-col {
            display: flex;
            flex-direction: column;
            gap: 10px;
            min-height: 0;
            overflow: hidden;
        }

        .info-panel {
            background: var(--bg-panel);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 18px;
            flex: 1;
        }
        .info-panel-title {
            font-size: 11px;
            font-weight: 700;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.12em;
            margin-bottom: 14px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border);
        }

        /* Progress bar kehadiran */
        .progress-item {
            margin-bottom: 18px;
        }
        .progress-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 6px;
        }
        .progress-label {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-primary);
        }
        .progress-value {
            font-size: 13px;
            font-weight: 700;
        }
        .progress-bar {
            height: 8px;
            background: rgba(0,0,0,0.06);
            border-radius: 4px;
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            border-radius: 4px;
            transition: width 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .fill-green  { background: linear-gradient(90deg, #16a34a, var(--green)); }
        .fill-yellow { background: linear-gradient(90deg, #d97706, var(--yellow)); }
        .fill-blue   { background: linear-gradient(90deg, #1d4ed8, var(--blue)); }
        .fill-red    { background: linear-gradient(90deg, #b91c1c, var(--red)); }
        .fill-purple { background: linear-gradient(90deg, #7e22ce, var(--purple)); }

        /* Guru & Staf ringkasan */
        .emp-summary {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-top: 4px;
        }
        .emp-card {
            background: var(--bg-card2);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 14px;
            text-align: center;
        }
        .emp-card-num {
            font-size: 32px;
            font-weight: 900;
            line-height: 1;
        }
        .emp-card-num.hadir { color: var(--green); }
        .emp-card-num.belum { color: var(--red); }
        .emp-card-lbl {
            font-size: 10px;
            color: var(--text-dim);
            margin-top: 4px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-weight: 600;
        }

        /* Status update */
        .status-bar {
            background: var(--bg-card2);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 10px 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-shrink: 0;
        }
        .status-bar-text {
            font-size: 11px;
            color: var(--text-dim);
        }
        .status-bar-time {
            font-family: 'JetBrains Mono', monospace;
            font-size: 11px;
            color: var(--text-secondary);
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
    </style>
</head>
<body>

<div class="display-wrapper">

    <!-- ── HEADER ─────────────────────────────────────────────── -->
    <header class="header">
        <div class="header-left">
            <div class="school-logo">
                <img src="{{ asset('images/logo-pembda.png') }}" alt="Logo Yayasan">
            </div>
            <div class="school-info">
                <h1>Perguruan PEMBDA</h1>
                <p>Sistem Monitoring Kehadiran Real-Time</p>
            </div>
        </div>
        <div class="header-center">
            <div class="live-badge">
                <span class="live-dot"></span>
                LIVE
            </div>
            <div class="header-date" id="header-date">Memuat...</div>
        </div>
        <div class="header-right">
            <div class="clock" id="clock">--:--:--</div>
            <div class="clock-label">Waktu Saat Ini</div>
        </div>
    </header>

    <!-- ── BODY ───────────────────────────────────────────────── -->
    <div class="body-grid">

        <!-- KOLOM KIRI -->
        <div class="left-col">

            <!-- STAT CARDS -->
            <div class="stat-cards">
                <div class="stat-card hadir">
                    <div>
                        <div class="stat-icon">✅</div>
                        <div class="stat-label">Hadir</div>
                    </div>
                    <div>
                        <div class="stat-number" id="stat-hadir">–</div>
                        <div class="stat-sublabel">siswa hadir tepat waktu</div>
                    </div>
                </div>
                <div class="stat-card terlambat">
                    <div>
                        <div class="stat-icon">🕐</div>
                        <div class="stat-label">Terlambat</div>
                    </div>
                    <div>
                        <div class="stat-number" id="stat-terlambat">–</div>
                        <div class="stat-sublabel">siswa terlambat masuk</div>
                    </div>
                </div>
                <div class="stat-card pulang">
                    <div>
                        <div class="stat-icon">🚪</div>
                        <div class="stat-label">Pulang</div>
                    </div>
                    <div>
                        <div class="stat-number" id="stat-pulang">–</div>
                        <div class="stat-sublabel">siswa sudah check-out</div>
                    </div>
                </div>
                <div class="stat-card belum">
                    <div>
                        <div class="stat-icon">❌</div>
                        <div class="stat-label">Belum Absen</div>
                    </div>
                    <div>
                        <div class="stat-number" id="stat-belum">–</div>
                        <div class="stat-sublabel">siswa belum terdeteksi</div>
                    </div>
                </div>
            </div>

            <!-- FEED AKTIVITAS TERBARU -->
            <!-- FEED AKTIVITAS TERBARU -->
            <div class="feed-panel">
                <div class="feed-header">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <span class="feed-title">⚡ Aktivitas Terbaru</span>
                        <span class="feed-count" id="feed-count">–</span>
                    </div>

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
                </div>

                <!-- Zoom Container: Hanya area tabel & data feed ini yang terkena Zoom / Scale -->
                <div class="feed-zoom-wrapper" id="feed-zoom-container">
                    <!-- Table Header Kolom -->
                    <div class="feed-table-header">
                        <span>NO</span>
                        <span>FOTO</span>
                        <span>NAMA &amp; UNIT</span>
                        <span>KELAS</span>
                        <span>WAKTU (IN/OUT)</span>
                        <span>CARA ABSEN</span>
                        <span style="text-align:center;">STATUS</span>
                    </div>
                    <div class="feed-list" id="feed-list">
                        <div style="padding:20px;text-align:center;color:var(--text-dim);font-size:14px;">
                            Memuat data...
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- KOLOM KANAN -->
        <div class="right-col">
            <!-- REKAPITULASI PER UNIT SEKOLAH -->
            <div id="unit-panels" style="display:flex; flex-direction:column; gap:10px; flex:1; overflow-y:auto; padding-right:2px;">
                <div style="padding:20px;text-align:center;color:var(--text-dim);font-size:14px;background:var(--bg-panel);border-radius:14px;border:1px solid var(--border);">
                    Memuat rekapitulasi unit...
                </div>
            </div>

            <!-- STATUS BAR -->
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
const API_URL       = "{{ route('display.live-data') }}";
const POLL_INTERVAL = 5000;  // 5 detik
const NOTIF_DURATION= 5000;  // Notifikasi hilang setelah 5 detik

// ============================================================
//  STATE
// ============================================================
let lastFeedHash   = '';
let failCount      = 0;
let prevStats      = {};

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
        const res  = await fetch(API_URL + '?t=' + Date.now());
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
    const s = data.statistik;

    // ─ Tanggal dari server ─
    document.getElementById('header-date').textContent = data.tanggal;
    document.getElementById('last-updated').textContent = '✓ Update: ' + data.last_updated;

    // ─ Stat Cards ─
    updateStat('stat-hadir',    s.siswa_hadir,    prevStats.siswa_hadir);
    updateStat('stat-terlambat',s.siswa_terlambat,prevStats.siswa_terlambat);
    updateStat('stat-pulang',   s.siswa_pulang,   prevStats.siswa_pulang);
    updateStat('stat-belum',    s.siswa_belum,    prevStats.siswa_belum);

    // ─ Rekapitulasi Per Unit Sekolah ─
    renderUnitPanels(data.rekap_unit);

    // ─ Feed Aktivitas ─
    const feedHash = JSON.stringify(data.feed.slice(0, 3));
    let isNewScan = false;
    if (feedHash !== lastFeedHash) {
        // Ada data baru – tampilkan notifikasi
        if (lastFeedHash !== '' && data.feed.length > 0) {
            const newest = data.feed[0];
            showNotif(newest);
            isNewScan = true;
        }
        lastFeedHash = feedHash;
        renderFeed(data.feed, isNewScan);
    }

    document.getElementById('feed-count').textContent = data.feed.length + ' aktivitas hari ini';
    prevStats = { ...s };
}

function updateStat(id, newVal, oldVal) {
    const el = document.getElementById(id);
    el.textContent = newVal;
    if (newVal !== oldVal && oldVal !== undefined) {
        el.classList.remove('flash');
        void el.offsetWidth; // reflow
        el.classList.add('flash');
    }
}

// ============================================================
//  RENDER UNIT PANELS (Dinamis per Unit Sekolah)
// ============================================================
function renderUnitPanels(rekapUnit) {
    const container = document.getElementById('unit-panels');
    if (!container) return;

    if (!rekapUnit || rekapUnit.length === 0) {
        container.innerHTML = `<div style="padding:20px;text-align:center;color:var(--text-dim);font-size:14px;">Tidak ada data unit sekolah.</div>`;
        return;
    }

    container.innerHTML = rekapUnit.map(unit => {
        const isYayasan = unit.is_yayasan;
        
        // Kalkulasi Siswa
        const sTotal = unit.siswa.total;
        const sHadir = unit.siswa.hadir;
        const sTerlambat = unit.siswa.terlambat;
        const sPulang = unit.siswa.pulang;
        const sBelum = unit.siswa.belum;
        const sHadirTotal = sHadir + sTerlambat;
        const sHadirPct = sTotal > 0 ? Math.round((sHadirTotal / sTotal) * 100) : 0;
        const sBelumPct = sTotal > 0 ? Math.round((sBelum / sTotal) * 100) : 0;
        
        // Kalkulasi Pegawai
        const gTotal = unit.pegawai.total;
        const gHadir = unit.pegawai.hadir;
        const gBelum = unit.pegawai.belum;
        const gHadirPct = gTotal > 0 ? Math.round((gHadir / gTotal) * 100) : 0;
        
        // CSS warna berdasarkan type unit
        let headerColor = 'var(--text-primary)';
        let borderAccent = 'var(--border-bright)';
        if (unit.type === 'SMP') {
            headerColor = '#1d4ed8'; // SMP Blue
            borderAccent = 'rgba(29, 78, 216, 0.3)';
        } else if (unit.type === 'SMK') {
            headerColor = '#ea580c'; // SMK Orange
            borderAccent = 'rgba(234, 88, 12, 0.3)';
        } else if (isYayasan) {
            headerColor = '#e11d48'; // Yayasan Rose
            borderAccent = 'rgba(225, 29, 72, 0.3)';
        }

        let siswaHtml = '';
        if (sTotal > 0) {
            siswaHtml = `
                <div style="margin-bottom: 14px;">
                    <div style="font-size: 13px; font-weight: 800; color: #1e293b; margin-bottom: 6px; display:flex; justify-content:space-between;">
                        <span>👨‍🎓 Kehadiran Siswa</span>
                        <span style="color:#475569;">Total: ${sTotal}</span>
                    </div>
                    
                    <!-- Progress Bar Hadir -->
                    <div class="progress-item" style="margin-bottom: 6px;">
                        <div class="progress-header" style="font-size:11px; margin-bottom: 2px;">
                            <span class="progress-label" style="font-weight:700; color:#15803d;">✅ Masuk: ${sHadirTotal} anak (${sHadir} tepat, ${sTerlambat} lambat, ${sPulang} pulang)</span>
                            <span class="progress-value" style="color:#15803d; font-weight:800;">${sHadirPct}%</span>
                        </div>
                        <div class="progress-bar" style="height: 7px; background: rgba(21,128,61,0.08);">
                            <div class="progress-fill fill-green" style="width: ${sHadirPct}%"></div>
                        </div>
                    </div>
                    
                    <!-- Progress Bar Belum Absen -->
                    <div class="progress-item" style="margin-bottom: 2px;">
                        <div class="progress-header" style="font-size:11px; margin-bottom: 2px;">
                            <span class="progress-label" style="font-weight:700; color:#b91c1c;">❌ Belum Absen: ${sBelum} anak</span>
                            <span class="progress-value" style="color:#b91c1c; font-weight:800;">${sBelumPct}%</span>
                        </div>
                        <div class="progress-bar" style="height: 7px; background: rgba(185,28,28,0.08);">
                            <div class="progress-fill fill-red" style="width: ${sBelumPct}%"></div>
                        </div>
                    </div>
                </div>
            `;
        }

        let pegawaiHtml = '';
        if (gTotal > 0) {
            const roleLabel = isYayasan ? '🏢 Staf Yayasan' : '👩‍🏫 Guru & Staf';
            pegawaiHtml = `
                <div>
                    <div style="font-size: 13px; font-weight: 800; color: #1e293b; margin-bottom: 6px; display:flex; justify-content:space-between;">
                        <span>${roleLabel}</span>
                        <span style="color:#475569;">Wajib: ${gTotal}</span>
                    </div>
                    
                    <!-- Progress Bar Pegawai -->
                    <div class="progress-item" style="margin-bottom: 2px;">
                        <div class="progress-header" style="font-size:11px; margin-bottom: 2px;">
                            <span class="progress-label" style="font-weight:700; color:#6b21a8;">✅ Hadir: ${gHadir} orang (Belum: ${gBelum})</span>
                            <span class="progress-value" style="color:#6b21a8; font-weight:800;">${gHadirPct}%</span>
                        </div>
                        <div class="progress-bar" style="height: 7px; background: rgba(107,33,168,0.08);">
                            <div class="progress-fill fill-purple" style="width: ${gHadirPct}%"></div>
                        </div>
                    </div>
                </div>
            `;
        }

        return `
            <div class="info-panel" style="border: 2px solid ${borderAccent}; padding: 14px 18px; margin-bottom: 2px; flex:0 0 auto;">
                <div style="font-size: 15px; font-weight: 900; color: ${headerColor}; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 12px; border-bottom: 1px solid var(--border); padding-bottom: 6px; display:flex; align-items:center; gap:8px;">
                    <span>🏫</span> ${escHtml(unit.name)}
                </div>
                ${siswaHtml}
                ${pegawaiHtml}
            </div>
        `;
    }).join('');
}

// ============================================================
//  CHIME GENERATOR (Web Audio API)
// ============================================================
function playChime(type) {
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
    if (container) {
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
//  RENDER FEED
// ============================================================
function renderFeed(feed, isNewScan) {
    const list = document.getElementById('feed-list');

    // Tampilkan seluruh data feed (bisa di-scroll bebas & di-zoom)
    const items = feed;
    const DEFAULT_PHOTO = "{{ asset('images/default-student.jpg') }}";

    list.innerHTML = items.map((item, idx) => {
        const icon        = item.tipe === 'terlambat' ? '🕐'
                          : item.tipe === 'pulang'    ? '🚪'
                          : item.kategori === 'pegawai'? '👔'
                          : '✅';
        const delay = idx * 40;

        // Tentukan Unit Sekolah & kelas CSS
        const roleName = item.kategori === 'pegawai' ? 'GURU/STAF' : 'SISWA';
        const schoolName = item.school_name ? item.school_name.toUpperCase() : '';
        const badgeText = schoolName ? `${roleName} · ${schoolName}` : roleName;

        const unitClass = `${item.kategori}-${item.unit ? item.unit.toLowerCase() : 'default'}`;

        // Glow class jika item pertama dan ini scan baru
        let glowClass = '';
        if (idx === 0 && isNewScan) {
            glowClass = item.tipe === 'terlambat' ? 'glow-terlambat'
                      : item.tipe === 'pulang'    ? 'glow-pulang'
                      : 'glow-masuk';
        }

        // Kelas khusus untuk item paling baru
        const newestClass = idx === 0 ? 'feed-item-newest' : '';
        const fotoUrl = item.foto || DEFAULT_PHOTO;
        const nomor = feed.length - idx;

        // Indicator "TERBARU" berkedip untuk item teratas
        let newLabelHtml = '';
        if (idx === 0) {
            newLabelHtml = `<span style="font-size: 10px; background: #000; color: #4ade80; padding: 1px 7px; border-radius: 10px; font-weight: 900; animation: blinker 1s linear infinite; border: 1px solid #4ade80; letter-spacing: 0.04em; flex-shrink: 0;">TERBARU</span>`;
        }

        // Tentukan IN & OUT times
        const inTime = item.jam_masuk || '--:--';
        const outTime = item.jam_keluar || '--:--';

        // Tentukan Cara Absen
        const caraAbsen = item.cara_absen || 'Manual';
        const caraAbsenTipe = item.cara_absen_tipe || 'manual';
        const caraAbsenIcon = item.cara_absen_icon || 'fa-solid fa-clipboard-user';

        return `<div class="feed-item ${unitClass} ${glowClass} ${newestClass}" style="animation-delay:${delay}ms">
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
        </div>`;
    }).join('');
}

// ============================================================
//  NOTIFIKASI POP-UP
// ============================================================
function showNotif(item) {
    const wrapper = document.getElementById('notif-wrapper');
    const el = document.createElement('div');
    
    const roleName = item.kategori === 'pegawai' ? 'GURU/STAF' : 'SISWA';
    const schoolName = item.school_name ? item.school_name.toUpperCase() : '';
    const badgeText = schoolName ? `${roleName} - ${schoolName}` : roleName;

    const unitClass = `${item.kategori}-${item.unit ? item.unit.toLowerCase() : 'default'}`;
    const statusClass = item.tipe || 'masuk';
    
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
    
    // Mainkan sound chime
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
//  MULAI POLLING
// ============================================================
fetchData();
setInterval(fetchData, POLL_INTERVAL);
</script>
</body>
</html>
