<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Scanning #{{ $session->id }} — Greenhouse UNSRI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <style>
        body {
            background: #f4f6f9;
            font-family: 'Inter', sans-serif;
            font-size: 14px;
        }

        /* ── Navbar ── */
        .gh-navbar {
            background: linear-gradient(135deg, #1a6b3c, #22863a);
            padding: 0 24px;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .gh-navbar-brand {
            color: white;
            font-weight: 700;
            font-size: 15px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .gh-navbar-right { display: flex; align-items: center; gap: 10px; }
        .gh-status {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .05em;
            padding: 4px 14px;
            border-radius: 20px;
        }
        .gh-status.pending  { background: rgba(255,255,255,.15); color: #fff; border: 1px solid rgba(255,255,255,.3); }
        .gh-status.scanning { background: #fff3cd; color: #856404; border: 1px solid #ffc107; }
        .gh-status.done     { background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }
        .gh-status.error    { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
        .btn-back-nav {
            background: rgba(255,255,255,.15);
            border: 1px solid rgba(255,255,255,.3);
            color: white;
            padding: 5px 14px;
            border-radius: 6px;
            font-size: 12px;
            text-decoration: none;
            transition: all .15s;
        }
        .btn-back-nav:hover { background: rgba(255,255,255,.25); color: white; }

        /* ── Layout ── */
        .page-wrap { max-width: 1400px; margin: 0 auto; padding: 20px 20px 60px; }
        .layout-grid { display: grid; grid-template-columns: 300px 1fr; gap: 20px; }

        /* ── Cards ── */
        .gh-card {
            background: white;
            border-radius: 12px;
            border: 1px solid #e5e9f0;
            box-shadow: 0 1px 4px rgba(0,0,0,.06);
            overflow: hidden;
            margin-bottom: 16px;
        }
        .gh-card:last-child { margin-bottom: 0; }
        .gh-card-header {
            padding: 12px 16px;
            border-bottom: 1px solid #f0f2f5;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 600;
            color: #1f2d3d;
            background: #fafbfc;
        }
        .gh-card-icon {
            width: 26px;
            height: 26px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            flex-shrink: 0;
        }
        .icon-green  { background: #d1fae5; }
        .icon-blue   { background: #dbeafe; }
        .icon-yellow { background: #fef3c7; }
        .icon-red    { background: #fee2e2; }
        .icon-gray   { background: #f3f4f6; }
        .gh-card-body { padding: 16px; }

        /* ── Info Table ── */
        .info-table { width: 100%; }
        .info-table tr td { padding: 5px 0; font-size: 12px; border-bottom: 1px solid #f3f4f6; }
        .info-table tr:last-child td { border-bottom: none; }
        .info-table td:first-child { color: #6b7280; }
        .info-table td:last-child  { font-weight: 600; text-align: right; color: #111827; font-family: 'JetBrains Mono', monospace; }

        /* ── Stream ── */
        .stream-box {
            background: #111;
            aspect-ratio: 16/10;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .stream-box img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .stream-waiting { color: #6b7280; text-align: center; font-size: 12px; }
        .stream-waiting .icon { font-size: 32px; opacity: .4; margin-bottom: 8px; }

        /* ── Progress ── */
        .gh-progress-track {
            background: #e5e7eb;
            border-radius: 6px;
            height: 10px;
            overflow: hidden;
        }
        .gh-progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #16a34a, #22c55e);
            border-radius: 6px;
            transition: width .5s ease;
        }

        /* ── Rekap Total Box ── */
        .total-box {
            border-radius: 10px;
            padding: 16px;
            text-align: center;
            margin-bottom: 14px;
        }
        .total-box.green { background: #f0fdf4; border: 1px solid #bbf7d0; }
        .total-box.amber { background: #fffbeb; border: 1px solid #fde68a; }
        .total-box-val {
            font-family: 'JetBrains Mono', monospace;
            font-size: 34px;
            font-weight: 700;
            line-height: 1;
        }
        .total-box.green .total-box-val { color: #16a34a; }
        .total-box.amber .total-box-val { color: #d97706; }
        .total-box-lbl { font-size: 11px; color: #6b7280; margin-top: 4px; }

        /* ── Rekap Bars ── */
        .rekap-item { margin-bottom: 10px; }
        .rekap-item:last-child { margin-bottom: 0; }
        .rekap-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px; font-size: 12px; }
        .rekap-top-label { color: #374151; display: flex; align-items: center; gap: 5px; }
        .rekap-top-count { font-family: 'JetBrains Mono', monospace; font-weight: 600; }
        .rekap-bar-track { background: #f3f4f6; border-radius: 4px; height: 7px; overflow: hidden; }
        .rekap-bar-fill  { height: 100%; border-radius: 4px; transition: width .5s ease; }

        /* ── Akumulasi Grid ── */
        .akum-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
        .akum-box {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 10px;
            text-align: center;
            background: #fafafa;
        }
        .akum-val { font-family: 'JetBrains Mono', monospace; font-size: 20px; font-weight: 700; }
        .akum-lbl { font-size: 10px; color: #9ca3af; margin-top: 3px; }

        /* ── Pending row ── */
        .pending-info {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 10px;
            border-top: 1px solid #f3f4f6;
            margin-top: 8px;
            font-size: 12px;
            color: #6b7280;
        }
        .pending-badge {
            background: #f3f4f6;
            border: 1px solid #e5e7eb;
            color: #374151;
            font-family: 'JetBrains Mono', monospace;
            font-size: 12px;
            font-weight: 600;
            padding: 2px 10px;
            border-radius: 20px;
        }

        /* ── Plant Grid ── */
        .section-title {
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .section-title::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e5e7eb;
        }

        .plant-grid { display: grid; gap: 10px; }
        .plant-cell {
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            padding: 10px 6px;
            text-align: center;
            background: white;
            cursor: pointer;
            transition: all .15s;
            position: relative;
        }
        .plant-cell:hover { box-shadow: 0 2px 8px rgba(0,0,0,.1); transform: translateY(-1px); }

        .plant-cell.ripe,    .plant-cell.matang           { border-color: #fca5a5; background: #fff5f5; }
        .plant-cell.unripe,  .plant-cell.mentah           { border-color: #86efac; background: #f0fdf4; }
        .plant-cell.turning, .plant-cell.setengah_matang  { border-color: #fcd34d; background: #fffbeb; }
        .plant-cell.broken,  .plant-cell.rusak            { border-color: #d1d5db; background: #f9fafb; }
        .plant-cell.scanning-now { border-color: #93c5fd; background: #eff6ff; animation: pulse 1s infinite; }
        @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.6} }

        .plant-pos   { font-size: 9px; color: #9ca3af; margin-bottom: 3px; letter-spacing: .03em; }
        .plant-emoji { font-size: 20px; line-height: 1; }
        .plant-score { font-family: 'JetBrains Mono', monospace; font-size: 13px; font-weight: 600; color: #111827; margin-top: 3px; }
        .plant-id    { font-size: 9px; color: #9ca3af; margin-top: 2px; }
        .plant-buah  { font-size: 9px; color: #6b7280; margin-top: 1px; }

        /* ── Legend ── */
        .legend-row {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            margin-top: 14px;
            padding-top: 12px;
            border-top: 1px solid #f3f4f6;
            font-size: 11px;
            color: #6b7280;
        }
        .legend-item { display: flex; align-items: center; gap: 5px; }
        .legend-dot  { width: 10px; height: 10px; border-radius: 3px; flex-shrink: 0; }

        /* ── Modal ── */
        .gh-modal-backdrop {
            position: fixed; inset: 0;
            background: rgba(0,0,0,.4);
            z-index: 300;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .gh-modal-backdrop.open { display: flex; }
        .gh-modal {
            background: white;
            border-radius: 12px;
            width: 100%;
            max-width: 320px;
            box-shadow: 0 20px 60px rgba(0,0,0,.2);
        }
        .gh-modal-header {
            padding: 14px 18px;
            border-bottom: 1px solid #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .gh-modal-title { font-size: 14px; font-weight: 700; color: #111827; }
        .gh-modal-close {
            background: none; border: 1px solid #e5e7eb;
            color: #6b7280; width: 26px; height: 26px;
            border-radius: 6px; cursor: pointer; font-size: 14px;
            display: flex; align-items: center; justify-content: center;
            transition: all .15s;
        }
        .gh-modal-close:hover { border-color: #ef4444; color: #ef4444; }
        .gh-modal-body { padding: 16px; }
        .gh-modal-row {
            display: flex; align-items: center;
            justify-content: space-between;
            padding: 7px 0;
            border-bottom: 1px solid #f9fafb;
            font-size: 13px;
        }
        .gh-modal-row:last-child { border-bottom: none; }
        .gh-modal-label { color: #6b7280; }
        .gh-modal-val   { font-family: 'JetBrains Mono', monospace; font-weight: 600; color: #111827; }

        /* ── Alert done/error ── */
        .alert-done  { background: #f0fdf4; border: 1px solid #86efac; border-radius: 8px; padding: 10px 14px; text-align: center; font-size: 13px; font-weight: 600; color: #16a34a; margin-top: 10px; }
        .alert-error { background: #fff5f5; border: 1px solid #fca5a5; border-radius: 8px; padding: 10px 14px; text-align: center; font-size: 13px; font-weight: 600; color: #dc2626; margin-top: 10px; }

        @media (max-width: 900px) {
            .layout-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

{{-- ── Navbar ── --}}
<nav class="gh-navbar">
    <a href="{{ url('/scanning') }}" class="gh-navbar-brand">
        🌿 Live {{ $session->penyiraman && !$session->servo_pan ? '💧 Penyiraman' : '🔍 Scanning' }}
        — Sesi #{{ $session->id }}
    </a>
    <div class="gh-navbar-right">
        <span class="gh-status {{ $session->status }}" id="status-badge">
            @if($session->status === 'done')         ✅ SELESAI
            @elseif($session->status === 'scanning') 🔄 SCANNING
            @elseif($session->status === 'error')    ❌ ERROR
            @else                                    ⏳ PENDING
            @endif
        </span>

        <a href="{{ url('/scanning') }}" class="btn-back-nav">← Kembali</a>
    </div>
</nav>

{{-- ── Modal Detail Tanaman ── --}}
<div class="gh-modal-backdrop" id="detail-modal">
    <div class="gh-modal">
        <div class="gh-modal-header">
            <span class="gh-modal-title" id="modal-title">Detail Tanaman</span>
            <button class="gh-modal-close" onclick="closeModal()">×</button>
        </div>
        <div class="gh-modal-body" id="modal-body"></div>
    </div>
</div>

<div class="page-wrap">
    <div class="layout-grid">

        {{-- ══ KOLOM KIRI ══ --}}
        <div>

            {{-- Info Sesi --}}
            <div class="gh-card">
                <div class="gh-card-header">
                    <div class="gh-card-icon icon-green">📋</div>
                    Info Sesi #{{ $session->id }}
                </div>
                <div class="gh-card-body">
                    <table class="info-table">
                        <tr><td>Susunan</td><td>{{ $session->susunan_tanaman }}</td></tr>
                        <tr><td>Jumlah</td><td>{{ $session->jumlah_tanaman }} tanaman</td></tr>
                        <tr><td>Jarak Tanaman</td><td>{{ $session->jarak_antar_tanaman }} cm</td></tr>
                        <tr><td>Jarak Frame</td><td>{{ $session->jarak_frame_ke_tanaman }} cm</td></tr>
                        @if($session->servo_pan)
                        <tr><td>Servo Pan</td><td>{{ $session->servo_pan }}°</td></tr>
                        <tr><td>Servo Tilt</td><td>{{ $session->servo_tilt }}°</td></tr>
                        @endif
                        <tr><td>Penyiraman</td><td>{{ $session->penyiraman ? '✅ Aktif' : '❌ Tidak' }}</td></tr>
                        <tr><td>Mulai</td><td>{{ $session->started_at ? $session->started_at->format('H:i:s') : '—' }}</td></tr>
                        <tr><td>Selesai</td><td>{{ $session->finished_at ? $session->finished_at->format('H:i:s') : '—' }}</td></tr>
                    </table>
                </div>
            </div>

            {{-- Foto Hasil Scan Terbaru --}}
            <div class="gh-card">
                <div class="gh-card-header">
                    <div class="gh-card-icon icon-blue">📸</div>
                    Foto Hasil Scan Terbaru
                    <span style="margin-left:auto;font-size:10px;color:#9ca3af;font-weight:400" id="foto-label">Menunggu data...</span>
                </div>
                <div class="gh-card-body" style="padding:12px">
                    <div class="stream-box" id="stream-container">
                        @php $latestResult = $results->whereNotNull('image_path')->last(); @endphp
                        @if($latestResult && $latestResult->image_path)
                            <img src="{{ $latestResult->image_path }}" id="stream-img" alt="Foto YOLO" style="width:100%;height:100%;object-fit:cover;display:block;border-radius:6px" onerror="streamError()">
                        @else
                            <div class="stream-waiting" id="stream-waiting">
                                <div class="icon">📷</div>
                                <div>Menunggu foto dari Dafa...</div>
                                <div style="font-size:11px;margin-top:4px;color:#9ca3af">Foto YOLO akan muncul otomatis</div>
                            </div>
                        @endif
                    </div>
                    {{-- Info tanaman yang sedang di-scan --}}
                    <div id="current-plant-info" style="margin-top:10px;padding:8px 10px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;font-size:12px;display:none">
                        <div style="font-weight:600;color:#16a34a" id="current-plant-name">—</div>
                        <div style="color:#6b7280;margin-top:2px" id="current-plant-detail">—</div>
                    </div>
                </div>
            </div>

            {{-- Progress --}}
            <div class="gh-card">
                <div class="gh-card-header">
                    <div class="gh-card-icon icon-yellow">📊</div>
                    Progress Scanning
                </div>
                <div class="gh-card-body">
                    <div class="gh-progress-track">
                        <div class="gh-progress-fill" id="progress-bar" style="width:{{ $session->progress }}%"></div>
                    </div>
                    <div style="display:flex;justify-content:space-between;margin-top:8px;font-size:12px;color:#6b7280">
                        <span><span id="scanned-count" style="font-weight:600;color:#111827">{{ $results->count() }}</span> / {{ $session->jumlah_tanaman }} tanaman</span>
                        <span id="progress-text" style="font-family:'JetBrains Mono',monospace;font-weight:700;color:#16a34a">{{ $session->progress }}%</span>
                    </div>
                </div>
            </div>

            {{-- Rekap Sesi Ini --}}
            <div class="gh-card">
                <div class="gh-card-header">
                    <div class="gh-card-icon icon-green">📈</div>
                    Rekap Sesi #{{ $session->id }}
                </div>
                <div class="gh-card-body">
                    <div class="total-box green">
                        <div class="total-box-val" id="sesi-total-buah">{{ $rekap['total_buah'] }}</div>
                        <div class="total-box-lbl">Total Buah · Sesi Ini</div>
                    </div>
                    @php $maxSesi = max($rekap['total_buah'], 1); @endphp

                    <div class="rekap-item">
                        <div class="rekap-top">
                            <span class="rekap-top-label">🌶️ Ripe (Matang)</span>
                            <span class="rekap-top-count" style="color:#dc2626" id="scount-ripe">{{ $rekap['count_ripe'] }}</span>
                        </div>
                        <div class="rekap-bar-track">
                            <div class="rekap-bar-fill" id="sbar-ripe" style="background:#ef4444;width:{{ round($rekap['count_ripe']/$maxSesi*100) }}%"></div>
                        </div>
                    </div>
                    <div class="rekap-item">
                        <div class="rekap-top">
                            <span class="rekap-top-label">🌱 Unripe (Mentah)</span>
                            <span class="rekap-top-count" style="color:#16a34a" id="scount-unripe">{{ $rekap['count_unripe'] }}</span>
                        </div>
                        <div class="rekap-bar-track">
                            <div class="rekap-bar-fill" id="sbar-unripe" style="background:#22c55e;width:{{ round($rekap['count_unripe']/$maxSesi*100) }}%"></div>
                        </div>
                    </div>
                    <div class="rekap-item">
                        <div class="rekap-top">
                            <span class="rekap-top-label">🫑 Turning (½ Matang)</span>
                            <span class="rekap-top-count" style="color:#d97706" id="scount-turning">{{ $rekap['count_turning'] }}</span>
                        </div>
                        <div class="rekap-bar-track">
                            <div class="rekap-bar-fill" id="sbar-turning" style="background:#f59e0b;width:{{ round($rekap['count_turning']/$maxSesi*100) }}%"></div>
                        </div>
                    </div>
                    <div class="rekap-item">
                        <div class="rekap-top">
                            <span class="rekap-top-label">🍂 Broken (Rusak)</span>
                            <span class="rekap-top-count" style="color:#6b7280" id="scount-broken">{{ $rekap['count_broken'] }}</span>
                        </div>
                        <div class="rekap-bar-track">
                            <div class="rekap-bar-fill" id="sbar-broken" style="background:#9ca3af;width:{{ round($rekap['count_broken']/$maxSesi*100) }}%"></div>
                        </div>
                    </div>

                    <div class="pending-info">
                        <span>⏳ Belum di-scan</span>
                        <span class="pending-badge" id="count-pending">{{ $session->jumlah_tanaman - $results->count() }}</span>
                    </div>

                    @if($session->status === 'done')
                        <div class="alert-done">✅ Scanning Selesai!</div>
                    @elseif($session->status === 'error')
                        <div class="alert-error">❌ Session Error</div>
                    @endif
                </div>
            </div>

            {{-- Total Akumulasi --}}
            <div class="gh-card">
                <div class="gh-card-header">
                    <div class="gh-card-icon icon-yellow">🗂️</div>
                    Total Akumulasi (Semua Sesi)
                </div>
                <div class="gh-card-body">
                    <div class="total-box amber">
                        <div class="total-box-val" id="total-semua-buah">{{ $rekapTotal['total_buah'] }}</div>
                        <div class="total-box-lbl">Total Buah · {{ $rekapTotal['session_done'] }} Sesi Selesai</div>
                    </div>
                    <div class="akum-grid">
                        <div class="akum-box">
                            <div class="akum-val" style="color:#dc2626" id="total-ripe">{{ $rekapTotal['count_ripe'] }}</div>
                            <div class="akum-lbl">🌶️ Ripe</div>
                        </div>
                        <div class="akum-box">
                            <div class="akum-val" style="color:#16a34a" id="total-unripe">{{ $rekapTotal['count_unripe'] }}</div>
                            <div class="akum-lbl">🌱 Unripe</div>
                        </div>
                        <div class="akum-box">
                            <div class="akum-val" style="color:#d97706" id="total-turning">{{ $rekapTotal['count_turning'] }}</div>
                            <div class="akum-lbl">🫑 Turning</div>
                        </div>
                        <div class="akum-box">
                            <div class="akum-val" style="color:#6b7280" id="total-broken">{{ $rekapTotal['count_broken'] }}</div>
                            <div class="akum-lbl">🍂 Broken</div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- ══ KOLOM KANAN: Grid Tanaman ══ --}}
        <div>
            <div class="gh-card" style="height:100%">
                <div class="gh-card-header">
                    <div class="gh-card-icon icon-green">🌱</div>
                    Peta Tanaman · {{ $session->susunan_tanaman }} · {{ $session->baris }} Baris × {{ $session->kolom }} Kolom
                    <span style="margin-left:auto;font-size:11px;color:#9ca3af;font-weight:400">Klik tanaman untuk detail</span>
                </div>
                <div class="gh-card-body">
                    <div class="plant-grid" id="plant-grid"
                         style="grid-template-columns: repeat({{ $session->kolom }}, 1fr); gap: 10px;">
                        @for($i = 1; $i <= $session->jumlah_tanaman; $i++)
                        @php
                            $result = $results->firstWhere('nomor_tanaman', $i);
                            $baris  = ceil($i / $session->kolom);
                            $kolom  = (($i - 1) % $session->kolom) + 1;
                            $kat    = $result ? $result->kategori : null;
                            $icon   = match($kat) {
                                'ripe',    'matang'          => '🌶️',
                                'turning', 'setengah_matang' => '🫑',
                                'unripe',  'mentah'          => '🌱',
                                'broken',  'rusak'           => '🍂',
                                default                      => '🪴',
                            };
                        @endphp
                        <div class="plant-cell {{ $kat ?? '' }}" id="plant-{{ $i }}" onclick="showDetail({{ $i }})">
                            <div class="plant-pos">B{{ $baris }}-K{{ $kolom }}</div>
                            <div class="plant-emoji">{{ $icon }}</div>
                            <div class="plant-score">{{ $result ? number_format($result->ripeness_score,1).'%' : '—' }}</div>
                            <div class="plant-id">#{{ str_pad($i,2,'0',STR_PAD_LEFT) }}</div>
                            @if($result && $result->total_buah > 0)
                            <div class="plant-buah">{{ $result->total_buah }} buah</div>
                            @endif
                        </div>
                        @endfor
                    </div>

                    {{-- Legend --}}
                    <div class="legend-row">
                        <div class="legend-item"><div class="legend-dot" style="background:#fca5a5;border:1px solid #fca5a5"></div>Ripe</div>
                        <div class="legend-item"><div class="legend-dot" style="background:#86efac;border:1px solid #86efac"></div>Unripe</div>
                        <div class="legend-item"><div class="legend-dot" style="background:#fcd34d;border:1px solid #fcd34d"></div>Turning</div>
                        <div class="legend-item"><div class="legend-dot" style="background:#d1d5db;border:1px solid #d1d5db"></div>Broken</div>
                        <div class="legend-item"><div class="legend-dot" style="background:white;border:1px solid #e5e7eb"></div>Belum scan</div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
const SESSION_ID = {{ $session->id }};
const TOTAL      = {{ $session->jumlah_tanaman }};
let   plantData  = {};

function getIcon(k) {
    const m = { ripe:'🌶️', matang:'🌶️', turning:'🫑', setengah_matang:'🫑',
                unripe:'🌱', mentah:'🌱', broken:'🍂', rusak:'🍂' };
    return m[k] || '🪴';
}

// ── Pusher ──
const pusher  = new Pusher('{{ env("PUSHER_APP_KEY") }}', { cluster: '{{ env("PUSHER_APP_CLUSTER") }}' });
const channel = pusher.subscribe('scanning-' + SESSION_ID);

channel.bind('scanning.progress', function(data) {
    document.getElementById('progress-bar').style.width  = data.progress + '%';
    document.getElementById('progress-text').textContent = data.progress + '%';

    const s = document.getElementById('status-badge');
    if      (data.status === 'done')     { s.textContent = '✅ SELESAI';  s.className = 'gh-status done'; }
    else if (data.status === 'scanning') { s.textContent = '🔄 SCANNING'; s.className = 'gh-status scanning'; }
    else if (data.status === 'error')    { s.textContent = '❌ ERROR';    s.className = 'gh-status error'; }
    else                                 { s.textContent = '⏳ PENDING';  s.className = 'gh-status pending'; }

    if (data.stream_url) updateStream(data.stream_url);
    if (data.rekap)      updateRekapSesi(data.rekap);
    fetchResults();
});

function updateStream(url) {
    const c = document.getElementById('stream-container');
    let img = document.getElementById('stream-img');
    if (!img) {
        c.innerHTML = `<img id="stream-img" src="${url}" alt="Live Stream"
            onerror="streamError()" style="width:100%;height:100%;object-fit:cover;display:block;">`;
    } else { img.src = url; }
}
function streamError() {
    document.getElementById('stream-container').innerHTML =
        '<div class="stream-waiting"><div class="icon">❌</div><div style="color:#dc2626">Foto tidak dapat dimuat</div></div>';
}

function updateRekapSesi(r) {
    const total = Math.max(r.total_buah, 1);
    document.getElementById('sesi-total-buah').textContent = r.total_buah;
    [['ripe',r.count_ripe],['unripe',r.count_unripe],['turning',r.count_turning],['broken',r.count_broken]]
    .forEach(([k,v]) => {
        const bar = document.getElementById('sbar-'+k);
        const cnt = document.getElementById('scount-'+k);
        if (bar) bar.style.width = Math.round(v/total*100)+'%';
        if (cnt) cnt.textContent = v;
    });
}

function updateRekapTotal(r) {
    document.getElementById('total-semua-buah').textContent = r.total_buah;
    document.getElementById('total-ripe').textContent    = r.count_ripe;
    document.getElementById('total-unripe').textContent  = r.count_unripe;
    document.getElementById('total-turning').textContent = r.count_turning;
    document.getElementById('total-broken').textContent  = r.count_broken;
}

function fetchResults() {
    fetch('{{ url('/api/scanning/session') }}/' + SESSION_ID + '/results')
    .then(r => r.json())
    .then(data => {
        const results = data.results || [];
        let latestImagePath = null;
        let latestPlantNum  = null;
        let latestKategori  = null;

        results.forEach(r => {
            const cell = document.getElementById('plant-' + r.nomor_tanaman);
            if (!cell) return;
            plantData[r.nomor_tanaman] = r;
            cell.className = 'plant-cell ' + (r.kategori || '');
            cell.querySelector('.plant-emoji').textContent = getIcon(r.kategori);
            cell.querySelector('.plant-score').textContent = parseFloat(r.ripeness_score||0).toFixed(1)+'%';
            let buahEl = cell.querySelector('.plant-buah');
            if (!buahEl) { buahEl = document.createElement('div'); buahEl.className = 'plant-buah'; cell.appendChild(buahEl); }
            buahEl.textContent = r.total_buah > 0 ? r.total_buah + ' buah' : '';

            // Simpan foto terbaru (tanaman dengan nomor terbesar = paling baru di-scan)
            if (r.image_path) {
                latestImagePath = r.image_path;
                latestPlantNum  = r.nomor_tanaman;
                latestKategori  = r.kategori;
            }
        });

        document.getElementById('scanned-count').textContent = results.length;
        document.getElementById('count-pending').textContent = TOTAL - results.length;
        if (data.rekap)       updateRekapSesi(data.rekap);
        if (data.rekap_total) updateRekapTotal(data.rekap_total);

        // Update foto terbaru di sidebar
        if (latestImagePath) {
            const container = document.getElementById('stream-container');
            let img = document.getElementById('stream-img');
            if (!img) {
                container.innerHTML = `<img id="stream-img" src="${latestImagePath}"
                    alt="Foto YOLO" onerror="streamError()"
                    style="width:100%;height:100%;object-fit:cover;display:block;border-radius:6px">`;
            } else if (img.src !== latestImagePath) {
                img.src = latestImagePath;
            }
            // Update label
            const lbl = document.getElementById('foto-label');
            if (lbl) lbl.textContent = 'Tanaman #' + String(latestPlantNum).padStart(2,'0');
            // Update info tanaman
            const info = document.getElementById('current-plant-info');
            const name = document.getElementById('current-plant-name');
            const detail = document.getElementById('current-plant-detail');
            if (info && name && detail) {
                info.style.display = 'block';
                name.textContent = 'Tanaman #' + String(latestPlantNum).padStart(2,'0') + ' — ' + (latestKategori || '—').toUpperCase();
                const r = plantData[latestPlantNum];
                detail.textContent = r ? `Total: ${r.total_buah} buah | Ripe: ${r.count_ripe} | Unripe: ${r.count_unripe} | Turning: ${r.count_turning} | Broken: ${r.count_broken}` : '';
            }
        }
    });
}

function showDetail(num) {
    const r = plantData[num];
    document.getElementById('modal-title').textContent = `Tanaman #${String(num).padStart(2,'0')}`;
    document.getElementById('modal-body').innerHTML = r ? `
        <div class="gh-modal-row"><span class="gh-modal-label">Kategori</span><span class="gh-modal-val">${r.kategori || '—'}</span></div>
        <div class="gh-modal-row"><span class="gh-modal-label">Ripeness Score</span><span class="gh-modal-val">${parseFloat(r.ripeness_score||0).toFixed(1)}%</span></div>
        <div class="gh-modal-row"><span class="gh-modal-label">Total Buah</span><span class="gh-modal-val">${r.total_buah||0}</span></div>
        <div class="gh-modal-row"><span class="gh-modal-label">🌶️ Ripe</span><span class="gh-modal-val" style="color:#dc2626">${r.count_ripe||0}</span></div>
        <div class="gh-modal-row"><span class="gh-modal-label">🌱 Unripe</span><span class="gh-modal-val" style="color:#16a34a">${r.count_unripe||0}</span></div>
        <div class="gh-modal-row"><span class="gh-modal-label">🫑 Turning</span><span class="gh-modal-val" style="color:#d97706">${r.count_turning||0}</span></div>
        <div class="gh-modal-row"><span class="gh-modal-label">🍂 Broken</span><span class="gh-modal-val" style="color:#6b7280">${r.count_broken||0}</span></div>
    ` : `<div style="text-align:center;padding:24px;color:#9ca3af">Tanaman ini belum di-scan</div>`;
    document.getElementById('detail-modal').classList.add('open');
}
function closeModal() { document.getElementById('detail-modal').classList.remove('open'); }
document.getElementById('detail-modal').addEventListener('click', e => {
    if (e.target === document.getElementById('detail-modal')) closeModal();
});

@if($session->status !== 'done' && $session->status !== 'error')
fetchResults();
setInterval(fetchResults, 3000);
@endif
</script>
</body>
</html>