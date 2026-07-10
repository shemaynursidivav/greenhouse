<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Sesi #{{ $sessionId }} — Greenhouse Monitor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>

        :root{ --navy:#1e3a5f; --blue:#2563eb; }
        .topbar{background:#1e3a5f;color:#fff;padding:12px 22px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px}
        .topbar .brand{font-weight:700;font-size:16px}
        .topbar .nav-links{display:flex;gap:8px;flex-wrap:wrap;align-items:center}
        .topbar .nav-links a,.topbar .nav-links button{background:transparent;border:1px solid rgba(255,255,255,.35);color:#fff;border-radius:8px;padding:6px 12px;font-size:12.5px;font-weight:600;text-decoration:none;cursor:pointer}
        .topbar .nav-links a:hover{background:rgba(255,255,255,.12)}
        .topbar .nav-links .logout{background:rgba(220,38,38,.85)}

        body { background: #eef2f6; font-size: 14px; }
        .navbar { background: #1e3a5f !important; }
        .card { border-radius: 10px; border: none; box-shadow: 0 1px 4px rgba(0,0,0,.08); }
        #log { max-height: 220px; overflow-y: auto; font-family: monospace; font-size: 11px; }
        #log > div { padding: 3px 8px; border-bottom: 1px solid #eee; }
        #big-img { width: 100%; border-radius: 8px; background: #111; min-height: 260px; object-fit: contain; }
        .det-badge { font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 4px; margin-right: 4px; }
        .d-unripe  { background: #d1fae5; color: #065f46; }
        .d-turning { background: #fef3c7; color: #92400e; }
        .d-ripe    { background: #fee2e2; color: #991b1b; }
        .d-broken  { background: #e5e7eb; color: #374151; }
        .grid-item { border: 1px solid #e9ecef; border-radius: 8px; overflow: hidden; background: white; }
        .grid-item img { width: 100%; height: 90px; object-fit: cover; background: #111; }
        .grid-cap { font-size: 10px; padding: 4px 6px; color: #374151; }
        .prog-bar { height: 8px; border-radius: 4px; background: #e9ecef; overflow: hidden; }
        .prog-fill { height: 100%; background: #1e3a5f; width: 0%; transition: width .3s; }
    </style>
</head>
<body>

<div class="topbar">
    <span class="brand">🌿 Greenhouse Monitor</span>
    <div class="nav-links">
        <a href="{{ url('/') }}">Dashboard</a>
        <a href="{{ url('/sensors') }}">Kelola Sensor</a>
        <a href="{{ route('gantry.live') }}">Gantry</a>
        
        <a href="{{ route('gantry.recap') }}">Rekap</a>
        <a href="{{ route('soil.index') }}">Sensor Rio</a>
        <form method="POST" action="{{ url('/logout') }}" style="margin:0">@csrf<button class="logout">Logout</button></form>
    </div>
</div>

<div class="container-fluid py-3 px-4" style="max-width:1100px">

    @if($error)
        <div class="alert alert-warning py-2 small">🔌 {{ $error }}</div>
    @endif

    {{-- Header + progress --}}
    <div class="card mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <h6 class="fw-bold mb-1">👁 Live Sesi #{{ $sessionId }}</h6>
                    <span class="small text-muted" id="conn-status">Menghubungkan…</span>
                </div>
                <form method="POST" action="{{ route('gantry.stop', $sessionId) }}" class="mb-0">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-sm">⏹ Stop</button>
                </form>
            </div>
            <div class="prog-bar"><div class="prog-fill" id="prog"></div></div>
            <div class="small text-muted mt-1"><span id="prog-text">0 / 27 tanaman</span></div>
        </div>
    </div>

    <div class="row g-3">
        {{-- Gambar besar terbaru --}}
        <div class="col-md-7">
            <div class="card">
                <div class="card-body">
                    <h6 class="fw-bold mb-2">🌶️ Tanaman Terbaru: <span id="cur-plant">—</span></h6>
                    <img id="big-img" src="" alt="Menunggu gambar…">
                    <div class="mt-2" id="cur-det"><span class="text-muted small">Menunggu hasil deteksi…</span></div>
                </div>
            </div>
        </div>

        {{-- Event stream --}}
        <div class="col-md-5">
            <div class="card">
                <div class="card-body">
                    <h6 class="fw-bold mb-2">📡 Event Stream</h6>
                    <div id="log"><div class="text-muted">Menunggu event…</div></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Grid riwayat semua tanaman --}}
    <div class="card mt-3">
        <div class="card-body">
            <h6 class="fw-bold mb-3">🖼️ Riwayat Scan (Tanaman 1–27)</h6>
            <div class="row g-2" id="grid"></div>
        </div>
    </div>

</div>

<script>
    const eventsUrl = @json($eventsUrl);
    const rpiBase   = @json($dashBase);
    const log    = document.getElementById('log');
    const status = document.getElementById('conn-status');
    const bigImg = document.getElementById('big-img');
    const grid   = document.getElementById('grid');
    let scanned = 0;

    function imgUrl(path) {
        if (!path) return '';
        return path.startsWith('http') ? path : rpiBase + path;
    }

    function detBadges(dets) {
        if (!dets || !dets.length) return '<span class="text-muted small">tidak ada deteksi</span>';
        return dets.map(d =>
            `<span class="det-badge d-${d.cls}">${d.cls}: ${d.count} (${Math.round(d.confidence*100)}%)</span>`
        ).join('');
    }

    function addLog(e) {
        const row = document.createElement('div');
        row.innerHTML = `<strong>[${e.type}]</strong> ${e.plant_id ? 'plant '+e.plant_id : ''}`;
        log.insertBefore(row, log.firstChild);
    }

    try {
        const es = new EventSource(eventsUrl);
        es.onopen = () => status.textContent = '🟢 Terhubung';

        es.onmessage = (msg) => {
            let e; try { e = JSON.parse(msg.data); } catch { return; }
            addLog(e);

            if (e.type === 'session_started') {
                const tot = e.total_plants || 27;
                document.getElementById('prog-text').textContent = `0 / ${tot} tanaman`;
            }

            if (e.type === 'plant_scanned') {
                scanned++;
                const url = imgUrl(e.annotated_image_url || e.image_url);

                // gambar besar
                document.getElementById('cur-plant').textContent = 'T-' + String(e.plant_id).padStart(2,'0');
                if (url) bigImg.src = url;
                document.getElementById('cur-det').innerHTML = detBadges(e.detections);

                // progress
                document.getElementById('prog').style.width = (scanned/27*100) + '%';
                document.getElementById('prog-text').textContent = `${scanned} / 27 tanaman`;

                // grid
                const col = document.createElement('div');
                col.className = 'col-4 col-md-2';
                col.innerHTML = `
                    <div class="grid-item">
                        <img src="${url}" alt="T-${e.plant_id}">
                        <div class="grid-cap">
                            <strong>T-${String(e.plant_id).padStart(2,'0')}</strong><br>
                            ${(e.detections||[]).map(d=>d.cls).join(', ')||'—'}
                        </div>
                    </div>`;
                grid.appendChild(col);
            }

            if (e.type === 'session_complete') {
                status.textContent = '✅ Scan selesai';
                const s = e.summary?.ripeness;
                if (s) {
                    document.getElementById('cur-det').innerHTML =
                        `<strong>Total:</strong> ${detBadges([
                            {cls:'ripe',count:s.ripe,confidence:1},
                            {cls:'turning',count:s.turning,confidence:1},
                            {cls:'unripe',count:s.unripe,confidence:1},
                            {cls:'broken',count:s.broken,confidence:1},
                        ])}`;
                }
                es.close();
            }
            if (e.type === 'session_reconnect') { status.textContent = 'ℹ️ Sesi sudah berakhir'; es.close(); }
        };

        es.onerror = () => status.textContent = '🔴 Stream terputus / RPi offline';
    } catch (err) {
        status.textContent = '🔴 Tidak bisa membuka stream';
    }
</script>

</body>
</html>