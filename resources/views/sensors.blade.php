<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Sensor — Greenhouse Monitor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f0f4f8; font-size: 14px; }
        .navbar { background: #198754 !important; }
        .card { border-radius: 10px; border: none; box-shadow: 0 1px 4px rgba(0,0,0,.08); }
        .table th { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: #6c757d; background: #f8f9fa; }
        .table td { font-size: 13px; vertical-align: middle; }
    </style>
</head>
<body>

{{-- ── Navbar ── --}}
<nav class="navbar navbar-dark px-4 py-2">
    <span class="navbar-brand fw-bold">🌿 Greenhouse Monitor</span>
    <div class="d-flex gap-2">
        <a href="/" class="btn btn-outline-light btn-sm">Dashboard</a>
        <form method="POST" action="/logout" class="mb-0">
            @csrf
            <button type="submit" class="btn btn-outline-light btn-sm">Logout</button>
        </form>
    </div>
</nav>

<div class="container-fluid py-3 px-4" style="max-width:1100px">

    {{-- Alert --}}
    @if(session('success'))
    <div class="alert alert-success py-2 small">✅ {{ session('success') }}</div>
    @endif

    {{-- ── Form Tambah Sensor ── --}}
    <div class="card mb-4">
        <div class="card-body">
            <h6 class="fw-bold mb-3">➕ Tambah Sensor</h6>
            <form method="POST" action="/sensors">
                @csrf
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold text-muted text-uppercase">Device ID</label>
                        <select name="device_id" class="form-select form-select-sm" required>
                            <option value="esp32_master">esp32_master</option>
                            <option value="rpi_vision">rpi_vision</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold text-muted text-uppercase">Sensor Type</label>
                        <input type="text" name="sensor_type" class="form-control form-control-sm"
                               placeholder="cth: temperature" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold text-muted text-uppercase">Label</label>
                        <input type="text" name="label" class="form-control form-control-sm"
                               placeholder="cth: Suhu Zona A" required>
                    </div>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold text-muted text-uppercase">Satuan</label>
                        <input type="text" name="unit" class="form-control form-control-sm"
                               placeholder="cth: °C" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold text-muted text-uppercase">Owner / PIC</label>
                        <input type="text" name="owner" class="form-control form-control-sm"
                               placeholder="cth: Admin" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold text-muted text-uppercase">
                            <span class="text-success">●</span> Threshold Min (SPmin)
                        </label>
                        <input type="number" step="0.01" name="threshold_min"
                               class="form-control form-control-sm" placeholder="cth: 25">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold text-muted text-uppercase">
                            <span class="text-danger">●</span> Threshold Max (SPmax)
                        </label>
                        <input type="number" step="0.01" name="threshold_max"
                               class="form-control form-control-sm" placeholder="cth: 30">
                    </div>
                </div>
                <div class="small text-muted mb-3">
                    Setpoint Threshold — Normal: SPmin ≤ PV ≤ SPmax · Warning: deviasi 0–20% · Danger: deviasi &gt;20%
                </div>
                <button type="submit" class="btn btn-success btn-sm fw-semibold px-4">
                    ✅ Simpan Sensor
                </button>
            </form>
        </div>
    </div>

    {{-- ── Daftar Sensor ── --}}
    <div class="card">
        <div class="card-body">
            <h6 class="fw-bold mb-3">📋 Daftar Sensor Terdaftar</h6>
            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead>
                        <tr>
                            <th>Device</th>
                            <th>Type</th>
                            <th>Label</th>
                            <th>Satuan</th>
                            <th>Owner</th>
                            <th>Min</th>
                            <th>Max</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sensors as $sensor)
                        <tr>
                            <td>
                                <span class="badge {{ $sensor->device_id === 'esp32_master' ? 'bg-success' : 'bg-purple' }} text-white"
                                      style="{{ $sensor->device_id === 'rpi_vision' ? 'background:#7c3aed!important' : '' }}">
                                    {{ $sensor->device_id }}
                                </span>
                            </td>
                            <td><code style="font-size:12px">{{ $sensor->sensor_type }}</code></td>
                            <td class="fw-semibold">{{ $sensor->label }}</td>
                            <td><span class="text-success fw-semibold">{{ $sensor->unit }}</span></td>
                            <td>{{ $sensor->owner }}</td>
                            <td><span class="text-success" style="font-family:monospace">{{ $sensor->threshold_min ?? '—' }}</span></td>
                            <td><span class="text-danger" style="font-family:monospace">{{ $sensor->threshold_max ?? '—' }}</span></td>
                            <td>
                                @if($sensor->is_active)
                                    <span class="badge bg-success">AKTIF</span>
                                @else
                                    <span class="badge bg-secondary">NONAKTIF</span>
                                @endif
                            </td>
                            <td>
                                <form method="POST" action="/sensors/{{ $sensor->id }}"
                                      onsubmit="return confirm('Hapus sensor {{ $sensor->label }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <div style="font-size:24px;margin-bottom:8px">📡</div>
                                Belum ada sensor terdaftar
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
</body>
</html>