<footer class="site-footer">
    <div class="container">

        <div class="footer-main">

            <div class="footer-brand">
                <a href="{{ route('home') }}" class="brand">
                    <img class="brand-logo" src="{{ asset('images/logo/logo.PNG') }}" alt="Logo SMK Islamiyah Ciputat">

                    <span class="brand-text">
                        <strong>SMK ISLAMIYAH</strong>
                        <small>CIPUTAT · TANGERANG SELATAN</small>
                    </span>
                </a>

                <p>
                    Website resmi SMK Islamiyah Ciputat sebagai pusat
                    informasi sekolah, program keahlian, kegiatan,
                    dan penerimaan peserta didik baru.
                </p>
            </div>

            <div class="footer-column">
                <h4>Navigasi</h4>
                <a href="{{ route('home') }}">Beranda</a>
                <a href="{{ route('about') }}">Tentang Kami</a>
                <a href="{{ route('teachers') }}">Tenaga Kependidikan</a>
                <a href="{{ route('spmb') }}">SPMB</a>
            </div>

            <div class="footer-column">
                <h4>Program Keahlian</h4>
                <a href="{{ route('program.kuliner') }}">Kuliner</a>
                <a href="{{ route('program.dkv') }}">DKV</a>
                <a href="{{ route('program.tkj') }}">TKJ</a>
                <a href="{{ route('program.ak') }}">Akuntansi</a>
                <a href="{{ route('program.mp') }}">Manajemen Perkantoran</a>
                <a href="{{ route('program.bd') }}">Bisnis Digital</a>
            </div>

            <div class="footer-column">
                <h4>Bursa Kerja Khusus</h4>
                <a href="{{ route('bkk.jobs') }}">Lowongan Pekerjaan</a>
                <a href="{{ route('bkk.tracer-study') }}">Tracer Study</a>
                <h4>Informasi Sekolah</h4>
                <p>Ciputat, Tangerang Selatan</p>
            </div>

        </div>

        <div class="footer-bottom">
            <span>© {{ date('Y') }} SMK Islamiyah Ciputat.</span>
            <span>Website Informasi Sekolah</span>
        </div>

    </div>
</footer>
