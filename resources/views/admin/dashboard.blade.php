@extends('admin.layout')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard Overview')
@section('breadcrumb', 'Dashboard')

@section('content')
    <section class="admin-dashboard-intro">
        <div>
            <p class="admin-dashboard-eyebrow">RINGKASAN SEKOLAH</p>
            <h2>Dashboard Overview</h2>
            <p class="admin-dashboard-welcome">Selamat datang kembali, <strong>{{ auth('admin')->user()->name }}</strong>.</p>
            <p class="admin-dashboard-context">Pantau konten, tenaga kependidikan, dan aktivitas terbaru website sekolah dari satu tempat.</p>
            <p class="admin-dashboard-role">Peran Anda: <strong>{{ auth('admin')->user()->roleLabel() }}</strong>. {{ auth('admin')->user()->roleDescription() }}</p>
        </div>
        <a href="{{ route('home') }}" target="_blank" rel="noopener" class="admin-button admin-button-secondary">
            <i data-lucide="external-link" aria-hidden="true"></i>
            Lihat website
        </a>
    </section>

    <section class="admin-dashboard-section" aria-labelledby="admin-stats-title">
        <div class="admin-dashboard-section-heading">
            <div>
                <h2 id="admin-stats-title">Data Website</h2>
                <p>Jumlah data yang tersimpan pada sistem saat ini.</p>
            </div>
        </div>
        <div class="admin-stat-grid">
        @foreach ([
            ['Program Keahlian', $counts['programs'], route('admin.content.index', 'programs'), 'book-open', 'program'],
            ['Tenaga Kependidikan', $counts['teachers'], route('admin.teachers.index'), 'graduation-cap', 'teachers'],
            ['Berita', $counts['news'], route('admin.content.index', 'news'), 'newspaper', 'news'],
            ['Lowongan BKK', $counts['jobs'], route('admin.content.index', 'jobs'), 'briefcase-business', 'jobs'],
            ['Galeri', $counts['gallery'], route('admin.content.index', 'gallery'), 'images', 'gallery'],
        ] as [$label, $count, $url, $icon, $tone])
            <a class="admin-stat-card admin-stat-card-{{ $tone }}" href="{{ $url }}">
                <span class="admin-stat-card-top">
                    <span class="admin-stat-icon" aria-hidden="true"><i data-lucide="{{ $icon }}"></i></span>
                    <i class="admin-stat-arrow" data-lucide="arrow-up-right" aria-hidden="true"></i>
                </span>
                <span class="admin-stat-label">{{ $label }}</span>
                <strong>{{ number_format($count, 0, ',', '.') }}</strong>
                <span class="admin-stat-link">Lihat dan kelola data</span>
            </a>
        @endforeach
        </div>
    </section>

    <section class="admin-quick-panel" aria-labelledby="admin-quick-title">
        <div class="admin-dashboard-section-heading">
            <div>
                <h2 id="admin-quick-title">Akses Cepat</h2>
                <p>Pintasan ke fitur pengelolaan yang tersedia untuk akun Anda.</p>
            </div>
        </div>
        <div class="admin-quick-actions">
            <a href="{{ route('admin.content.create', 'news') }}" class="admin-quick-action">
                <span class="admin-quick-action-icon"><i data-lucide="newspaper" aria-hidden="true"></i></span>
                <span><strong>Tambah Berita</strong><small>Buat informasi terbaru sekolah</small></span>
                <i class="admin-quick-action-arrow" data-lucide="arrow-right" aria-hidden="true"></i>
            </a>
            <a href="{{ route('admin.content.create', 'programs') }}" class="admin-quick-action">
                <span class="admin-quick-action-icon"><i data-lucide="book-open" aria-hidden="true"></i></span>
                <span><strong>Tambah Program</strong><small>Perbarui program keahlian</small></span>
                <i class="admin-quick-action-arrow" data-lucide="arrow-right" aria-hidden="true"></i>
            </a>
            <a href="{{ route('admin.teachers.create') }}" class="admin-quick-action">
                <span class="admin-quick-action-icon"><i data-lucide="graduation-cap" aria-hidden="true"></i></span>
                <span><strong>Tambah Tenaga Kependidikan</strong><small>Kelola informasi tenaga sekolah</small></span>
                <i class="admin-quick-action-arrow" data-lucide="arrow-right" aria-hidden="true"></i>
            </a>
            @if (! auth('admin')->user()->isEditor())
                <a href="{{ route('admin.media.index') }}" class="admin-quick-action">
                    <span class="admin-quick-action-icon"><i data-lucide="hard-drive" aria-hidden="true"></i></span>
                    <span><strong>Kelola Media</strong><small>Atur aset media website</small></span>
                    <i class="admin-quick-action-arrow" data-lucide="arrow-right" aria-hidden="true"></i>
                </a>
            @endif
            @if (auth('admin')->user()->isSuperAdmin())
                <a href="{{ route('admin.admins.index') }}" class="admin-quick-action">
                    <span class="admin-quick-action-icon"><i data-lucide="users-round" aria-hidden="true"></i></span>
                    <span><strong>Kelola Pengguna</strong><small>Atur akun dan hak akses</small></span>
                    <i class="admin-quick-action-arrow" data-lucide="arrow-right" aria-hidden="true"></i>
                </a>
            @endif
        </div>
    </section>

    <div class="admin-dashboard-grid">
        <section class="admin-card admin-dashboard-card">
            <div class="admin-card-heading">
                <div>
                    <span class="admin-card-heading-icon"><i data-lucide="newspaper" aria-hidden="true"></i></span>
                    <div><h2>Berita Terbaru</h2><p>Konten berita terbaru yang tersimpan</p></div>
                </div>
                <a href="{{ route('admin.content.index', 'news') }}">Lihat semua <i data-lucide="arrow-up-right" aria-hidden="true"></i></a>
            </div>
            @forelse ($latestNews as $article)
                <a href="{{ route('admin.content.edit', ['type' => 'news', 'entry' => $article]) }}" class="admin-recent-item">
                    <span class="admin-recent-date">{{ optional($article->published_at)->format('d M Y') ?? 'Belum terjadwal' }}</span>
                    <span class="admin-recent-title">{{ $article->title }}</span>
                    <span class="admin-status admin-status-{{ $article->status }}">{{ $article->status === 'published' ? 'Published' : 'Draft' }}</span>
                </a>
            @empty
                <div class="admin-empty-state">
                    <span class="admin-empty-icon"><i data-lucide="newspaper" aria-hidden="true"></i></span>
                    <strong>Belum ada berita</strong>
                    <p>Tambahkan berita pertama untuk ditampilkan di website.</p>
                    <a href="{{ route('admin.content.create', 'news') }}" class="admin-button admin-button-secondary">Tambah berita</a>
                </div>
            @endforelse
        </section>

        <section class="admin-card admin-dashboard-card admin-activity-card">
            <div class="admin-card-heading">
                <div>
                    <span class="admin-card-heading-icon"><i data-lucide="activity" aria-hidden="true"></i></span>
                    <div><h2>Aktivitas Terbaru</h2><p>Perubahan terakhir yang tercatat</p></div>
                </div>
            </div>
            <div class="admin-activity-table-wrap">
                <table class="admin-activity-table">
                    <thead>
                        <tr>
                            <th scope="col">Aktivitas</th>
                            <th scope="col">Administrator</th>
                            <th scope="col">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($activities as $activity)
                            @php
                                $activityAction = match ($activity->action) {
                                    'created' => 'Dibuat',
                                    'updated' => 'Diperbarui',
                                    'deleted' => 'Dihapus',
                                    default => ucfirst($activity->action),
                                };
                            @endphp
                            <tr>
                                <td>
                                    <div class="admin-activity-description">
                                        <span class="admin-event-badge admin-event-{{ $activity->action }}">{{ $activityAction }}</span>
                                        <strong>{{ $activity->title }}</strong>
                                        <small>{{ ucwords(str_replace('_', ' ', $activity->content_type)) }}</small>
                                    </div>
                                </td>
                                <td>{{ $activity->admin?->name ?? 'Admin dihapus' }}</td>
                                <td><time datetime="{{ $activity->created_at->toIso8601String() }}">{{ $activity->created_at->format('d M Y, H:i') }}</time></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3">
                                    <div class="admin-empty-state">
                                        <span class="admin-empty-icon"><i data-lucide="activity" aria-hidden="true"></i></span>
                                        <strong>Belum ada aktivitas</strong>
                                        <p>Aktivitas akan tercatat setelah konten dikelola.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
