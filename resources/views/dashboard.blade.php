<!DOCTYPE html>
<html lang="id"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Dashboard — Greenhouse Monitor</title>


<style>
  @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
  :root{
    --bg:#0a0f1a; --panel:#111a2b; --panel2:#16223a; --line:rgba(148,163,184,.13);
    --text:#e7edf8; --muted:#8ba0c2; --accent:#4f8cff; --accent2:#22d3ee;
    --ok:#34d399; --warn:#fbbf24; --dng:#fb7185; --sb:248px;
  }
  *{box-sizing:border-box}
  body{margin:0;background:radial-gradient(1200px 600px at 80% -10%,rgba(79,140,255,.10),transparent),var(--bg);
       color:var(--text);font-family:'Inter',system-ui,Arial,sans-serif;font-size:14px;-webkit-font-smoothing:antialiased}
  a{color:inherit}
  ::-webkit-scrollbar{width:9px;height:9px} ::-webkit-scrollbar-thumb{background:#22314c;border-radius:6px}

  /* ---- Sidebar ---- */
  .sidebar{position:fixed;top:0;left:0;width:var(--sb);height:100vh;
    background:linear-gradient(180deg,#0e1626,#0a0f1a);border-right:1px solid var(--line);
    display:flex;flex-direction:column;z-index:50;padding:0}
  .sb-brand{display:flex;align-items:center;gap:11px;padding:20px 20px 18px;font-weight:800;font-size:15.5px;letter-spacing:-.2px}
  .sb-logo{width:34px;height:34px;border-radius:9px;display:grid;place-items:center;color:#fff;
    background:linear-gradient(135deg,var(--accent),var(--accent2));box-shadow:0 6px 16px -4px rgba(79,140,255,.6)}
  .sb-sec{font-size:10px;font-weight:700;letter-spacing:.12em;color:var(--muted);text-transform:uppercase;padding:14px 22px 8px}
  .sb-nav{display:flex;flex-direction:column;gap:2px;padding:4px 12px}
  .sb-nav a,.sb-nav button{display:flex;align-items:center;gap:12px;width:100%;text-align:left;background:transparent;border:0;
    color:var(--muted);padding:10px 13px;border-radius:10px;font-size:13.5px;font-weight:600;text-decoration:none;cursor:pointer;
    transition:.15s;position:relative}
  .sb-nav a:hover,.sb-nav button:hover{background:rgba(148,163,184,.08);color:var(--text)}
  .sb-nav a.active{background:linear-gradient(90deg,rgba(79,140,255,.16),rgba(79,140,255,.04));color:#fff}
  .sb-nav a.active::before{content:"";position:absolute;left:0;top:8px;bottom:8px;width:3px;border-radius:3px;background:linear-gradient(var(--accent),var(--accent2))}
  .sb-nav svg{width:18px;height:18px;flex:none}
  .sb-foot{margin-top:auto;padding:12px}
  .sb-foot .logout{color:var(--dng)}

  /* ---- Layout ---- */
  .content{margin-left:var(--sb);padding:26px 32px 60px;max-width:1280px;animation:fade .4s ease}
  @keyframes fade{from{opacity:0;transform:translateY(6px)}to{opacity:1;transform:none}}
  @media(max-width:860px){.sidebar{position:static;width:100%;height:auto;border-right:0;border-bottom:1px solid var(--line)}.sb-brand{padding:14px 16px}.sb-sec{display:none}.sb-nav{flex-direction:row;overflow-x:auto;gap:4px;padding:0 10px 10px}.sb-nav a,.sb-nav button{white-space:nowrap;padding:9px 13px}.sb-nav a.active::before{display:none}.sb-foot{margin-top:0;padding:0 10px 10px}.content{margin-left:0;padding:18px 14px 50px;max-width:100%}body{overflow-x:hidden}.grid{grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:10px}.panel,.scard,.stat{max-width:100%}table{display:block;overflow-x:auto;white-space:nowrap}.summary,.chips{overflow-x:auto}.pagehead h1{font-size:19px}#feat{grid-template-columns:1fr!important}.feat,.feat-wrap{grid-template-columns:1fr!important}img{max-width:100%;height:auto}*{max-width:100%}}

  .pagehead{display:flex;align-items:flex-end;justify-content:space-between;flex-wrap:wrap;gap:14px;margin-bottom:22px}
  .pagehead h1{font-size:23px;font-weight:800;margin:0;letter-spacing:-.4px;
    background:linear-gradient(90deg,#fff,#b9c9e8);-webkit-background-clip:text;background-clip:text;color:transparent}
  .pagehead .sub{color:var(--muted);font-size:12.5px;margin-top:5px}

  /* ---- Chips / status ---- */
  .chips{display:flex;gap:9px;flex-wrap:wrap;align-items:center}
  .chip{background:var(--panel);border:1px solid var(--line);border-radius:20px;padding:6px 13px;font-size:12px;font-weight:600;display:inline-flex;align-items:center;gap:7px}
  .chip .d{width:8px;height:8px;border-radius:50%} .chip.ok .d{background:var(--ok)} .chip.warn .d{background:var(--warn)} .chip.dng .d{background:var(--dng)}
  .btn{border:0;border-radius:10px;padding:9px 16px;font-weight:700;font-size:13px;cursor:pointer;color:#fff;transition:.15s;
    background:linear-gradient(135deg,var(--accent),#3b6fd4);box-shadow:0 8px 20px -8px rgba(79,140,255,.7)}
  .btn:hover{filter:brightness(1.08);transform:translateY(-1px)}
  .btn.ghost{background:var(--panel);border:1px solid var(--line);box-shadow:none;color:var(--text)}
  .btn.cyan{background:linear-gradient(135deg,#0891b2,#06b6d4)} .btn.danger{background:linear-gradient(135deg,#e11d48,#f43f5e)}

  /* ---- Section ---- */
  .section-title{font-size:11px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--muted);margin:26px 0 12px;display:flex;align-items:center;gap:10px}
  .section-title::after{content:"";flex:1;height:1px;background:var(--line)}

  /* ---- Cards ---- */
  .grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(210px,1fr));gap:15px}
  .scard{background:linear-gradient(180deg,var(--panel),#0e1727);border:1px solid var(--line);border-left:3px solid var(--ok);
    border-radius:14px;padding:16px 17px;position:relative;transition:.18s;box-shadow:0 10px 30px -18px rgba(0,0,0,.8)}
  .scard:hover{transform:translateY(-3px);border-color:rgba(79,140,255,.4)}
  .scard.s-warning{border-left-color:var(--warn)} .scard.s-danger{border-left-color:var(--dng)} .scard.s-none{border-left-color:#3a4762;opacity:.7}
  .scard .lbl{font-size:11px;color:var(--muted);font-weight:700;text-transform:uppercase;letter-spacing:.03em;padding-right:64px}
  .scard .val{font-size:28px;font-weight:800;margin-top:8px;letter-spacing:-.5px;font-variant-numeric:tabular-nums}
  .scard .val .u{font-size:13px;color:var(--muted);font-weight:600;margin-left:4px}
  .scard .dev{font-size:10.5px;color:#5f7196;margin-top:7px;font-family:'Inter';letter-spacing:.02em}
  .sbadge{position:absolute;top:14px;right:14px;font-size:9px;font-weight:800;padding:4px 9px;border-radius:6px;letter-spacing:.04em}
  .b-normal{background:rgba(52,211,153,.16);color:#6ee7b7} .b-warning{background:rgba(251,191,36,.16);color:#fcd34d}
  .b-danger{background:rgba(251,113,133,.16);color:#fda4af} .b-none{background:rgba(148,163,184,.12);color:#94a3b8}

  /* ---- Panel / table ---- */
  .panel{background:linear-gradient(180deg,var(--panel),#0e1727);border:1px solid var(--line);border-radius:14px;overflow:hidden;box-shadow:0 10px 30px -18px rgba(0,0,0,.8)}
  .panel.pad{padding:18px}
  .panel-h{padding:14px 18px;border-bottom:1px solid var(--line);font-weight:700;font-size:13.5px}
  table{width:100%;border-collapse:collapse;font-size:12.5px}
  th,td{padding:11px 14px;text-align:left;border-bottom:1px solid var(--line)}
  th{background:rgba(255,255,255,.02);color:var(--muted);font-weight:700;font-size:10.5px;text-transform:uppercase;letter-spacing:.05em}
  tr:last-child td{border-bottom:0} tbody tr{transition:.12s} tbody tr:hover{background:rgba(148,163,184,.04)}
  .mono{font-variant-numeric:tabular-nums}
  code{font-family:ui-monospace,Consolas,monospace;font-size:12px;color:#93b4ff;background:rgba(79,140,255,.1);padding:2px 7px;border-radius:5px}

  /* status pills */
  .st{font-size:9.5px;font-weight:800;padding:3px 9px;border-radius:6px}
  .st-COMPLETED,.st-normal{background:rgba(52,211,153,.16);color:#6ee7b7}
  .st-RUNNING{background:rgba(79,140,255,.18);color:#93b4ff}
  .st-ERROR,.st-danger{background:rgba(251,113,133,.16);color:#fda4af}
  .st-STOPPED,.st-warning{background:rgba(251,191,36,.16);color:#fcd34d}
  .st-PENDING{background:rgba(148,163,184,.12);color:#94a3b8}

  /* forms */
  label{display:block;font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--muted);margin-bottom:6px}
  input,select{width:100%;padding:10px 12px;border:1px solid var(--line);border-radius:10px;font-size:13.5px;background:#0c1424;color:var(--text);font-family:inherit}
  input:focus,select:focus{outline:0;border-color:var(--accent);box-shadow:0 0 0 3px rgba(79,140,255,.18)}
  .form-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:15px}
  .flash{padding:11px 15px;border-radius:11px;font-size:13px;margin-bottom:16px;background:rgba(52,211,153,.1);color:#a7f3d0;border:1px solid rgba(52,211,153,.3)}
  .flash.err{background:rgba(251,113,133,.1);color:#fecdd3;border-color:rgba(251,113,133,.3)}
  .muted{color:var(--muted)} .cr{color:var(--dng)} .ct{color:var(--warn)} .cu{color:var(--ok)}
  .stat{background:linear-gradient(180deg,var(--panel),#0e1727);border:1px solid var(--line);border-radius:12px;padding:12px 16px;min-width:104px}
  .stat b{display:block;font-size:23px;font-weight:800;letter-spacing:-.5px;font-variant-numeric:tabular-nums} .stat span{color:var(--muted);font-size:11px}
  .summary{display:flex;gap:11px;flex-wrap:wrap;margin-bottom:20px}
  .btn-del{background:rgba(251,113,133,.1);border:1px solid rgba(251,113,133,.3);color:#fda4af;border-radius:8px;padding:6px 14px;font-size:12px;font-weight:600;cursor:pointer}
  .btn-del:hover{background:rgba(251,113,133,.2)}
</style>

</head><body>
<aside class="sidebar">
  <div class="sb-brand"><span class="sb-logo"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z"/><path d="M2 21c0-3 1.85-5.36 5.08-6"/></svg></span> Greenhouse</div>
  <div class="sb-sec">Menu</div>
  <nav class="sb-nav">
    <a class="active" href="{{ url('/') }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/></svg><span>Dashboard</span></a>
    <a class="" href="{{ url('/sensors') }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" y1="21" x2="4" y2="14"/><line x1="4" y1="10" x2="4" y2="3"/><line x1="12" y1="21" x2="12" y2="12"/><line x1="12" y1="8" x2="12" y2="3"/><line x1="20" y1="21" x2="20" y2="16"/><line x1="20" y1="12" x2="20" y2="3"/><line x1="1" y1="14" x2="7" y2="14"/><line x1="9" y1="8" x2="15" y2="8"/><line x1="17" y1="16" x2="23" y2="16"/></svg><span>Kelola Sensor</span></a>
    <a class="" href="{{ route('gantry.live') }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7V5a2 2 0 0 1 2-2h2"/><path d="M17 3h2a2 2 0 0 1 2 2v2"/><path d="M21 17v2a2 2 0 0 1-2 2h-2"/><path d="M7 21H5a2 2 0 0 1-2-2v-2"/><circle cx="12" cy="12" r="3"/></svg><span>Gantry</span></a>
    <a class="" href="{{ route('gantry.recap') }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg><span>Rekap</span></a>
    <a class="" href="{{ route('soil.index') }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg><span>Grafik Sensor</span></a>
  </nav>
  <div class="sb-foot">
    <div class="sb-sec" style="padding-top:0">Akun</div>
    <nav class="sb-nav">
      <form method="POST" action="{{ url('/logout') }}">@csrf<button type="submit" class="logout"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg><span>Logout</span></button></form>
    </nav>
  </div>
</aside>
<main class="content">

@php
$catMap = [
 'Kondisi Lingkungan' => ['temperature','humidity','light_intensity'],
 'Kelembapan Tanah'   => ['soil_1','soil_2','soil_3','soil_avg'],
 'Kematangan & Tanaman' => ['ripeness_score','plant_height'],
 'Aktuator' => ['fan_speed','energy_power','energy_voltage','energy_current','solar_power','solenoid_current'],
];
$cN=0;$cW=0;$cD=0;
foreach($sensors as $s){ $r=$latestReadings[$s->sensor_type]??null; $st=$r?$r->status:'none'; if($st==='normal')$cN++; elseif($st==='warning')$cW++; elseif($st==='danger')$cD++; }
$shownTypes=[];
@endphp
<div class="pagehead">
  <div><h1>Dashboard Monitoring</h1><div class="sub">Akuisisi data real-time dari node sensor &amp; subsistem gantry via Tailscale · {{ now()->timezone('Asia/Jakarta')->format('d/m/Y H:i:s') }}</div></div>
  <div class="chips">
    <span class="chip ok"><span class="d"></span>{{ $cN }} Normal</span>
    <span class="chip warn"><span class="d"></span>{{ $cW }} Warning</span>
    <span class="chip dng"><span class="d"></span>{{ $cD }} Danger</span>
    <button class="btn" onclick="location.reload()">Perbarui</button>
  </div>
</div>
@if($sensors->isEmpty())<div class="panel pad">Belum ada sensor terdaftar. <a href="{{ url('/sensors') }}" style="color:var(--accent);font-weight:600">Tambah sensor →</a></div>@endif
@foreach($catMap as $catName => $types)
  @php $group = $sensors->whereIn('sensor_type',$types); @endphp
  @if($group->isNotEmpty())
    <div class="section-title">{{ $catName }}</div>
    <div class="grid">
      @foreach($group as $sensor)
        @php $shownTypes[]=$sensor->sensor_type; $r=$latestReadings[$sensor->sensor_type]??null; $status=$r?$r->status:'none';
             $value=$r?rtrim(rtrim(number_format($r->value,1,'.',''),'0'),'.'):'—'; @endphp
        <div class="scard s-{{ $status }}">
          <div class="lbl">{{ $sensor->label }}</div>
          <span class="sbadge b-{{ $status }}">{{ $status==='none'?'NO DATA':strtoupper($status) }}</span>
          <div class="val">{{ $value }}<span class="u">{{ $sensor->unit }}</span></div>
          <div class="dev">{{ $sensor->device_id }}</div>
        </div>
      @endforeach
    </div>
  @endif
@endforeach
@php $rest=$sensors->whereNotIn('sensor_type',$shownTypes); @endphp
@if($rest->isNotEmpty())
  <div class="section-title">Lainnya</div>
  <div class="grid">
    @foreach($rest as $sensor)
      @php $r=$latestReadings[$sensor->sensor_type]??null; $status=$r?$r->status:'none'; $value=$r?rtrim(rtrim(number_format($r->value,1,'.',''),'0'),'.'):'—'; @endphp
      <div class="scard s-{{ $status }}"><div class="lbl">{{ $sensor->label }}</div>
        <span class="sbadge b-{{ $status }}">{{ $status==='none'?'NO DATA':strtoupper($status) }}</span>
        <div class="val">{{ $value }}<span class="u">{{ $sensor->unit }}</span></div><div class="dev">{{ $sensor->device_id }}</div></div>
    @endforeach
  </div>
@endif

<div class="section-title">Kendali Aktuator</div>
<div class="panel pad">
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px" class="act-wrap">
    <div style="text-align:center">
      <div style="font-weight:700;margin-bottom:12px">Kipas (Fan)</div>
      <div style="display:flex;gap:8px;justify-content:center">
        <button class="btn" onclick="sendCommand('esp32_master','fan','on')">Hidup</button>
        <button class="btn danger" onclick="sendCommand('esp32_master','fan','off')">Mati</button>
      </div>
      <div style="display:flex;gap:8px;margin-top:12px">
        <input type="number" id="fan-speed-input" placeholder="Kecepatan 0–100" min="0" max="100">
        <button class="btn ghost" onclick="sendCommandWithValue('esp32_master','fan','speed',document.getElementById('fan-speed-input').value)">Set %</button>
      </div>
      <div class="muted" id="fan-status" style="font-size:12px;margin-top:10px">—</div>
    </div>
    <div style="text-align:center">
      <div style="font-weight:700;margin-bottom:12px">Solenoid / Irigasi</div>
      <div style="display:flex;gap:8px;justify-content:center">
        <button class="btn" onclick="sendCommand('esp32_master','solenoid','open')">Buka</button>
        <button class="btn danger" onclick="sendCommand('esp32_master','solenoid','close')">Tutup</button>
      </div>
      <div class="muted" id="solenoid-status" style="font-size:12px;margin-top:10px">—</div>
    </div>
  </div>
</div>

<div class="section-title">Log Akuisisi Data</div>
<div class="panel">
  <table>
    <thead><tr><th>Waktu</th><th>Device</th><th>Sensor</th><th>Nilai</th><th>Satuan</th><th>Status</th></tr></thead>
    <tbody>
      @forelse($logs as $log)
      <tr><td class="mono muted">{{ $log->created_at->timezone('Asia/Jakarta')->format('d/m H:i:s') }}</td>
        <td><code>{{ $log->device_id }}</code></td><td>{{ $log->sensor_type }}</td>
        <td class="mono" style="font-weight:700">{{ $log->value }}</td><td class="muted">{{ $log->unit }}</td>
        <td><span class="st st-{{ $log->status }}">{{ strtoupper($log->status) }}</span></td></tr>
      @empty
      <tr><td colspan="6" style="text-align:center;padding:26px" class="muted">Belum ada data. Pastikan node sensor 1 jaringan / tailnet aktif, lalu Perbarui.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
<div style="text-align:center;margin-top:18px"><label style="display:inline;text-transform:none;letter-spacing:0;font-weight:400;color:var(--muted);cursor:pointer"><input type="checkbox" id="autoref" style="width:auto"> Perbarui otomatis tiap 30 detik</label></div>

</main>
<script>
let autoTimer=null;
document.getElementById('autoref').addEventListener('change',function(){if(this.checked){autoTimer=setInterval(()=>location.reload(),30000);}else{clearInterval(autoTimer);}});
function setStatus(a,msg,c){const el=document.getElementById(a+'-status');if(el){el.textContent=msg;el.style.color=c==='ok'?'var(--ok)':c==='err'?'var(--dng)':'var(--muted)';}}
function sendCommand(dev,act,cmd){fetch('/actuator/command',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},body:JSON.stringify({device_id:dev,actuator:act,command:cmd})}).then(r=>r.json()).then(()=>setStatus(act,'✓ '+cmd.toUpperCase()+' terkirim','ok')).catch(()=>setStatus(act,'✗ Gagal terhubung','err'));}
function sendCommandWithValue(dev,act,cmd,val){if(!val){alert('Masukkan nilai dulu');return;}fetch('/actuator/command',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},body:JSON.stringify({device_id:dev,actuator:act,command:cmd,value:val})}).then(r=>r.json()).then(()=>setStatus(act,'✓ Speed '+val+'%','ok')).catch(()=>setStatus(act,'✗ Gagal','err'));}
</script>
</body></html>