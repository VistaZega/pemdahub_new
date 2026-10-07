<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🏆 Livescore — {{ $exam->exam_title }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        :root {
            --gold: #FFD700; --silver: #C0C0C0; --bronze: #CD7F32;
            --green: #22c55e; --blue: #3b82f6; --red: #ef4444;
            --bg: #0a0f1e; --card: #111827; --border: #1f2937;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: var(--bg);
            color: #f1f5f9;
            font-family: 'Segoe UI', system-ui, sans-serif;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* ── HEADER ─────────────────────────────────── */
        .header {
            background: linear-gradient(135deg, #003366 0%, #0a1628 100%);
            border-bottom: 3px solid var(--gold);
            padding: 16px 32px;
            display: flex; justify-content: space-between; align-items: center;
        }
        .header-left h1 { font-size: 1.5rem; font-weight: 900; color: var(--gold); letter-spacing: 1px; }
        .header-left p  { font-size: 0.85rem; color: #94a3b8; margin-top: 2px; }
        .header-right { text-align: right; }
        .live-badge {
            display: inline-flex; align-items: center; gap: 6px;
            background: #dc2626; color: white; padding: 4px 14px;
            border-radius: 20px; font-size: 0.78rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: 1px;
            animation: pulse-badge 1.5s infinite;
        }
        .live-dot { width: 8px; height: 8px; background: white; border-radius: 50%; }
        @keyframes pulse-badge { 0%,100%{opacity:1} 50%{opacity:.6} }
        .timer-clock { font-size: 1.4rem; font-weight: 900; color: var(--gold); margin-top: 6px; }

        /* ── STATS BAR ───────────────────────────────── */
        .stats-bar {
            display: flex; gap: 12px; padding: 12px 32px;
            background: #070d1a; border-bottom: 1px solid var(--border);
        }
        .stat-pill {
            background: var(--card); border: 1px solid var(--border);
            border-radius: 8px; padding: 8px 16px;
            display: flex; flex-direction: column; align-items: center;
        }
        .stat-pill .val { font-size: 1.3rem; font-weight: 900; color: var(--gold); }
        .stat-pill .lbl { font-size: 0.7rem; color: #64748b; text-transform: uppercase; letter-spacing: 1px; }
        .scoring-info {
            margin-left: auto; background: #1a2d1a; border: 1px solid #166534;
            border-radius: 8px; padding: 8px 16px; font-size: 0.8rem; color: #86efac;
            display: flex; align-items: center;
        }

        /* ── LEADERBOARD TABLE ───────────────────────── */
        .leaderboard-wrap { padding: 20px 32px; }
        .section-title {
            font-size: 1rem; font-weight: 700; color: #94a3b8;
            text-transform: uppercase; letter-spacing: 2px;
            margin-bottom: 14px; display: flex; align-items: center; gap: 8px;
        }

        .lb-table { width: 100%; border-collapse: separate; border-spacing: 0 6px; }
        .lb-table thead th {
            font-size: 0.72rem; text-transform: uppercase; letter-spacing: 1.5px;
            color: #475569; padding: 6px 14px; text-align: left;
        }
        .lb-row {
            background: var(--card);
            border-radius: 10px;
            transition: transform 0.3s ease, background 0.3s ease;
        }
        .lb-row td { padding: 14px 14px; vertical-align: middle; }
        .lb-row td:first-child { border-radius: 10px 0 0 10px; }
        .lb-row td:last-child  { border-radius: 0 10px 10px 0; }

        /* Rank podium colors */
        .lb-row.rank-1 { background: linear-gradient(90deg, #2d2200 0%, #1a1200 100%); border-left: 4px solid var(--gold); }
        .lb-row.rank-2 { background: linear-gradient(90deg, #1e2028 0%, #111827 100%); border-left: 4px solid var(--silver); }
        .lb-row.rank-3 { background: linear-gradient(90deg, #1a0e0a 0%, #111827 100%); border-left: 4px solid var(--bronze); }

        .rank-badge {
            width: 36px; height: 36px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-weight: 900; font-size: 1rem; border: 2px solid #374151;
        }
        .rank-1 .rank-badge { background: var(--gold); color: #000; border-color: var(--gold); font-size: 1.2rem; }
        .rank-2 .rank-badge { background: var(--silver); color: #000; border-color: var(--silver); }
        .rank-3 .rank-badge { background: var(--bronze); color: #fff; border-color: var(--bronze); }

        .team-name { font-size: 1rem; font-weight: 700; color: #f1f5f9; }
        .class-name { font-size: 0.78rem; color: #64748b; margin-top: 2px; }

        .score-cell { text-align: center; }
        .score-val { font-size: 1.5rem; font-weight: 900; color: var(--gold); }
        .score-val.negative { color: var(--red); }
        .score-val.zero { color: #64748b; }

        .detail-cell { font-size: 0.82rem; text-align: center; }
        .correct { color: var(--green); font-weight: 700; }
        .wrong   { color: var(--red); font-weight: 700; }
        .skip    { color: #64748b; }

        /* Status badge */
        .status-badge {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 4px 10px; border-radius: 20px;
            font-size: 0.72rem; font-weight: 700; text-transform: uppercase;
        }
        .status-done    { background: #14532d; color: #4ade80; }
        .status-ongoing { background: #1e3a5f; color: #60a5fa; animation: pulse-badge 2s infinite; }
        .status-waiting { background: #1f2937; color: #6b7280; }

        /* Waiting teams (grey area) */
        .waiting-section { padding: 0 32px 24px; }
        .waiting-grid { display: flex; flex-wrap: wrap; gap: 8px; }
        .waiting-chip {
            background: #1f2937; border: 1px solid #374151;
            border-radius: 8px; padding: 8px 14px;
            font-size: 0.82rem; color: #6b7280;
        }

        /* Paused overlay */
        #pausedBanner {
            display: none;
            position: fixed; top: 0; left: 0; right: 0;
            background: #7c2d12; color: white;
            text-align: center; padding: 10px;
            font-weight: 700; font-size: 1rem; z-index: 99;
            animation: pulse-badge 1s infinite;
        }

        /* Update flash */
        .flash-update { animation: flash 0.5s ease; }
        @keyframes flash { 0%{background:#1e3a5f} 100%{background:var(--card)} }
    </style>
</head>
<body>

<div id="pausedBanner">⏸ UJIAN SEDANG DIJEDA OLEH PENGAWAS</div>

<!-- HEADER -->
<div class="header">
    <div class="header-left">
        <h1>🏆 {{ $exam->exam_title }}</h1>
        <p>SMK Swasta Pembda Nias &bull; Livescore Lomba Numerasi</p>
    </div>
    <div class="header-right">
        <div class="live-badge">
            <span class="live-dot"></span> LIVE
        </div>
        <div class="timer-clock" id="clockDisplay">--:--:--</div>
    </div>
</div>

<!-- STATS BAR -->
<div class="stats-bar">
    <div class="stat-pill">
        <span class="val" id="statActive">0</span>
        <span class="lbl">Sedang Mengerjakan</span>
    </div>
    <div class="stat-pill">
        <span class="val" id="statDone">0</span>
        <span class="lbl">Sudah Selesai</span>
    </div>
    <div class="stat-pill">
        <span class="val" id="statWaiting">0</span>
        <span class="lbl">Belum Mulai</span>
    </div>
    <div class="stat-pill">
        <span class="val" id="statTotal">0</span>
        <span class="lbl">Total Tim</span>
    </div>
    <div class="scoring-info" id="scoringInfo">⚡ Benar: +4 | Salah: -1 | Kosong: 0</div>
</div>

<!-- LEADERBOARD -->
<div class="leaderboard-wrap">
    <div class="section-title">
        🥇 Papan Peringkat
        <span style="font-size:0.7rem;color:#374151;font-weight:400;margin-left:8px">
            Diperbarui otomatis setiap 2 detik &bull; Terakhir: <span id="lastUpdate">-</span>
        </span>
    </div>

    <table class="lb-table">
        <thead>
            <tr>
                <th width="60">No.</th>
                <th>Tim / Kelompok</th>
                <th width="120" style="text-align:center">Skor</th>
                <th width="200" style="text-align:center">Benar / Salah / Kosong</th>
                <th width="130" style="text-align:center">Status</th>
            </tr>
        </thead>
        <tbody id="leaderboardBody">
            <tr><td colspan="5" style="text-align:center;padding:40px;color:#374151">
                Memuat data...
            </td></tr>
        </tbody>
    </table>
</div>

<!-- WAITING TEAMS -->
<div class="waiting-section" id="waitingSection" style="display:none">
    <div class="section-title" style="font-size:0.8rem;">⏳ Belum Mulai</div>
    <div class="waiting-grid" id="waitingGrid"></div>
</div>

<script>
const POLL_URL  = '{{ route("admin.cbt.competition.livescore-data", $exam) }}';
const POLL_MS   = 2500;
let lastScores  = {};
let pollTimer;

// Jam real-time
function updateClock() {
    const now = new Date();
    document.getElementById('clockDisplay').textContent =
        now.toLocaleTimeString('id-ID', {hour12: false});
}
setInterval(updateClock, 1000);
updateClock();

// Render leaderboard
function renderLeaderboard(data) {
    const body      = document.getElementById('leaderboardBody');
    const waitGrid  = document.getElementById('waitingGrid');
    const waitSect  = document.getElementById('waitingSection');

    const done    = data.leaderboard.filter(r => r.status === 'done');
    const ongoing = data.leaderboard.filter(r => r.status === 'ongoing');
    const waiting = data.leaderboard.filter(r => r.status === 'waiting');

    // Update stats
    document.getElementById('statActive').textContent  = ongoing.length;
    document.getElementById('statDone').textContent    = done.length;
    document.getElementById('statWaiting').textContent = waiting.length;
    document.getElementById('statTotal').textContent   = data.stats.total_teams || data.leaderboard.length;
    document.getElementById('scoringInfo').textContent = '⚡ ' + data.stats.scoring_info;
    document.getElementById('lastUpdate').textContent  = data.timestamp;

    // Paused banner
    document.getElementById('pausedBanner').style.display = data.stats.is_paused ? 'block' : 'none';

    // Render ranking rows (done + ongoing hanya)
    const ranked = [...done, ...ongoing];
    let rank = 0;
    let html = '';

    if (ranked.length === 0) {
        html = `<tr><td colspan="5" style="text-align:center;padding:40px;color:#374151">
            Belum ada tim yang mulai mengerjakan...
        </td></tr>`;
    } else {
        ranked.forEach((row, idx) => {
            const isDone = row.status === 'done';
            rank = isDone ? rank + 1 : rank;
            const displayRank = isDone ? rank : '...';

            const isNewScore = lastScores[row.team_id] !== row.score;
            if (isNewScore) lastScores[row.team_id] = row.score;

            const rankClass = displayRank === 1 ? 'rank-1' : displayRank === 2 ? 'rank-2' : displayRank === 3 ? 'rank-3' : '';
            const medal = displayRank === 1 ? '🥇' : displayRank === 2 ? '🥈' : displayRank === 3 ? '🥉' : displayRank;

            const scoreNum = row.score !== null ? parseFloat(row.score) : null;
            const scoreClass = scoreNum === null ? 'zero' : scoreNum < 0 ? 'negative' : '';
            const scoreDisplay = scoreNum !== null ? scoreNum : '-';

            const statusBadge = isDone
                ? `<span class="status-badge status-done">✓ Selesai</span>`
                : `<span class="status-badge status-ongoing">● Mengerjakan</span>`;

            html += `
            <tr class="lb-row ${rankClass} ${isNewScore && isDone ? 'flash-update' : ''}" data-team="${row.team_id}">
                <td>
                    <div class="rank-badge">${medal}</div>
                </td>
                <td>
                    <div class="team-name">${escapeHtml(row.display_name)}</div>
                    <div class="class-name">${escapeHtml(row.class_name)}</div>
                </td>
                <td class="score-cell">
                    <div class="score-val ${scoreClass}">${scoreDisplay}</div>
                </td>
                <td class="detail-cell">
                    <span class="correct">✓ ${row.correct}</span> &nbsp;
                    <span class="wrong">✗ ${row.wrong}</span> &nbsp;
                    <span class="skip">— ${row.unanswered}</span>
                </td>
                <td style="text-align:center">${statusBadge}</td>
            </tr>`;
        });
    }
    body.innerHTML = html;

    // Waiting section
    if (waiting.length > 0) {
        waitSect.style.display = 'block';
        waitGrid.innerHTML = waiting.map(w =>
            `<div class="waiting-chip">⌛ ${escapeHtml(w.display_name)}</div>`
        ).join('');
    } else {
        waitSect.style.display = 'none';
    }
}

function escapeHtml(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

async function poll() {
    try {
        const res  = await fetch(POLL_URL, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const data = await res.json();
        renderLeaderboard(data);
    } catch (e) {
        console.warn('Poll error:', e);
    }
    pollTimer = setTimeout(poll, POLL_MS);
}

// Mulai polling
poll();
</script>
</body>
</html>
