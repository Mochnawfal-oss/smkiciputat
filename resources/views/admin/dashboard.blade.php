@extends('admin.layout')

@section('title', 'Dashboard')
@section('page-title', 'Ringkasan Website')
@section('breadcrumb', 'Dashboard')

@section('content')
    <div class="admin-page-intro">
        <div>
            <h2>Selamat datang, {{ auth('admin')->user()->name }}</h2>
            <p>Kelola informasi publik SMK Islamiyah Ciputat dari satu tempat.</p>
        </div>
        <a href="{{ route('home') }}" target="_blank" rel="noopener" class="admin-button admin-button-secondary">Lihat website <span aria-hidden="true">↗</span></a>
    </div>

    <div class="admin-stat-grid">
        @foreach ([
            ['Program Keahlian', $counts['programs'], route('admin.content.index', 'programs'), '▦'],
            ['Tenaga Kependidikan', $counts['teachers'], route('admin.teachers.index'), '♙'],
            ['Berita', $counts['news'], route('admin.content.index', 'news'), '▤'],
            ['Lowongan BKK', $counts['jobs'], route('admin.content.index', 'jobs'), '▣'],
            ['Galeri', $counts['gallery'], route('admin.content.index', 'gallery'), '▧'],
        ] as [$label, $count, $url, $icon])
            <a class="admin-stat-card" href="{{ $url }}">
                <span class="admin-stat-icon" aria-hidden="true">{{ $icon }}</span>
                <span class="admin-stat-label">{{ $label }}</span>
                <strong>{{ number_format($count, 0, ',', '.') }}</strong>
                <span class="admin-stat-link">Kelola data <span aria-hidden="true">→</span></span>
            </a>
        @endforeach
    </div>

    <div class="admin-quick-actions">
        <a href="{{ route('admin.content.create', 'news') }}" class="admin-button admin-button-primary">+ Tambah berita</a>
        <a href="{{ route('admin.content.create', 'programs') }}" class="admin-button admin-button-secondary">+ Tambah program</a>
        <a href="{{ route('admin.teachers.create') }}" class="admin-button admin-button-secondary">+ Tambah tenaga kependidikan</a>
    </div>

    <div class="admin-dashboard-grid">
        <section class="admin-card">
            <div class="admin-card-heading">
                <div><h2>Berita terbaru</h2><p>Berita yang terakhir diperbarui</p></div>
                <a href="{{ route('admin.content.index', 'news') }}">Lihat semua</a>
            </div>
            @forelse ($latestNews as $article)
                <a href="{{ route('admin.content.edit', ['type' => 'news', 'entry' => $article]) }}" class="admin-recent-item">
                    <span class="admin-recent-date">{{ optional($article->published_at)->format('d M Y') ?? 'Belum terjadwal' }}</span>
                    <strong>{{ $article->title }}</strong>
                    <span class="admin-status admin-status-{{ $article->status }}">{{ $article->status === 'published' ? 'Published' : 'Draft' }}</span>
                </a>
            @empty
                <div class="admin-empty-state">Belum ada berita. Tambahkan berita pertama untuk ditampilkan di website.</div>
            @endforelse
        </section>

        <section class="admin-card">
            <div class="admin-card-heading">
                <div><h2>Aktivitas konten</h2><p>Perubahan terbaru di panel admin</p></div>
            </div>
            @forelse ($activities as $activity)
                <div class="admin-activity-item">
                    <span class="admin-activity-dot" aria-hidden="true"></span>
                    <div>
                        <strong>{{ ucfirst($activity->action) }} · {{ $activity->title }}</strong>
                        <small>{{ $activity->admin?->name ?? 'Admin dihapus' }} · {{ $activity->created_at->format('d M Y, H:i') }}</small>
                    </div>
                </div>
            @empty
                <div class="admin-empty-state">Aktivitas akan tercatat setelah konten dikelola.</div>
            @endforelse
        </section>
    </div>
@endsection
