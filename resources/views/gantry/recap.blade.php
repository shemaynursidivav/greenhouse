<!DOCTYPE html>
<html lang="id"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Rekap — Greenhouse Monitor</title>


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
    <a class="" href="{{ url('/') }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/></svg><span>Dashboard</span></a>
    <a class="" href="{{ url('/sensors') }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" y1="21" x2="4" y2="14"/><line x1="4" y1="10" x2="4" y2="3"/><line x1="12" y1="21" x2="12" y2="12"/><line x1="12" y1="8" x2="12" y2="3"/><line x1="20" y1="21" x2="20" y2="16"/><line x1="20" y1="12" x2="20" y2="3"/><line x1="1" y1="14" x2="7" y2="14"/><line x1="9" y1="8" x2="15" y2="8"/><line x1="17" y1="16" x2="23" y2="16"/></svg><span>Kelola Sensor</span></a>
    <a class="" href="{{ route('gantry.live') }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7V5a2 2 0 0 1 2-2h2"/><path d="M17 3h2a2 2 0 0 1 2 2v2"/><path d="M21 17v2a2 2 0 0 1-2 2h-2"/><path d="M7 21H5a2 2 0 0 1-2-2v-2"/><circle cx="12" cy="12" r="3"/></svg><span>Gantry</span></a>
    <a class="active" href="{{ route('gantry.recap') }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg><span>Rekap</span></a>
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

<div class="pagehead"><div><h1>Rekapitulasi &amp; Analisis Sesi</h1><div class="sub">Ringkasan seluruh sesi pemindaian &amp; penyiraman dari subsistem gantry</div></div></div>
@if($error)<div class="flash err">{{ $error }}</div>@endif
<div class="summary">
  <div class="stat"><b>{{ count($scans) }}</b><span>Sesi Pemindaian</span></div>
  <div class="stat"><b>{{ count($waters) }}</b><span>Sesi Penyiraman</span></div>
  <div class="stat"><b>{{ array_sum(array_column($scans,'ripe')) }}</b><span>Total Ripe</span></div>
</div>
<div class="section-title">A · Rekap Pemindaian Kematangan</div>
@if(count($scans))
<div class="panel"><table><thead><tr><th>No</th><th>ID</th><th>Tanggal (WIB)</th><th>Tanaman</th><th>Total</th><th>Ripe</th><th>Turn</th><th>Unripe</th><th>Broken</th><th>Siap Panen</th><th>% Ripe</th></tr></thead><tbody>
@foreach($scans as $i=>$s)<tr><td>{{ $i+1 }}</td><td>#{{ $s['id'] }}</td><td>{{ $s['date'] }}</td><td>{{ $s['plants'] }}</td><td>{{ $s['total'] }}</td>
<td class="cr">{{ $s['ripe'] }}</td><td class="ct">{{ $s['turning'] }}</td><td class="cu">{{ $s['unripe'] }}</td><td>{{ $s['broken'] }}</td><td>{{ $s['ready'] }}</td>
<td><b>{{ $s['pctRipe'] }}%</b></td></tr>@endforeach
</tbody></table></div>
<div class="muted" style="font-size:11.5px;margin-top:8px">Siap Panen = tanaman dengan buah ripe &gt; 5. % Ripe = proporsi buah matang.</div>
@else<div class="muted">Belum ada sesi pemindaian selesai.</div>@endif
<div class="section-title">B · Rekap Penyiraman</div>
@if(count($waters))
<div class="panel"><table><thead><tr><th>No</th><th>ID</th><th>Tanggal (WIB)</th><th>Titik Disiram</th><th>Durasi Fuzzy (s)</th><th>Lembab Sblm</th><th>Lembab Ssdh</th><th>Tinggi Maks (cm)</th></tr></thead><tbody>
@foreach($waters as $i=>$w)<tr><td>{{ $i+1 }}</td><td>#{{ $w['id'] }}</td><td>{{ $w['date'] }}</td><td>{{ $w['stops'] }}</td><td>{{ $w['duration'] ?? '-' }}</td><td>{{ $w['mb'] ?? '-' }}</td><td>{{ $w['ma'] ?? '-' }}</td><td>{{ $w['height'] ?? '-' }}</td></tr>@endforeach
</tbody></table></div>
<div class="muted" style="font-size:11.5px;margin-top:8px">⚠ Bila Lembab = 0 &amp; Durasi selalu sama → sensor kelembapan belum terpasang saat sesi itu.</div>
@else<div class="muted">Belum ada sesi penyiraman selesai.</div>@endif

</main>

</body></html>