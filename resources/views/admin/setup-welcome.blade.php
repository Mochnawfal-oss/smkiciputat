<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex,nofollow">
    <meta name="theme-color" content="#101b35">
    <title>Penyiapan Awal | SMK Islamiyah Ciputat</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="setup-body setup-body-dark">
    <main class="setup-welcome">
        <div class="setup-welcome-grid" aria-hidden="true"></div>
        <div class="setup-welcome-glow setup-welcome-glow-one" aria-hidden="true"></div>
        <div class="setup-welcome-glow setup-welcome-glow-two" aria-hidden="true"></div>

        <div class="setup-welcome-main">
            <a href="{{ route('home') }}" class="setup-brand setup-brand-welcome">
                <img src="{{ asset('images/logo/logo.PNG') }}" alt="Logo SMK Islamiyah Ciputat">
                <span><strong>SMK ISLAMIYAH</strong><small>CIPUTAT · SISTEM INFORMASI</small></span>
            </a>

            <section class="setup-welcome-copy">
                <p class="setup-kicker"><span></span> INITIAL SYSTEM SETUP</p>
                <h1>Selamat Datang di<br><span>Sistem Informasi<br>SMK Ciputat</span></h1>
                <p class="setup-welcome-description">Website ini belum memiliki administrator utama. Selesaikan konfigurasi awal dengan membuat akun Superadmin untuk mengelola sistem informasi sekolah.</p>

                <a href="{{ route('admin.register.form') }}" class="setup-primary-link">
                    <span>Mulai Konfigurasi</span>
                    <i data-lucide="arrow-right" aria-hidden="true"></i>
                </a>

                <p class="setup-once-note"><i data-lucide="lock-keyhole" aria-hidden="true"></i> Konfigurasi ini hanya dilakukan satu kali pada setiap instalasi website.</p>
            </section>
        </div>

        <aside class="setup-welcome-aside" aria-label="Tahapan konfigurasi">
            <div class="setup-welcome-visual" aria-hidden="true">
                <div class="setup-visual-ring setup-visual-ring-outer"></div>
                <div class="setup-visual-ring setup-visual-ring-inner"></div>
                <div class="setup-visual-center"><i data-lucide="shield-check"></i></div>
                <span class="setup-visual-node setup-visual-node-one"></span>
                <span class="setup-visual-node setup-visual-node-two"></span>
                <span class="setup-visual-node setup-visual-node-three"></span>
            </div>

            <ol class="setup-steps">
                <li><span>01</span><p>Buat akun Superadmin</p></li>
                <li><span>02</span><p>Konfigurasikan akses administrator</p></li>
                <li><span>03</span><p>Mulai kelola website sekolah</p></li>
            </ol>
        </aside>

        <footer class="setup-welcome-footer">
            <span>SMK ISLAMIYAH CIPUTAT</span>
            <span>ADMINISTRATOR SETUP <i></i> SECURE ONBOARDING</span>
        </footer>
    </main>
</body>
</html>
