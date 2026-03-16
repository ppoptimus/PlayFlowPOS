<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | PlayFlow POS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Prompt', sans-serif;
            min-height: 100vh;
            margin: 0;
            display: grid;
            place-items: center;
            background: radial-gradient(circle at 20% 20%, #d4edf9 0%, #e8f3fa 42%, #edf7fb 100%);
        }
        .login-card {
            width: min(92vw, 420px);
            border: 1px solid rgba(31, 115, 224, 0.14);
            border-radius: 1.1rem;
            background: #ffffff;
            box-shadow: 0 18px 36px rgba(20, 73, 124, 0.14);
        }
        .brand-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            border-radius: 999px;
            padding: 0.35rem 0.75rem;
            background: rgba(31, 115, 224, 0.1);
            color: #1f73e0;
            font-weight: 700;
        }
        .btn-primary {
            background: linear-gradient(135deg, #2d8ff0, #14b89a);
            border: none;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #246fd0, #109079);
        }
    </style>
</head>
<body>
    <div class="login-card p-4 p-md-5">
        <div class="mb-4 text-center">
            <span class="brand-pill"><i class="bi bi-flower1"></i> PlayFlow POS</span>
            <h1 class="h4 fw-bold mt-3 mb-1">เข้าสู่ระบบ</h1>
            <p class="text-muted mb-0">ใช้ Username และ Password ของพนักงาน</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger py-2" role="alert">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.attempt') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label fw-semibold" for="username">Username</label>
                <input
                    id="username"
                    type="text"
                    name="username"
                    class="form-control form-control-lg @error('username') is-invalid @enderror"
                    value="{{ old('username') }}"
                    required
                    autofocus
                    autocomplete="username"
                >
                @error('username')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold" for="password">Password</label>
                <input
                    id="password"
                    type="password"
                    name="password"
                    class="form-control form-control-lg @error('password') is-invalid @enderror"
                    required
                    autocomplete="current-password"
                >
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold">เข้าสู่ระบบ</button>
        </form>
    </div>
</body>
</html>
