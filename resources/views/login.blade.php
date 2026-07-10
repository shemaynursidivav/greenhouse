<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Greenhouse Monitor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f0f4f8; }
        .login-card {
            max-width: 400px;
            margin: 100px auto;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.1);
        }
        .card-header {
            background: #198754;
            color: white;
            border-radius: 16px 16px 0 0 !important;
            text-align: center;
            padding: 24px;
        }
    </style>
</head>
<body>
    <div class="card login-card">
        <div class="card-header">
            <h4 class="mb-0">🌿 Greenhouse Monitor</h4>
            <small>Sistem Monitoring IoT</small>
        </div>
        <div class="card-body p-4">
            @if($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif
            <form method="POST" action="{{ url('/login') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control"
                           value="{{ old('email') }}" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-success w-100">Login</button>
            </form>
        </div>
    </div>
</body>
</html>