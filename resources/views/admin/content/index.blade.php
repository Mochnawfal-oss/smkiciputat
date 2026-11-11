@extends('admin.layout')

@section('title', $typeConfig['plural'])
@section('page-title', $typeConfig['plural'])
@section('breadcrumb', $typeConfig['plural'])

@section('content')
    <div class="admin-page-intro">
        <div>
            <h2>Kelola {{ mb_strtolower($typeConfig['plural']) }}</h2>
            <p>Data yang berstatus published akan ditampilkan pada website publik.</p>
        </div>
        <a href="{{ route('admin.content.create', $type) }}" class="admin-button admin-button-primary">+ Tambah {{ $typeConfig['singular'] }}</a>
    </div>

    <section class="admin-card" data-bulk-root data-total-count="{{ $totalCount }}" data-category-label="{{ $typeConfig['plural'] }}">
        <form method="GET" action="{{ route('admin.content.index', $type) }}" class="admin-search-form">
            <label for="content-search" class="sr-only">Cari konten</label>
            <input id="content-search" type="search" name="search" value="{{ $search }}" placeholder="Cari judul, slug, atau kategori">
            <button class="admin-button admin-button-secondary" type="submit">Cari</button>
            @if ($search !== '')
                <a href="{{ route('admin.content.index', $type) }}" class="admin-reset-search">Hapus pencarian</a>
            @endif
        </form>

        <form method="POST" action="{{ route('admin.content.bulk-destroy', $type) }}" data-bulk-form>
            @csrf
            <input type="hidden" name="select_all" value="0">

            <div class="admin-bulk-bar">
                <div class="admin-bulk-summary"><strong><span data-bulk-count>0</span></strong> data dipilih (halaman ini: {{ $entries->count() }} / total kategori: {{ $totalCount }})</div>
                <div class="admin-bulk-actions">
                    <button type="button" class="admin-button admin-button-small admin-button-secondary" data-bulk-select-all-category>Pilih seluruh kategori ({{ $totalCount }})</button>
                    <button type="button" class="admin-button admin-button-small admin-button-secondary" data-bulk-clear>Batalkan Pilihan</button>
                    <button type="button" class="admin-button admin-button-small admin-button-danger" data-bulk-delete disabled>Hapus Terpilih</button>
                    <span class="admin-bulk-divider" aria-hidden="true"></span>
                    <button type="button" class="admin-button admin-button-small admin-button-danger" data-empty-open data-action="{{ route('admin.content.empty', $type) }}" data-category-label="{{ $typeConfig['plural'] }}" data-total-count="{{ $totalCount }}" data-confirm-word="{{ Str::upper($typeConfig['plural']) }}">Kosongkan Semua Data</button>
                </div>
            </div>

            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th class="admin-check-col"><input type="checkbox" data-bulk-select-page aria-label="Pilih semua di halaman"></th>
                            <th>Konten</th>
                            <th>Jenis / Kategori</th>
                            <th>Status</th>
                            <th>Diperbarui</th>
                            <th class="admin-table-actions">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($entries as $entry)
                            <tr>
                                <td class="admin-check-col"><input type="checkbox" name="ids[]" value="{{ $entry->id }}" data-bulk-item data-name="{{ e($entry->title) }}" aria-label="Pilih {{ $entry->title }}"></td>
                                <td>
                                    <div class="admin-table-title">
                                        @if ($entry->image_path)
                                            <img src="{{ app(\App\Support\SiteConfiguration::class)->imageUrl($entry->image_path) }}" alt="" loading="lazy">
                                        @endif
                                        <span><strong>{{ $entry->title }}</strong><small>{{ $entry->slug ?? '—' }}</small></span>
                                    </div>
                                </td>
                                <td>{{ $entry->category ?: $typeConfig['singular'] }}</td>
                                <td><span class="admin-status admin-status-{{ $entry->status }}">{{ $entry->status === 'published' ? 'Published' : 'Draft' }}</span></td>
                                <td>{{ $entry->updated_at->format('d M Y') }}</td>
                                <td class="admin-table-actions">
                                    <div class="admin-row-actions">
                                        <a class="admin-button admin-button-small admin-button-secondary" href="{{ route('admin.content.edit', ['type' => $type, 'entry' => $entry]) }}">Edit</a>
                                        <button type="submit" class="admin-button admin-button-small admin-button-danger" form="delete-content-{{ $type }}-{{ $entry->id }}">Hapus</button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6"><div class="admin-empty-state">Belum ada data {{ mb_strtolower($typeConfig['plural']) }}{{ $search !== '' ? ' yang cocok dengan pencarian.' : '.' }}</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </form>

        @foreach ($entries as $entry)
            <form id="delete-content-{{ $type }}-{{ $entry->id }}" method="POST" action="{{ route('admin.content.destroy', ['type' => $type, 'entry' => $entry]) }}" data-confirm="Hapus {{ e($entry->title) }} secara permanen? Data akan terhapus dari database dan tidak dapat dibatalkan." class="hidden">
                @csrf
                @method('DELETE')
            </form>
        @endforeach

        <div class="admin-pagination">{{ $entries->links() }}</div>
    </section>
@endsection
