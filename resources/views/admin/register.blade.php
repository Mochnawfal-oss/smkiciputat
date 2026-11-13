<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex,nofollow">
    <title>Buat Superadmin | SMK Islamiyah Ciputat</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-login-body">
    <main class="admin-login-card">
        <img src="{{ asset('images/logo/logo.PNG') }}" alt="Logo SMK Islamiyah Ciputat" class="admin-login-logo">
        <p class="admin-login-eyebrow">PENYIAPAN PANEL</p>
        <h1>Buat Superadmin Pertama</h1>
        <p class="admin-login-description">Formulir ini hanya tersedia untuk testing sebelum Superadmin pertama dibuat. Akun ini memiliki akses penuh dan dapat membuat akun Admin atau Editor.</p>

        @if ($errors->any())
            <div class="admin-alert admin-alert-error" role="alert">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('admin.register.store') }}" class="admin-form">
            @csrf

            <label for="name">Nama lengkap</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" autocomplete="name" maxlength="120" required autofocus>

            <label for="username">Username</label>
            <input id="username" name="username" type="text" value="{{ old('username') }}" autocomplete="username" maxlength="60" required>

            <label for="email">Email (opsional)</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" maxlength="255">

            <label for="password">Kata sandi</label>
            <input id="password" name="password" type="password" autocomplete="new-password" required>

            <label for="password_confirmation">Ulangi kata sandi</label>
            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>

            <button type="submit" class="admin-button admin-button-primary admin-login-submit">Buat Superadmin</button>
        </form>

        <a href="{{ route('admin.login') }}" class="admin-login-back">← Kembali ke login</a>
    </main>
</body>
</html>
