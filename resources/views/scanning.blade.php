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

<div class="container-fluid py-3 px-4" style="max-width:1200px">

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

    {{-- ── Form Scanning ── --}}
    <div id="form-scanning">
        <div class="card mb-4">
            <div class="card-body">
                <h6 class="fw-bold mb-3">🔍 Buat Sesi Scanning Baru</h6>
                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold text-muted text-uppercase">Jumlah Tanaman</label>
                        <input type="number" id="s_jumlah_tanaman" class="form-control" value="24" min="1">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold text-muted text-uppercase">Jarak Antar Tanaman (cm)</label>
                        <input type="number" id="s_jarak_antar" class="form-control" value="30" step="0.1">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold text-muted text-uppercase">Jarak Frame ke Tanaman (cm)</label>
                        <input type="number" id="s_jarak_frame" class="form-control" value="20" step="0.1">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold text-muted text-uppercase">Susunan Tanaman</label>
                        <select id="s_susunan" class="form-select" onchange="updateBarisKolom('s')">
                            <option value="3x8" data-baris="8" data-kolom="3">3 × 8 (24 tanaman)</option>
                            <option value="custom">Custom</option>
                        </select>
                    </div>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold text-muted text-uppercase">Baris</label>
                        <input type="number" id="s_baris" class="form-control" value="8" min="1">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold text-muted text-uppercase">Kolom</label>
                        <input type="number" id="s_kolom" class="form-control" value="3" min="1">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold text-muted text-uppercase">Koordinat Servo Pan °(0-180)</label>
                        <input type="number" id="s_servo_pan" class="form-control" value="90" step="0.1">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold text-muted text-uppercase">Koordinat Servo Tilt °(0-180)</label>
                        <input type="number" id="s_servo_tilt" class="form-control" value="90" step="0.1">
                    </div>
                </div>
                <div class="alert alert-success py-2 small mb-3">
                    📡 Perintah servo dikirim ke Raspberry Pi (Dafa) via endpoint
                    <code>/api/scanning/session/active</code>.
                    Kamera YOLO mendeteksi kematangan:
                    <strong>unripe / turning / ripe / broken</strong>.
                </div>
                <button class="btn btn-success fw-semibold px-4" onclick="buatSesi('scanning')">
                    🚀 Mulai Sesi Scanning
                </button>
                <div id="alert-scanning" class="mt-2"></div>
            </div>
        </div>
    </div>

    {{-- ── Form Penyiraman ── --}}
    <div id="form-penyiraman" style="display:none">
        <div class="card mb-4">
            <div class="card-body">
                <h6 class="fw-bold mb-3">💧 Buat Sesi Penyiraman Baru</h6>
                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold text-muted text-uppercase">Jumlah Tanaman</label>
                        <input type="number" id="p_jumlah_tanaman" class="form-control" value="24">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold text-muted text-uppercase">Jarak Antar Tanaman (cm)</label>
                        <input type="number" id="p_jarak_antar" class="form-control" value="30" step="0.1">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold text-muted text-uppercase">Jarak Frame ke Tanaman (cm)</label>
                        <input type="number" id="p_jarak_frame" class="form-control" value="20" step="0.1">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold text-muted text-uppercase">Susunan Tanaman</label>
                        <select id="p_susunan" class="form-select" onchange="updateBarisKolom('p')">
                            <option value="3x8" data-baris="8" data-kolom="3">3 × 8 (24 tanaman)</option>
                            <option value="custom">Custom</option>
                        </select>
                    </div>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold text-muted text-uppercase">Baris</label>
                        <input type="number" id="p_baris" class="form-control" value="8">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold text-muted text-uppercase">Kolom</label>
                        <input type="number" id="p_kolom" class="form-control" value="3">
                    </div>
                </div>
                <div class="alert alert-info py-2 small mb-3">
                    💧 Sesi penyiraman tidak menggerakkan servo. Solenoid valve dikontrol langsung via aktuator ESP32 Master (Rio) · Channel Pusher: <code>scanning-{id}</code>
                </div>
                <button class="btn btn-primary fw-semibold px-4" onclick="buatSesi('penyiraman')">
                    💧 Mulai Sesi Penyiraman
                </button>
                <div id="alert-penyiraman" class="mt-2"></div>
            </div>
        </div>
    </div>

    {{-- ── Riwayat Sesi ── --}}
    <div class="card">
        <div class="card-body">
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
function switchTab(tab) {
    const isScan = tab === 'scanning';
    document.getElementById('form-scanning').style.display   = isScan ? 'block' : 'none';
    document.getElementById('form-penyiraman').style.display = isScan ? 'none'  : 'block';
    document.getElementById('tab-scan-btn').className  = 'nav-link' + (isScan ? ' active' : '');
    document.getElementById('tab-water-btn').className = 'nav-link' + (!isScan ? ' active' : '');
}

function updateBarisKolom(prefix) {
    const sel = document.getElementById(prefix + '_susunan');
    const opt = sel.options[sel.selectedIndex];
    if (opt.value !== 'custom') {
        document.getElementById(prefix + '_baris').value = opt.dataset.baris;
        document.getElementById(prefix + '_kolom').value = opt.dataset.kolom;
        document.getElementById(prefix + '_jumlah_tanaman').value =
            parseInt(opt.dataset.baris) * parseInt(opt.dataset.kolom);
    }
}

function buatSesi(tipe) {
    const prefix  = tipe === 'scanning' ? 's' : 'p';
    const url     = tipe === 'scanning' ? '/api/scanning/session' : '/api/scanning/penyiraman';
    const alertEl = document.getElementById('alert-' + tipe);
    alertEl.innerHTML = `<div class="alert alert-info py-2 small">⏳ Membuat sesi...</div>`;

    const data = {
        jumlah_tanaman:         parseInt(document.getElementById(prefix + '_jumlah_tanaman').value),
        jarak_antar_tanaman:    parseFloat(document.getElementById(prefix + '_jarak_antar').value),
        jarak_frame_ke_tanaman: parseFloat(document.getElementById(prefix + '_jarak_frame').value),
        susunan_tanaman:        document.getElementById(prefix + '_susunan').value,
        baris:                  parseInt(document.getElementById(prefix + '_baris').value),
        kolom:                  parseInt(document.getElementById(prefix + '_kolom').value),
    };
    if (tipe === 'scanning') {
        data.servo_pan  = parseFloat(document.getElementById('s_servo_pan').value);
        data.servo_tilt = parseFloat(document.getElementById('s_servo_tilt').value);
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
        alertEl.innerHTML = `<div class="alert alert-success py-2 small">
            ✅ Sesi #${res.session_id} berhasil dibuat!
            <a href="/scanning/${res.session_id}/live" class="fw-semibold ms-2">👁️ Buka Live View →</a>
        </div>`;
        setTimeout(() => location.reload(), 2500);
    })
    .catch(() => {
        alertEl.innerHTML = `<div class="alert alert-danger py-2 small">❌ Gagal membuat sesi. Periksa koneksi server.</div>`;
    });
}
</script>

</body>
</html>