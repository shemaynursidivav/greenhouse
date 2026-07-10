<!DOCTYPE html>
<html lang="id"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Gantry — Greenhouse Monitor</title>


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
  @media(max-width:860px){.sidebar{position:static;width:100%;height:auto;border-right:0;border-bottom:1px solid var(--line)}.sb-brand{padding:14px 16px}.sb-sec{display:none}.sb-nav{flex-direction:row;overflow-x:auto;gap:4px;padding:0 10px 10px}.sb-nav a,.sb-nav button{white-space:nowrap;padding:9px 13px}.sb-nav a.active::before{display:none}.sb-foot{margin-top:0;padding:0 10px 10px}.content{margin-left:0;padding:18px 14px 50px;max-width:100%}body{overflow-x:hidden}.grid{grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:10px}.panel,.scard,.stat{max-width:100%}table{display:block;overflow-x:auto;white-space:nowrap}.summary,.chips{overflow-x:auto}.pagehead h1{font-size:19px}}

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
    <a class="active" href="{{ route('gantry.live') }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7V5a2 2 0 0 1 2-2h2"/><path d="M17 3h2a2 2 0 0 1 2 2v2"/><path d="M21 17v2a2 2 0 0 1-2 2h-2"/><path d="M7 21H5a2 2 0 0 1-2-2v-2"/><circle cx="12" cy="12" r="3"/></svg><span>Gantry</span></a>
    <a class="" href="{{ route('gantry.recap') }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg><span>Rekap</span></a>
    <a class="" href="{{ route('soil.index') }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg><span>Grafik Sensor</span></a>
  </nav>
  <div class="sb-foot">
    <div class="sb-sec" style="padding-top:0">Akun</div>
    <nav class="sb-nav">
      <form method="POST" action="{ url('/logout') }">@csrf<button type="submit" class="logout"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg><span>Logout</span></button></form>
    </nav>
  </div>
</aside>
<main class="content">

<div class="pagehead">
  <div><h1>Kontrol &amp; Akuisisi Gantry</h1>
    <div class="sub"><span id="meta" class="muted">memuat…</span> <span id="updated" class="muted"></span> <span id="err" style="color:var(--warn)"></span></div></div>
</div>
@if(session('gantry_error'))<div class="flash err">{{ session('gantry_error') }}</div>@endif
@if(session('success'))<div class="flash">{{ session('success') }}</div>@endif

<div class="section-title">Kontrol Sesi</div>
<div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:6px">
  <form method="POST" action="{{ route('gantry.start') }}" onsubmit="return confirm('Mulai SCAN? Gantry akan bergerak.')">@csrf<input type="hidden" name="sessionType" value="SCAN"><button class="btn">▶ Mulai Scan</button></form>
  <form method="POST" action="{{ route('gantry.start') }}" onsubmit="return confirm('Mulai WATERING? Gantry akan menyiram.')">@csrf<input type="hidden" name="sessionType" value="WATERING"><button class="btn cyan">💧 Mulai Watering</button></form>
  @if($sessionId)<form method="POST" action="{{ route('gantry.stop', ['id'=>$sessionId]) }}">@csrf<button class="btn danger">■ Stop Sesi #{{ $sessionId }}</button></form>@endif
</div>
<div class="muted" style="font-size:12px;margin-bottom:4px">Satu sesi berjalan dalam satu waktu. Hasil muncul otomatis di bawah.</div>

<div class="section-title">Hasil Pemindaian (Real-Time)</div>
<div class="summary" id="summary"></div>
<div id="feat" style="display:none;grid-template-columns:minmax(0,360px) 1fr;gap:16px;margin-bottom:20px" class="feat-wrap">
  <img id="featImg" style="width:100%;border-radius:12px;border:1px solid var(--line);background:#0c1424" alt="">
  <div class="panel pad"><div style="color:var(--accent);font-size:11px;font-weight:700;letter-spacing:.4px">CAPTURE TERBARU</div>
    <h2 id="featCode" style="margin:4px 0 10px;font-size:24px">—</h2>
    <div style="display:flex;gap:12px;font-size:13px" class="muted"><span class="cr">Ripe: <b id="fR">0</b></span><span class="ct">Turning: <b id="fT">0</b></span><span class="cu">Unripe: <b id="fU">0</b></span><span>Total: <b id="fTot">0</b></span></div>
    <div id="featTime" class="muted" style="margin-top:8px;font-size:12px"></div></div>
</div>
<div class="grid" id="grid"></div>

<div class="section-title">Riwayat Sesi</div>
<div class="panel"><table><thead><tr><th>ID</th><th>Tipe</th><th>Status</th><th>Tanaman</th><th>Mulai (WIB)</th><th></th></tr></thead>
  <tbody id="histBody"><tr><td colspan="6" class="muted" style="padding:16px">memuat…</td></tr></tbody></table></div>

</main>
<script>
const SID=@json($sessionId ?? null);
const BASE="{{ route('gantry.live.data') }}", DATA_URL=SID?(BASE+"?id="+SID):BASE;
const SESS_URL="{{ route('gantry.sessions') }}", IMG_URL="{{ route('gantry.img') }}", HERE="{{ route('gantry.live') }}";
const imgSrc=(p)=>p?IMG_URL+"?p="+encodeURIComponent(p):"";
const el=(i)=>document.getElementById(i);
const stat=(v,l,c='')=>`<div class="stat"><b class="${c}">${v}</b><span>${l}</span></div>`;
const fmt=(s)=>{try{return new Date(s).toLocaleString('id-ID');}catch(e){return s||'';}};
function render(session){
  if(!session){el('meta').textContent='Belum ada data sesi.';return;}
  const caps=(session.captures||[]).slice().sort((a,b)=>(a.plantIndex||0)-(b.plantIndex||0));
  let R=0,T=0,U=0,BR=0,ready=0;caps.forEach(c=>{R+=c.ripeCount||0;T+=c.turningCount||0;U+=c.unripeCount||0;BR+=c.brokenCount||0;if((c.ripeCount||0)>5)ready++;});
  el('summary').innerHTML=stat(caps.length,'Tanaman')+stat(R+T+U+BR,'Total buah')+stat(R,'Ripe','cr')+stat(T,'Turning','ct')+stat(U,'Unripe','cu')+stat(BR,'Broken')+stat(ready,'Siap panen','cr');
  const run=(session.status==='RUNNING');
  el('meta').innerHTML=`Sesi #${session.id} <span class="st st-${session.status}">${session.status}</span> · ${caps.length} tanaman`;
  const newest=caps.slice().sort((a,b)=>new Date(b.scannedAt||0)-new Date(a.scannedAt||0))[0];
  if(newest){el('feat').style.display='grid';el('featImg').src=imgSrc(newest.annotatedImageUrl||newest.imageUrl);
    el('featCode').textContent='T-'+(newest.plantIndex??'?');el('fR').textContent=newest.ripeCount||0;el('fT').textContent=newest.turningCount||0;
    el('fU').textContent=newest.unripeCount||0;el('fTot').textContent=newest.totalFruits||0;el('featTime').textContent='dipindai: '+fmt(newest.scannedAt);}
  el('grid').innerHTML=caps.map(c=>{const src=imgSrc(c.annotatedImageUrl||c.imageUrl);
    const badge=(c.ripeCount||0)>5?'<span class="sbadge b-danger" style="position:static;margin-left:6px">Siap panen</span>':'';
    const img=src?`<img src="${src}" loading="lazy" style="width:100%;height:100%;object-fit:cover">`:'<span class="muted">no img</span>';
    return `<div class="scard" style="padding:0;overflow:hidden"><div style="aspect-ratio:1/1;background:#0c1424;display:flex;align-items:center;justify-content:center">${img}</div>
      <div style="padding:10px 12px"><div style="font-weight:700;display:flex;align-items:center">T-${c.plantIndex??'?'} ${badge}</div>
      <div style="display:flex;gap:8px;margin-top:5px;font-size:12px" class="muted"><span class="cr">R${c.ripeCount||0}</span><span class="ct">T${c.turningCount||0}</span><span class="cu">U${c.unripeCount||0}</span><span>Σ${c.totalFruits||0}</span></div></div></div>`;
  }).join('');
}
async function tick(){try{const r=await fetch(DATA_URL,{cache:'no-store'});if(!r.ok)throw 0;const j=await r.json();render(j.session);el('err').textContent='';el('updated').textContent='diperbarui '+new Date().toLocaleTimeString('id-ID');}catch(e){el('err').textContent='⚠ memuat ulang…';}}
async function loadHist(){try{const j=await(await fetch(SESS_URL,{cache:'no-store'})).json();
  el('histBody').innerHTML=(j.sessions||[]).map(s=>{const act=(SID&&String(SID)===String(s.id))?'style="background:rgba(79,140,255,.08)"':'';
    const lihat=s.caps?`<a class="btn ghost" style="padding:4px 12px;font-size:11.5px" href="${HERE}?id=${s.id}">Lihat</a>`:'<span class="muted">—</span>';
    return `<tr ${act}><td>#${s.id}</td><td>${s.type}</td><td><span class="st st-${s.status}">${s.status}</span></td><td>${s.plants??'-'}</td><td>${s.date}</td><td style="text-align:right">${lihat}</td></tr>`;}).join('')||'<tr><td colspan=6 class=muted style=padding:16px>Belum ada sesi.</td></tr>';
}catch(e){el('histBody').innerHTML='<tr><td colspan=6 style="padding:16px;color:var(--warn)">Gagal memuat riwayat.</td></tr>';}}
tick();setInterval(tick,4000);loadHist();setInterval(loadHist,15000);
</script>
</body></html>