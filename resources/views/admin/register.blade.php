<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex,nofollow">
    <meta name="theme-color" content="#101b35">
    <title>Buat Akun Superadmin | SMK Islamiyah Ciputat</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="setup-body">
    <main class="setup-form-layout">
        <section class="setup-brand-panel">
            <a href="{{ route('home') }}" class="setup-brand" aria-label="SMK Islamiyah Ciputat">
                <img src="{{ asset('images/logo/logo.PNG') }}" alt="Logo SMK Islamiyah Ciputat">
                <span><strong>SMK ISLAMIYAH</strong><small>CIPUTAT · SISTEM INFORMASI</small></span>
            </a>

            <div class="setup-brand-copy">
                <span class="setup-kicker"><span></span> FIRST ADMINISTRATOR SETUP</span>
                <h1>Buat Akun<br><span>Superadmin.</span></h1>
                <p>Akun ini memiliki akses tertinggi untuk mengelola sistem informasi sekolah dan mengatur hak akses administrator.</p>
            </div>

            <div class="setup-security-card">
                <span class="setup-security-icon" aria-hidden="true"><i data-lucide="shield-check"></i></span>
                <div>
                    <strong>Konfigurasi terlindungi</strong>
                    <p>Akun Superadmin pertama hanya dapat dibuat satu kali pada instalasi ini.</p>
                </div>
            </div>

            <div class="setup-abstract-illustration" aria-hidden="true">
                <div class="setup-illustration-orbit setup-illustration-orbit-one"></div>
                <div class="setup-illustration-orbit setup-illustration-orbit-two"></div>
                <div class="setup-illustration-shield"><i data-lucide="shield-check"></i></div>
                <div class="setup-illustration-dot setup-illustration-dot-one"></div>
                <div class="setup-illustration-dot setup-illustration-dot-two"></div>
            </div>

            <p class="setup-panel-footer">PENYIAPAN SISTEM · LANGKAH 02 DARI 03</p>
        </section>

        <section class="setup-form-panel" aria-labelledby="setup-form-title">
            <div class="setup-form-content">
                <a href="{{ route('admin.register') }}" class="setup-back-link">
                    <i data-lucide="arrow-left" aria-hidden="true"></i>
                    <span>Kembali ke sambutan</span>
                </a>

                <p class="setup-form-eyebrow">INFORMASI ADMINISTRATOR</p>
                <h2 id="setup-form-title">Buat Administrator Utama</h2>
                <p class="setup-form-intro">Lengkapi informasi berikut untuk membuat akun Superadmin pertama Anda.</p>

                @if ($errors->any())
                    <div class="setup-error-summary" role="alert">
                        <i data-lucide="circle-alert" aria-hidden="true"></i>
                        <div>
                            <strong>Data belum dapat disimpan.</strong>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.register.store') }}" class="setup-form" data-setup-form>
                    @csrf

                    <div class="setup-field">
                        <label for="name">Nama Lengkap</label>
                        <div class="setup-input-wrap">
                            <i data-lucide="user-round" aria-hidden="true"></i>
                            <input id="name" name="name" type="text" value="{{ old('name') }}" autocomplete="name" maxlength="120" required autofocus @class(['has-error' => $errors->has('name')])>
                        </div>
                        @error('name')<small class="setup-field-error">{{ $message }}</small>@enderror
                    </div>

                    <div class="setup-field">
                        <label for="username">Username</label>
                        <div class="setup-input-wrap">
                            <i data-lucide="at-sign" aria-hidden="true"></i>
                            <input id="username" name="username" type="text" value="{{ old('username') }}" autocomplete="username" maxlength="60" required @class(['has-error' => $errors->has('username')])>
                        </div>
                        <small class="setup-field-hint">Gunakan huruf, angka, tanda hubung, atau garis bawah.</small>
                        @error('username')<small class="setup-field-error">{{ $message }}</small>@enderror
                    </div>

                    <div class="setup-field">
                        <label for="email">Email <span>(opsional)</span></label>
                        <div class="setup-input-wrap">
                            <i data-lucide="mail" aria-hidden="true"></i>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" maxlength="255" @class(['has-error' => $errors->has('email')])>
                        </div>
                        @error('email')<small class="setup-field-error">{{ $message }}</small>@enderror
                    </div>

                    <div class="setup-field">
                        <label for="password">Password</label>
                        <div class="setup-input-wrap">
                            <i data-lucide="lock-keyhole" aria-hidden="true"></i>
                            <input id="password" name="password" type="password" autocomplete="new-password" minlength="8" required aria-describedby="password-requirement password-strength-label" data-setup-password @class(['has-error' => $errors->has('password')])>
                            <button type="button" class="setup-password-toggle" aria-label="Tampilkan password" aria-controls="password" data-setup-password-toggle>
                                <span data-password-eye><i data-lucide="eye" aria-hidden="true"></i></span>
                                <span data-password-eye-off hidden><i data-lucide="eye-off" aria-hidden="true"></i></span>
                            </button>
                        </div>
                        <div class="setup-password-strength">
                            <div class="setup-strength-track" data-password-meter role="progressbar" aria-label="Kekuatan password" aria-valuemin="0" aria-valuemax="5" aria-valuenow="0">
                                <span data-password-meter-fill></span>
                            </div>
                            <span id="password-strength-label" data-password-strength-label aria-live="polite">Belum diisi</span>
                        </div>
                        <small id="password-requirement" class="setup-field-hint">Minimal 8 karakter. Gunakan kombinasi huruf, angka, dan simbol.</small>
                        @error('password')<small class="setup-field-error">{{ $message }}</small>@enderror
                    </div>

                    <div class="setup-field">
                        <label for="password_confirmation">Konfirmasi Password</label>
                        <div class="setup-input-wrap">
                            <i data-lucide="key-round" aria-hidden="true"></i>
                            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required @class(['has-error' => $errors->has('password')])>
                            <button type="button" class="setup-password-toggle" aria-label="Tampilkan konfirmasi password" aria-controls="password_confirmation" data-setup-password-toggle>
                                <span data-password-eye><i data-lucide="eye" aria-hidden="true"></i></span>
                                <span data-password-eye-off hidden><i data-lucide="eye-off" aria-hidden="true"></i></span>
                            </button>
                        </div>
                        @error('password')<small class="setup-field-error">{{ $message }}</small>@enderror
                    </div>

                    <div class="setup-form-note">
                        <i data-lucide="info" aria-hidden="true"></i>
                        <p>Akun ini menjadi administrator utama yang berwenang mengelola akun dan hak akses pengguna.</p>
                    </div>

                    <button type="submit" class="setup-submit-button" data-setup-submit>
                        <span data-submit-label>Buat Akun Superadmin</span>
                        <i data-lucide="arrow-right" aria-hidden="true" data-submit-arrow></i>
                        <span class="setup-submit-spinner" aria-hidden="true" data-submit-spinner hidden></span>
                    </button>
                </form>

                <p class="setup-form-privacy"><i data-lucide="shield-check" aria-hidden="true"></i> Informasi akun Anda diproses dengan aman.</p>
            </div>
        </section>
    </main>
</body>
</html>
