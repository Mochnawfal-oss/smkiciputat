@php($logoUrl = $siteConfiguration->imageUrl($siteSettings['identity.logo']))
<footer class="site-footer">
    <div class="container">
        <div class="footer-main">
            <div class="footer-brand">
                <a href="{{ route('home') }}" class="brand">
                    @if ($logoUrl)
                        <img class="brand-logo" src="{{ $logoUrl }}" alt="Logo {{ $siteSettings['identity.school_name'] }}">
                    @else
                        <span class="brand-symbol" aria-hidden="true">SMK</span>
                    @endif

                    <span class="brand-text">
                        <strong>{{ mb_strtoupper($siteSettings['identity.school_name']) }}</strong>
                        <small>{{ mb_strtoupper($siteSettings['identity.location']) }}</small>
                    </span>
                </a>

                <p>{{ $siteSettings['footer.description'] }}</p>
            </div>

            <div class="footer-column">
                <h4>Navigasi</h4>
                <a href="{{ route('home') }}">Beranda</a>
                <a href="{{ route('about') }}">Tentang Kami</a>
                <a href="{{ route('teachers') }}">Tenaga Kependidikan</a>
                <a href="{{ route('spmb') }}">SPMB</a>
                <a href="{{ route('news.index') }}">Berita</a>
                <a href="{{ route('gallery') }}">Galeri</a>
            </div>

            <div class="footer-column">
                <h4>Program Keahlian</h4>
                @foreach ($navigationPrograms as $program)
                    <a href="{{ route('program.show', $program->slug) }}">{{ $program->title }}</a>
                @endforeach
            </div>

            <div class="footer-column">
                <h4>Bursa Kerja Khusus</h4>
                <a href="{{ route('bkk.jobs') }}">Lowongan Pekerjaan</a>
                <a href="{{ route('bkk.tracer-study') }}">Tracer Study</a>
                <h4>Informasi Sekolah</h4>
                @if ($siteSettings['identity.address'])
                    <p>{{ $siteSettings['identity.address'] }}</p>
                @endif
                <p>{{ $siteSettings['identity.location'] }}</p>
                @if ($siteSettings['identity.whatsapp'])
                    <a href="https://wa.me/{{ preg_replace('/\D+/', '', $siteSettings['identity.whatsapp']) }}">WhatsApp sekolah</a>
                @endif
                @if ($siteSettings['identity.email'])
                    <a href="mailto:{{ $siteSettings['identity.email'] }}">{{ $siteSettings['identity.email'] }}</a>
                @endif
                @foreach (['instagram' => 'Instagram', 'facebook' => 'Facebook', 'youtube' => 'YouTube'] as $socialKey => $socialLabel)
                    @if ($siteSettings['identity.'.$socialKey])
                        <a href="{{ $siteSettings['identity.'.$socialKey] }}" target="_blank" rel="noopener">{{ $socialLabel }}</a>
                    @endif
                @endforeach
            </div>
        </div>

        <div class="footer-bottom">
            <span>© {{ date('Y') }} {{ $siteSettings['identity.school_name'] }}.</span>
            <span>{{ $siteSettings['identity.copyright'] }}</span>
        </div>
    </div>
</footer>
