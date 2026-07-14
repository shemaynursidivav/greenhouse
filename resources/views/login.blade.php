<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Masuk — Greenhouse Monitor</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root{
            --forest:#2C5F2D; --forest-d:#1E4620; --moss:#97BC62; --ink:#20301F;
        }
        body{
            font-family: -apple-system, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            min-height:100vh; display:flex; align-items:center; justify-content:center;
            position:relative; overflow:hidden; padding:24px;
        }
        /* Background image + overlay */
        body::before{
            content:""; position:fixed; inset:0; z-index:-2;
            background:url('{{ asset('images/greenhouse-login.jpg') }}') center/cover no-repeat;
            transform:scale(1.05);
        }
        body::after{
            content:""; position:fixed; inset:0; z-index:-1;
            background:linear-gradient(135deg, rgba(30,70,32,.82) 0%, rgba(44,95,45,.55) 55%, rgba(20,40,25,.85) 100%);
        }
        .card{
            width:100%; max-width:410px;
            background:rgba(255,255,255,.12);
            backdrop-filter:blur(18px) saturate(140%);
            -webkit-backdrop-filter:blur(18px) saturate(140%);
            border:1px solid rgba(255,255,255,.25);
            border-radius:22px; padding:38px 34px 32px;
            box-shadow:0 24px 60px rgba(0,0,0,.35);
            color:#fff; animation:rise .6s ease;
        }
        @keyframes rise{ from{opacity:0; transform:translateY(18px);} to{opacity:1; transform:none;} }
        .badge{
            width:62px; height:62px; border-radius:18px; margin:0 auto 16px;
            background:linear-gradient(135deg,var(--moss),var(--forest));
            display:flex; align-items:center; justify-content:center;
            box-shadow:0 8px 22px rgba(0,0,0,.3);
        }
        .badge svg{ width:34px; height:34px; }
        h1{ font-size:22px; text-align:center; font-weight:700; letter-spacing:.2px; }
        .sub{ text-align:center; font-size:13px; color:rgba(255,255,255,.8); margin:6px 0 26px; }
        label{ display:block; font-size:12.5px; font-weight:600; margin:0 0 7px 2px; color:rgba(255,255,255,.9); }
        .field{ position:relative; margin-bottom:18px; }
        .field svg{ position:absolute; left:14px; top:50%; transform:translateY(-50%); width:18px; height:18px; opacity:.7; }
        input[type=email], input[type=password]{
            width:100%; padding:13px 14px 13px 42px; font-size:14px;
            background:rgba(255,255,255,.16); border:1px solid rgba(255,255,255,.28);
            border-radius:12px; color:#fff; outline:none; transition:.2s;
        }
        input::placeholder{ color:rgba(255,255,255,.6); }
        input:focus{ border-color:var(--moss); background:rgba(255,255,255,.22); box-shadow:0 0 0 3px rgba(151,188,98,.3); }
        .row{ display:flex; align-items:center; justify-content:space-between; margin-bottom:22px; font-size:12.5px; }
        .row label{ display:flex; align-items:center; gap:7px; margin:0; font-weight:500; cursor:pointer; }
        .btn{
            width:100%; padding:13px; border:none; border-radius:12px; cursor:pointer;
            background:linear-gradient(135deg,var(--forest),var(--forest-d));
            color:#fff; font-size:15px; font-weight:700; letter-spacing:.3px; transition:.2s;
            box-shadow:0 10px 24px rgba(30,70,32,.45);
        }
        .btn:hover{ transform:translateY(-1px); filter:brightness(1.08); }
        .alert{
            background:rgba(192,57,43,.85); border:1px solid rgba(255,255,255,.25);
            color:#fff; font-size:12.5px; padding:10px 13px; border-radius:10px; margin-bottom:18px;
        }
        .ok{ background:rgba(44,95,45,.85); }
        .foot{ text-align:center; font-size:11.5px; color:rgba(255,255,255,.65); margin-top:22px; }
    </style>
</head>
<body>
    <form class="card" method="POST" action="{{ url('login') }}">
        @csrf

        <div class="badge">
            <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 22c4-2 7-6 7-11a7 7 0 0 0-14 0c0 5 3 9 7 11Z"/>
                <path d="M12 22V9"/><path d="M12 13c-2 0-4-1.5-4-4"/><path d="M12 11c2 0 4-1.5 4-4"/>
            </svg>
        </div>

        <h1>Greenhouse Monitor</h1>
        <p class="sub">Sistem Monitoring &amp; Notifikasi Real-Time</p>

        @if (session('status'))
            <div class="alert ok">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert">{{ $errors->first() }}</div>
        @endif

        <label for="email">Email</label>
        <div class="field">
            <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.8"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
            <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="nama@email.com" required autofocus>
        </div>

        <label for="password">Kata Sandi</label>
        <div class="field">
            <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.8"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
            <input id="password" type="password" name="password" placeholder="••••••••" required>
        </div>

        <div class="row">
            <label><input type="checkbox" name="remember"> Ingat saya</label>
        </div>

        <button type="submit" class="btn">Masuk</button>

        <p class="foot">Universitas Sriwijaya &middot; Teknik Elektro</p>
    </form>
</body>
</html>