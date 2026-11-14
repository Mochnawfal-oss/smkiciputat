<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex,nofollow">
    <meta name="theme-color" content="#101b35">
    <title>Superadmin Berhasil Dibuat | SMK Islamiyah Ciputat</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="setup-body setup-body-dark">
    <main class="setup-success-page">
        <div class="setup-welcome-grid" aria-hidden="true"></div>
        <div class="setup-welcome-glow setup-welcome-glow-one" aria-hidden="true"></div>

        <a href="{{ route('home') }}" class="setup-brand setup-brand-welcome setup-success-brand">
            <img src="{{ asset('images/logo/logo.PNG') }}" alt="Logo SMK Islamiyah Ciputat">
            <span><strong>SMK ISLAMIYAH</strong><small>CIPUTAT · SISTEM INFORMASI</small></span>
        </a>

        <section class="setup-success-card" aria-labelledby="setup-success-title">
            <div class="setup-success-mark"><i data-lucide="check" aria-hidden="true"></i></div>
            <p class="setup-success-eyebrow">INITIAL SETUP COMPLETE</p>
            <h1 id="setup-success-title">Akun Superadmin<br>Berhasil Dibuat!</h1>
            <p class="setup-success-description">Konfigurasi awal sistem informasi sekolah telah selesai. Akun Anda kini memiliki akses tertinggi untuk mengelola website dan hak akses administrator.</p>

            <div class="setup-success-access">
                <i data-lucide="shield-check" aria-hidden="true"></i>
                <span><strong>Administrator utama aktif</strong><small>Gunakan akses ini dengan bijak dan aman.</small></span>
            </div>

            <a href="{{ route('admin.dashboard') }}" class="setup-primary-link setup-success-button">
                <span>Masuk ke Dashboard</span>
                <i data-lucide="arrow-right" aria-hidden="true"></i>
            </a>

            <p class="setup-success-footer">SMK ISLAMIYAH CIPUTAT <span>·</span> SECURE ADMINISTRATION</p>
        </section>
    </main>
</body>
</html>
