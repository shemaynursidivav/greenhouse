<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Greenhouse Monitor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <style>
        body { background: #f0f4f8; font-size: 14px; }
        .navbar { background: #198754 !important; }

        /* ── Cards ── */
        .card { border-radius: 10px; border: none; box-shadow: 0 1px 4px rgba(0,0,0,.08); }
        .card-header-custom {
            background: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
            padding: 12px 16px;
            font-size: 13px;
            font-weight: 600;
            color: #1f2d3d;
            border-radius: 10px 10px 0 0;
        }

        /* ── Sensor Cards ── */
        .sensor-card {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 16px;
            border-top: 3px solid #198754;
            position: relative;
        }
        .sensor-card.s-warning { border-top-color: #f59e0b; }
        .sensor-card.s-danger  { border-top-color: #ef4444; }
        .sensor-card.s-normal  { border-top-color: #198754; }
        .sc-label  { font-size: 11px; color: #6c757d; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; margin-bottom: 6px; }
        .sc-value  { font-size: 24px; font-weight: 700; color: #111827; font-family: monospace; }
        .sc-unit   { font-size: 13px; color: #6c757d; margin-left: 2px; }
        .sc-device { font-size: 11px; color: #9ca3af; margin-top: 6px; }
        .sc-badge  { position: absolute; top: 12px; right: 12px; font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 4px; }
        .badge-normal  { background: #d1fae5; color: #065f46; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-danger  { background: #fee2e2; color: #991b1b; }

        /* ── Actuator ── */
        .actuator-section { background: white; border: 1px solid #e9ecef; border-radius: 10px; overflow: hidden; margin-bottom: 20px; }
        .actuator-item { border-right: 1px solid #f3f4f6; padding: 20px; text-align: center; }
        .actuator-item:last-child { border-right: none; }
        .actuator-name { font-size: 13px; font-weight: 600; color: #111827; margin-bottom: 14px; }
        .actuator-status { font-size: 11px; color: #6c757d; margin-top: 8px; min-height: 16px; }
        .actuator-status.ok  { color: #16a34a; }
        .actuator-status.err { color: #dc2626; }
        .speed-row { display: flex; gap: 6px; margin-top: 10px; }
        .speed-row input { flex: 1; font-size: 13px; }

        /* ── Live Scanning Panel ── */
        .live-frame-wrap { position: relative; overflow: hidden; border-radius: 0 0 10px 10px; }
        #live-frame { display: block; }
        .live-overlay {
            position: absolute; bottom: 8px; left: 8px;
            background: rgba(0,0,0,.6); color: #fff;
            padding: 4px 10px; border-radius: 4px; font-size: 12px;
        }
        .live-status-badge {
            font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 4px;
        }
        .live-on  { background: #d1fae5; color: #065f46; }
        .live-off { background: #fee2e2; color: #991b1b; }
        .live-progress { height: 4px; border-radius: 0; background: #e9ecef; }
        .live-progress-bar { height: 100%; background: #198754; width: 0%; transition: width .5s; }

        /* ── Log Table ── */
        .log-table th { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: #6c757d; background: #f8f9fa; }
        .log-table td { font-size: 13px; color: #374151; vertical-align: middle; }
        .st-badge { font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 4px; }
        .st-normal  { background: #d1fae5; color: #065f46; }
        .st-warning { background: #fef3c7; color: #92400e; }
        .st-danger  { background: #fee2e2; color: #991b1b; }

        /* ── Toast ── */
        #toast-container { position: fixed; top: 70px; right: 16px; z-index: 9999; display: flex; flex-direction: column; gap: 8px; }
        .gh-toast { background: white; border: 1px solid #e9ecef; border-radius: 10px; padding: 12px 16px; min-width: 260px; max-width: 320px; display: flex; align-items: flex-start; gap: 10px; box-shadow: 0 4px 16px rgba(0,0,0,.12); animation: slideIn .2s ease; }
        .gh-toast.t-danger  { border-left: 4px solid #ef4444; }
        .gh-toast.t-warning { border-left: 4px solid #f59e0b; }
        .gh-toast.t-normal  { border-left: 4px solid #198754; }
        @keyframes slideIn { from { transform: translateX(20px); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        .toast-icon  { font-size: 18px; flex-shrink: 0; }
        .toast-title { font-size: 12px; font-weight: 600; color: #111827; }
        .toast-msg   { font-size: 11px; color: #6c757d; margin-top: 2px; }
        .toast-close { background: none; border: none; cursor: pointer; color: #9ca3af; font-size: 16px; padding: 0; margin-left: auto; }
    </style>
</head>
<body>

{{-- ── Navbar ── --}}
<nav class="navbar navbar-dark px-4 py-2">
    <span class="navbar-brand fw-bold">🌿 Greenhouse Monitor</span>
    <div class="d-flex gap-2">
        <a href="/sensors" class="btn btn-outline-light btn-sm">Kelola Sensor</a>
        <a href="/scanning" class="btn btn-outline-light btn-sm">🔍 Scanning</a>
        <form method="POST" action="/logout" class="mb-0">
            @csrf
            <button type="submit" class="btn btn-outline-danger btn-sm">Logout</button>
        </form>
    </div>
</nav>

{{-- Toast --}}
<div id="toast-container"></div>

<div class="container-fluid py-3 px-4" style="max-width:1200px">

    {{-- ── Alert sensor kosong ── --}}
    @if($sensors->isEmpty())
    <div class="alert alert-info d-flex align-items-center gap-2 mb-3">
        <span>📡</span>
        Belum ada sensor terdaftar.
        <a href="/sensors" class="fw-semibold ms-1">Tambah sensor</a>
    </div>
    @endif

    {{-- ── Sensor Cards ── --}}
    @if($sensors->isNotEmpty())
    <div class="row g-3 mb-4">
        @foreach($sensors as $sensor)
        @php
            $reading = $latestReadings[$sensor->sensor_type] ?? null;
            $status  = $reading ? $reading->status : 'normal';
            $value   = $reading ? $reading->value  : '—';
        @endphp
        <div class="col-6 col-md-4 col-lg-3">
            <div class="sensor-card s-{{ $status }}">
                <div class="sc-label">{{ $sensor->label }}</div>
                <span class="sc-badge badge-{{ $status }}">{{ strtoupper($status) }}</span>
                <div class="sc-value">{{ $value }}<span class="sc-unit">{{ $sensor->unit }}</span></div>
                <div class="sc-device">{{ $sensor->device_id }}</div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- ── Kontrol Aktuator ── --}}
    <div class="actuator-section mb-4">
        <div class="card-header-custom">🎛️ Kendali Hardware · ESP32 Master</div>
        <div class="row g-0">

            {{-- Fan --}}
            <div class="col-md-4 actuator-item">
                <div class="actuator-name">🌀 Fan / Kipas</div>
                <div class="d-flex gap-2 justify-content-center">
                    <button class="btn btn-success btn-sm fw-semibold px-3"
                        onclick="sendCommand('esp32_master','fan','on')">Hidup</button>
                    <button class="btn btn-danger btn-sm fw-semibold px-3"
                        onclick="sendCommand('esp32_master','fan','off')">Mati</button>
                </div>
                <div class="speed-row">
                    <input type="number" id="fan-speed-input" class="form-control form-control-sm"
                           placeholder="Kecepatan 0-100" min="0" max="100">
                    <button class="btn btn-outline-success btn-sm"
                        onclick="sendCommandWithValue('esp32_master','fan','speed',document.getElementById('fan-speed-input').value)">
                        Set %
                    </button>
                </div>
                <div class="actuator-status" id="fan-status">-</div>
            </div>

            {{-- Solenoid --}}
            <div class="col-md-4 actuator-item">
                <div class="actuator-name">💧 Solenoid Valve / Irigasi</div>
                <div class="d-flex gap-2 justify-content-center">
                    <button class="btn btn-success btn-sm fw-semibold px-3"
                        onclick="sendCommand('esp32_master','solenoid','open')">Buka</button>
                    <button class="btn btn-danger btn-sm fw-semibold px-3"
                        onclick="sendCommand('esp32_master','solenoid','close')">Tutup</button>
                </div>
                <div class="actuator-status" id="solenoid-status">-</div>
            </div>

            {{-- Lampu --}}
            <div class="col-md-4 actuator-item">
                <div class="actuator-name">💡 Lampu Greenhouse</div>
                <div class="d-flex gap-2 justify-content-center">
                    <button class="btn btn-warning btn-sm fw-semibold px-3"
                        onclick="sendCommand('esp32_master','lamp','on')">Hidup</button>
                    <button class="btn btn-secondary btn-sm fw-semibold px-3"
                        onclick="sendCommand('esp32_master','lamp','off')">Mati</button>
                </div>
                <div class="actuator-status" id="lamp-status">-</div>
            </div>

        </div>
    </div>

    {{-- ── Live Scanning Cabai ── --}}
    <div class="card mb-4" id="panel-live-scanning">
        <div class="card-header-custom d-flex justify-content-between align-items-center">
            <span>📷 Live Scanning Kematangan Cabai</span>
            <span class="live-status-badge live-on" id="live-badge">● LIVE</span>
        </div>
        <div class="card-body p-0">
            <div class="live-frame-wrap">
                <img id="live-frame"
                     src="/images/placeholder-camera.jpg"
                     alt="Live Capture"
                     class="w-100"
                     style="min-height:200px; object-fit:cover;">
                <div class="live-overlay" id="live-tanaman">Menunggu capture...</div>
            </div>
            <div class="live-progress">
                <div class="live-progress-bar" id="live-progress"></div>
            </div>
        </div>
        <div class="card-footer text-muted" style="font-size:12px;">
            <span id="live-skor">Skor kematangan: —</span> &nbsp;|&nbsp;
            <span id="live-kategori">Kategori: —</span> &nbsp;|&nbsp;
            <span id="live-time">—</span>
        </div>
    </div>

    {{-- ── Log Data Terbaru ── --}}
    <div class="card">
        <div class="card-header-custom">📋 Log Data Terbaru</div>
        <div class="table-responsive">
            <table class="table table-hover table-sm log-table mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Waktu</th>
                        <th>Device</th>
                        <th>Sensor</th>
                        <th>Nilai</th>
                        <th>Satuan</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td class="ps-3" style="font-family:monospace;font-size:12px;color:#6c757d">
                            {{ $log->created_at->format('d/m H:i:s') }}
                        </td>
                        <td>
                            <span style="background:#f3f4f6;border:1px solid #e5e7eb;border-radius:4px;padding:2px 7px;font-family:monospace;font-size:11px;color:#6c757d">
                                {{ $log->device_id }}
                            </span>
                        </td>
                        <td>{{ $log->sensor_type }}</td>
                        <td style="font-family:monospace;font-weight:600;color:#111827">{{ $log->value }}</td>
                        <td class="text-muted">{{ $log->unit }}</td>
                        <td>
                            @if($log->status === 'danger')
                                <span class="st-badge st-danger">DANGER</span>
                            @elseif($log->status === 'warning')
                                <span class="st-badge st-warning">WARNING</span>
                            @else
                                <span class="st-badge st-normal">NORMAL</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            <div style="font-size:28px;margin-bottom:8px">📭</div>
                            Belum ada data masuk dari sensor
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
// Pusher
const pusher  = new Pusher('{{ env("PUSHER_APP_KEY") }}', { cluster: '{{ env("PUSHER_APP_CLUSTER") }}' });
const channel = pusher.subscribe('greenhouse-alerts');
channel.bind('sensor.alert', function(data) {
    showToast(data.sensorType, data.value, data.unit, data.status);
    updateSensorCard(data.sensorType, data.value, data.status);
});

// ── Live Scanning via SSE ──────────────────────────────────────────────
const liveStream = new EventSource('/scanning/stream');

liveStream.addEventListener('capture', function (e) {
    const d = JSON.parse(e.data);

    document.getElementById('live-frame').src = d.image_url + '?t=' + Date.now();
    document.getElementById('live-tanaman').innerText  = 'Tanaman ' + d.tanaman_id;
    document.getElementById('live-skor').innerText     = 'Skor kematangan: ' + d.ripeness_score + '%';
    document.getElementById('live-kategori').innerText = 'Kategori: ' + d.kategori;
    document.getElementById('live-time').innerText     = d.timestamp;
    document.getElementById('live-progress').style.width = d.progress + '%';

    const badge = document.getElementById('live-badge');
    badge.textContent = '● LIVE';
    badge.className = 'live-status-badge live-on';
});

liveStream.onerror = function () {
    const badge = document.getElementById('live-badge');
    badge.textContent = '● OFFLINE';
    badge.className = 'live-status-badge live-off';
    document.getElementById('live-tanaman').innerText = 'Koneksi terputus, mencoba ulang...';
};
// ── END Live Scanning ─────────────────────────────────────────────────

function showToast(sensor, value, unit, status) {
    const icons  = { danger:'🔴', warning:'⚠️', normal:'✅' };
    const labels = { danger:'BAHAYA', warning:'PERINGATAN', normal:'NORMAL' };
    const c = document.getElementById('toast-container');
    const t = document.createElement('div');
    t.className = `gh-toast t-${status}`;
    t.innerHTML = `
        <span class="toast-icon">${icons[status]||'📡'}</span>
        <div>
            <div class="toast-title">${labels[status]||status} — ${sensor}</div>
            <div class="toast-msg">${value} ${unit} terdeteksi</div>
        </div>
        <button class="toast-close" onclick="this.closest('.gh-toast').remove()">×</button>`;
    c.appendChild(t);
    setTimeout(() => t.remove(), 6000);
}

function updateSensorCard(sensorType, value, status) {
    document.querySelectorAll('.sensor-card').forEach(card => {
        const dev = card.querySelector('.sc-device');
        if (dev && dev.textContent.includes(sensorType)) {
            const val   = card.querySelector('.sc-value');
            const badge = card.querySelector('.sc-badge');
            if (val)   val.childNodes[0].textContent = value;
            if (badge) { badge.textContent = status.toUpperCase(); badge.className = `sc-badge badge-${status}`; }
            card.className = `sensor-card s-${status}`;
        }
    });
}

function setStatus(actuator, msg, type) {
    const el = document.getElementById(actuator + '-status');
    if (el) { el.textContent = msg; el.className = `actuator-status ${type}`; }
}

function sendCommand(deviceId, actuator, command) {
    fetch('/actuator/command', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: JSON.stringify({ device_id: deviceId, actuator: actuator, command: command })
    })
    .then(r => r.json())
    .then(() => setStatus(actuator, `✓ ${command.toUpperCase()} terkirim`, 'ok'))
    .catch(() => setStatus(actuator, '✗ Gagal terhubung', 'err'));
}

function sendCommandWithValue(deviceId, actuator, command, value) {
    if (!value) { alert('Masukkan nilai kecepatan terlebih dahulu'); return; }
    fetch('/actuator/command', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: JSON.stringify({ device_id: deviceId, actuator: actuator, command: command, value: value })
    })
    .then(r => r.json())
    .then(() => setStatus(actuator, `✓ Speed diset ${value}%`, 'ok'))
    .catch(() => setStatus(actuator, '✗ Gagal terhubung', 'err'));
}
</script>

</body>
</html>