<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex,nofollow">
    <title>Login Admin | SMK Islamiyah Ciputat</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-login-body">
    <main class="admin-login-card">
        <img src="{{ asset('images/logo/logo.PNG') }}" alt="Logo SMK Islamiyah Ciputat" class="admin-login-logo">
        <p class="admin-login-eyebrow">PENGELOLAAN WEBSITE</p>
        <h1>Masuk ke Admin Panel</h1>
        <p class="admin-login-description">Akun dibuat oleh Superadmin. Peran akun menentukan menu dan akses yang tersedia; peran tidak dipilih saat login.</p>

        @if (session('success'))
            <div class="admin-alert admin-alert-success" role="status">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="admin-alert admin-alert-error" role="alert">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('admin.login.submit') }}" class="admin-form">
            @csrf
            <label for="username">Username</label>
            <input id="username" name="username" type="text" value="{{ old('username') }}" autocomplete="username" required autofocus>

            <label for="password">Kata sandi</label>
            <input id="password" name="password" type="password" autocomplete="current-password" required>

            <label class="admin-checkbox-label">
                <input type="checkbox" name="remember" value="1">
                <span>Ingat saya di perangkat ini</span>
            </label>

            <button type="submit" class="admin-button admin-button-primary admin-login-submit">Masuk</button>
        </form>

        @if ($canRegisterSuperAdmin)
            <a href="{{ route('admin.register') }}" class="admin-login-back">Pendaftaran Superadmin pertama untuk testing</a>
        @endif

        <a href="{{ route('home') }}" class="admin-login-back">← Kembali ke website</a>
    </main>
</body>
</html>
