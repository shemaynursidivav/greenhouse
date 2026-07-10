<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Gantry — Greenhouse Monitor</title>
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
        .table th { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: #6c757d; background: #f8f9fa; }
        .table td { font-size: 13px; vertical-align: middle; }
        .card-header-custom { background: #f8f9fa; border-bottom: 1px solid #e9ecef; padding: 12px 16px; font-size: 13px; font-weight: 600; color: #1f2d3d; border-radius: 10px 10px 0 0; }
        .sensor-mini { background: white; border: 1px solid #e9ecef; border-radius: 10px; padding: 14px; text-align: center; border-top: 3px solid #1e3a5f; }
        .sm-label { font-size: 11px; color: #6c757d; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; margin-bottom: 6px; }
        .sm-value { font-size: 22px; font-weight: 700; color: #111827; font-family: monospace; }
        .sm-unit  { font-size: 12px; color: #6c757d; margin-left: 2px; }
        .rpi-badge { font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 4px; }
        .rpi-on  { background: #d1fae5; color: #065f46; }
        .rpi-off { background: #fee2e2; color: #991b1b; }
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

    @if(session('success'))
        <div class="alert alert-success py-2 small">✅ {{ session('success') }}</div>
    @endif
    @if(session('gantry_error'))
        <div class="alert alert-danger py-2 small">⚠️ {{ session('gantry_error') }}</div>
    @endif
    @if($error)
        <div class="alert alert-warning py-2 small">🔌 {{ $error }}</div>
    @endif

    {{-- ── Sensor Gantry (RPi Dafa) ── --}}
    <div class="card mb-4">
        <div class="card-header-custom d-flex justify-content-between align-items-center">
            <span>📡 Sensor Gantry · RPi Dafa</span>
            <span class="rpi-badge rpi-off" id="rpi-status">● MENGHUBUNGKAN…</span>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <div class="sensor-mini">
                        <div class="sm-label">Suhu Udara</div>
                        <div class="sm-value"><span id="s-temp">—</span><span class="sm-unit">°C</span></div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="sensor-mini">
                        <div class="sm-label">Kelembapan Udara</div>
                        <div class="sm-value"><span id="s-hum">—</span><span class="sm-unit">%</span></div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="sensor-mini">
                        <div class="sm-label">Intensitas Cahaya</div>
                        <div class="sm-value"><span id="s-lux">—</span><span class="sm-unit">lux</span></div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="sensor-mini">
                        <div class="sm-label">Kecepatan Fan</div>
                        <div class="sm-value"><span id="s-fan">—</span><span class="sm-unit">%</span></div>
                    </div>
                </div>
            </div>
            <hr class="my-3">
            <div class="row g-3" id="soil-row">
                <div class="col-6 col-md-4">
                    <div class="sensor-mini">
                        <div class="sm-label">Kelembapan Tanah 1</div>
                        <div class="sm-value"><span id="s-soil1">—</span><span class="sm-unit">%</span></div>
                    </div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="sensor-mini">
                        <div class="sm-label">Kelembapan Tanah 2</div>
                        <div class="sm-value"><span id="s-soil2">—</span><span class="sm-unit">%</span></div>
                    </div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="sensor-mini">
                        <div class="sm-label">Kelembapan Tanah 3</div>
                        <div class="sm-value"><span id="s-soil3">—</span><span class="sm-unit">%</span></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer text-muted" style="font-size:12px;">
            <span id="rpi-time">Menunggu data dari RPi…</span>
        </div>
    </div>

    {{-- Panel Mulai Sesi --}}
    <div class="card mb-4">
        <div class="card-body">
            <h6 class="fw-bold mb-3">🚀 Mulai Sesi Gantry</h6>
            <div class="d-flex gap-2 flex-wrap">
                <form method="POST" action="{{ route('gantry.start') }}" class="mb-0">
                    @csrf
                    <input type="hidden" name="sessionType" value="SCAN">
                    <button type="submit" class="btn btn-success btn-sm fw-semibold px-3">🔍 Mulai Scanning</button>
                </form>
                <form method="POST" action="{{ route('gantry.start') }}" class="mb-0">
                    @csrf
                    <input type="hidden" name="sessionType" value="WATERING">
                    <button type="submit" class="btn btn-primary btn-sm fw-semibold px-3">💧 Mulai Penyiraman</button>
                </form>
            </div>
            <div class="small text-muted mt-2">
                Sesi dikontrol oleh sistem gantry (Dafa). Hanya satu sesi berjalan dalam satu waktu.
            </div>
        </div>
    </div>

    {{-- Riwayat Sesi --}}
    <div class="card">
        <div class="card-body">
            <h6 class="fw-bold mb-3">📋 Riwayat Sesi</h6>
            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead>
                        <tr>
                            <th>ID</th><th>Tipe</th><th>Status</th><th>Total Tanaman</th><th>Mulai</th><th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sessions as $s)
                        <tr>
                            <td class="fw-semibold">#{{ $s['id'] ?? '—' }}</td>
                            <td><span class="badge bg-secondary">{{ $s['sessionType'] ?? '—' }}</span></td>
                            <td><code style="font-size:12px">{{ $s['status'] ?? '—' }}</code></td>
                            <td>{{ $s['totalPlants'] ?? '—' }}</td>
                            <td style="font-size:12px">{{ $s['startedAt'] ?? '—' }}</td>
                            <td><a href="{{ route('gantry.watch', $s['id']) }}" class="btn btn-outline-success btn-sm">👁 Live</a></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <div style="font-size:24px;margin-bottom:8px">📡</div>
                                Belum ada sesi (atau sistem gantry belum terhubung)
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<script>
function muatSensorGantry() {
    fetch('{{ route('gantry.sensors') }}')
        .then(r => r.json())
        .then(d => {
            const badge = document.getElementById('rpi-status');
            if (!d.online) {
                badge.textContent = '● OFFLINE';
                badge.className = 'rpi-badge rpi-off';
                document.getElementById('rpi-time').textContent = 'RPi Dafa tidak terhubung.';
                return;
            }
            badge.textContent = '● ONLINE';
            badge.className = 'rpi-badge rpi-on';

            const f = (v, dec=1) => (v === null || v === undefined) ? '—' : Number(v).toFixed(dec);
            document.getElementById('s-temp').textContent = f(d.temperature_c);
            document.getElementById('s-hum').textContent  = f(d.humidity_pct);
            document.getElementById('s-lux').textContent  = f(d.lux);
            document.getElementById('s-fan').textContent  = f(d.fan_speed_pct);

            const soil = d.soil || [];
            document.getElementById('s-soil1').textContent = soil[0] ? f(soil[0].moisture_pct) : '—';
            document.getElementById('s-soil2').textContent = soil[1] ? f(soil[1].moisture_pct) : '—';
            document.getElementById('s-soil3').textContent = soil[2] ? f(soil[2].moisture_pct) : '—';

            document.getElementById('rpi-time').textContent =
                'Update terakhir: ' + new Date().toLocaleTimeString('id-ID');
        })
        .catch(() => {
            const badge = document.getElementById('rpi-status');
            badge.textContent = '● ERROR';
            badge.className = 'rpi-badge rpi-off';
        });
}

muatSensorGantry();
setInterval(muatSensorGantry, 5000);
</script>

</body>
</html>