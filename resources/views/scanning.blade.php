<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scanning Tanaman - Greenhouse Monitor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f0f4f8; }
        .navbar { background: #198754 !important; }
        .btn-start { font-size: 1.1rem; padding: 12px 40px; }
        .card-action { border-radius: 16px; border: none; box-shadow: 0 2px 12px rgba(0,0,0,0.08); }
        .status-info { background: #f8f9fa; border-radius: 10px; padding: 12px 18px; font-size: 14px; }
    </style>
</head>
<body>

{{-- ── Navbar ── --}}
<nav class="navbar navbar-dark px-4 py-2">
    <span class="navbar-brand fw-bold">🌿 Greenhouse Monitor</span>
    <div class="d-flex gap-2">
        <a href="/" class="btn btn-outline-light btn-sm">Dashboard</a>
        <a href="/sensors" class="btn btn-outline-light btn-sm">Sensor</a>
        <form method="POST" action="/logout" class="mb-0">
            @csrf
            <button type="submit" class="btn btn-outline-light btn-sm">Logout</button>
        </form>
    </div>
</nav>

<div class="container-fluid py-4 px-4" style="max-width:1200px">

    {{-- ── Tab ── --}}
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <button class="nav-link active" id="tab-scan-btn" onclick="switchTab('scanning')">
                🔍 Sesi Scanning
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" id="tab-water-btn" onclick="switchTab('penyiraman')">
                💧 Sesi Penyiraman
            </button>
        </li>
    </ul>

    {{-- ── SCANNING ── --}}
    <div id="form-scanning">
        <div class="card card-action mb-4">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-1">🔍 Sesi Scanning Kematangan Cabai</h5>
                <p class="text-muted small mb-4">
                    Kamera Raspberry Pi (Dafa) akan otomatis mendeteksi kematangan:
                    <strong>unripe / turning / ripe / broken</strong>
                </p>

                <div class="status-info mb-4">
                    <div class="row g-2 text-center">
                        <div class="col-4">
                            <div class="text-muted small">Jumlah Tanaman</div>
                            <div class="fw-bold">24 tanaman</div>
                        </div>
                        <div class="col-4">
                            <div class="text-muted small">Susunan</div>
                            <div class="fw-bold">3 × 8</div>
                        </div>
                        <div class="col-4">
                            <div class="text-muted small">Endpoint Dafa</div>
                            <div class="fw-bold" style="font-size:12px"><code>/api/scanning/session/active</code></div>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-3 align-items-center">
                    <button class="btn btn-success btn-start fw-bold" onclick="buatSesi('scanning')" id="btn-mulai-scanning">
                        🚀 Mulai Scanning
                    </button>
                    <button class="btn btn-danger btn-start fw-bold d-none" onclick="stopSesi('scanning')" id="btn-stop-scanning">
                        ⛔ Stop Scanning
                    </button>
                </div>
                <div id="alert-scanning" class="mt-3"></div>
            </div>
        </div>
    </div>

    {{-- ── PENYIRAMAN ── --}}
    <div id="form-penyiraman" style="display:none">
        <div class="card card-action mb-4">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-1">💧 Sesi Penyiraman</h5>
                <p class="text-muted small mb-4">
                    Solenoid valve dikontrol langsung via aktuator ESP32 Master (Rio).
                    Tidak menggerakkan servo kamera.
                </p>

                <div class="status-info mb-4">
                    <div class="row g-2 text-center">
                        <div class="col-4">
                            <div class="text-muted small">Jumlah Tanaman</div>
                            <div class="fw-bold">24 tanaman</div>
                        </div>
                        <div class="col-4">
                            <div class="text-muted small">Susunan</div>
                            <div class="fw-bold">3 × 8</div>
                        </div>
                        <div class="col-4">
                            <div class="text-muted small">Kontrol via</div>
                            <div class="fw-bold">ESP32 Master (Rio)</div>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-3 align-items-center">
                    <button class="btn btn-primary btn-start fw-bold" onclick="buatSesi('penyiraman')" id="btn-mulai-penyiraman">
                        💧 Mulai Penyiraman
                    </button>
                    <button class="btn btn-danger btn-start fw-bold d-none" onclick="stopSesi('penyiraman')" id="btn-stop-penyiraman">
                        ⛔ Stop Penyiraman
                    </button>
                </div>
                <div id="alert-penyiraman" class="mt-3"></div>
            </div>
        </div>
    </div>

    {{-- ── Riwayat Sesi ── --}}
    <div class="card card-action">
        <div class="card-body p-4">
            <h6 class="fw-bold mb-3">📋 Riwayat Sesi</h6>
            <div class="table-responsive">
                <table class="table table-hover table-sm align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Tipe</th>
                            <th>Susunan</th>
                            <th>Jumlah</th>
                            <th>Status</th>
                            <th>Progress</th>
                            <th>Waktu</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sessions as $s)
                        <tr>
                            <td class="text-muted" style="font-family:monospace">#{{ $s->id }}</td>
                            <td>
                                @if(isset($s->penyiraman) && $s->penyiraman && !$s->servo_pan)
                                    <span class="badge bg-primary">💧 Penyiraman</span>
                                @else
                                    <span class="badge bg-success">🔍 Scanning</span>
                                @endif
                            </td>
                            <td style="font-family:monospace;font-size:13px">{{ $s->susunan_tanaman ?? '3x8' }}</td>
                            <td>{{ $s->jumlah_tanaman }} tanaman</td>
                            <td>
                                @if($s->status === 'done')
                                    <span class="badge bg-success">SELESAI</span>
                                @elseif($s->status === 'scanning')
                                    <span class="badge bg-warning text-dark">SCANNING</span>
                                @elseif($s->status === 'error')
                                    <span class="badge bg-danger">ERROR</span>
                                @else
                                    <span class="badge bg-secondary">PENDING</span>
                                @endif
                            </td>
                            <td style="min-width:120px">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height:6px">
                                        <div class="progress-bar bg-success" style="width:{{ $s->progress ?? 0 }}%"></div>
                                    </div>
                                    <small class="text-muted" style="font-family:monospace">{{ $s->progress ?? 0 }}%</small>
                                </div>
                            </td>
                            <td class="text-muted" style="font-family:monospace;font-size:12px">
                                {{ $s->created_at->format('d/m H:i') }}
                            </td>
                            <td>
                                <a href="/scanning/{{ $s->id }}/live" class="btn btn-outline-success btn-sm">
                                    👁️ Live
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <div style="font-size:28px">📭</div>
                                Belum ada sesi yang dibuat
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
// Konfigurasi default — tidak perlu diubah dari dashboard
const DEFAULT_CONFIG = {
    jumlah_tanaman:         24,
    jarak_antar_tanaman:    30,
    jarak_frame_ke_tanaman: 20,
    susunan_tanaman:        '3x8',
    baris:                  8,
    kolom:                  3,
    servo_pan:              90,
    servo_tilt:             90,
};

let aktiveSesiId = { scanning: null, penyiraman: null };

function switchTab(tab) {
    const isScan = tab === 'scanning';
    document.getElementById('form-scanning').style.display   = isScan ? 'block' : 'none';
    document.getElementById('form-penyiraman').style.display = isScan ? 'none'  : 'block';
    document.getElementById('tab-scan-btn').className  = 'nav-link' + (isScan ? ' active' : '');
    document.getElementById('tab-water-btn').className = 'nav-link' + (!isScan ? ' active' : '');
}

function buatSesi(tipe) {
    const url     = tipe === 'scanning' ? '/api/scanning/session' : '/api/scanning/penyiraman';
    const alertEl = document.getElementById('alert-' + tipe);
    const btnMulai = document.getElementById('btn-mulai-' + tipe);
    const btnStop  = document.getElementById('btn-stop-' + tipe);

    alertEl.innerHTML = `<div class="alert alert-info py-2 small">⏳ Membuat sesi...</div>`;
    btnMulai.disabled = true;

    const data = { ...DEFAULT_CONFIG };
    if (tipe === 'penyiraman') {
        delete data.servo_pan;
        delete data.servo_tilt;
    }

    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(data)
    })
    .then(r => r.json())
    .then(res => {
        aktiveSesiId[tipe] = res.session_id;
        alertEl.innerHTML = `<div class="alert alert-success py-2 small">
            ✅ Sesi #${res.session_id} berhasil dibuat! Menunggu ${tipe === 'scanning' ? 'Dafa (Raspberry Pi)' : 'Rio (ESP32)'} memulai...
            <a href="/scanning/${res.session_id}/live" class="fw-semibold ms-2">👁️ Buka Live View →</a>
        </div>`;
        btnMulai.classList.add('d-none');
        btnStop.classList.remove('d-none');
        setTimeout(() => location.reload(), 3000);
    })
    .catch(() => {
        alertEl.innerHTML = `<div class="alert alert-danger py-2 small">❌ Gagal membuat sesi. Periksa koneksi server.</div>`;
        btnMulai.disabled = false;
    });
}

function stopSesi(tipe) {
    const sesiId  = aktiveSesiId[tipe];
    const alertEl = document.getElementById('alert-' + tipe);
    const btnMulai = document.getElementById('btn-mulai-' + tipe);
    const btnStop  = document.getElementById('btn-stop-' + tipe);

    if (!sesiId) {
        alertEl.innerHTML = `<div class="alert alert-warning py-2 small">⚠️ Tidak ada sesi aktif yang bisa dihentikan.</div>`;
        return;
    }

    alertEl.innerHTML = `<div class="alert alert-warning py-2 small">⏳ Menghentikan sesi...</div>`;

    fetch(`/api/scanning/session/${sesiId}/status`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ status: 'error' })
    })
    .then(r => r.json())
    .then(() => {
        alertEl.innerHTML = `<div class="alert alert-danger py-2 small">⛔ Sesi #${sesiId} dihentikan.</div>`;
        aktiveSesiId[tipe] = null;
        btnStop.classList.add('d-none');
        btnMulai.classList.remove('d-none');
        btnMulai.disabled = false;
        setTimeout(() => location.reload(), 2000);
    })
    .catch(() => {
        alertEl.innerHTML = `<div class="alert alert-danger py-2 small">❌ Gagal menghentikan sesi.</div>`;
    });
}
</script>

</body>
</html>