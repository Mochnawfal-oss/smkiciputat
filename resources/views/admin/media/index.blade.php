@extends('admin.layout')

@section('title', 'Media Manager')
@section('page-title', 'Media Manager')
@section('breadcrumb', 'Media Manager')

@section('content')
    <div class="admin-page-intro">
        <div>
            <h2>Kelola foto dan video website</h2>
            <p>File asli disimpan terpisah dari foto WebP yang digunakan website. Video tetap memakai file asli selama FFmpeg tidak tersedia.</p>
        </div>
    </div>

    <section class="admin-card admin-media-upload-card">
        <div class="admin-card-heading">
            <div>
                <h2>Unggah media</h2>
                <p>Batas saat ini {{ number_format(config('media.max_upload_mb') / 1024, 1, ',', '.') }} GB, dapat diatur melalui <code>MEDIA_MAX_UPLOAD_MB</code>.</p>
            </div>
        </div>
        <form action="{{ route('admin.media.store') }}" method="POST" enctype="multipart/form-data" data-media-upload-form data-media-upload-base="{{ route('admin.media.uploads.store') }}">
            @csrf
            <div class="admin-media-upload-fields">
                <label class="admin-field">
                    <span>Foto atau video</span>
                    <input id="media-file" type="file" name="file" accept="image/jpeg,image/png,image/webp,image/avif,video/mp4,video/webm,video/ogg" required data-media-file data-media-source-name="source_file">
                </label>
                <label class="admin-field">
                    <span>Kategori penggunaan</span>
                    <select name="category" required>
                        @foreach ($categories as $key => $categoryOption)
                            <option value="{{ $key }}">{{ $categoryOption['label'] }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="admin-field">
                    <span>Kualitas foto</span>
                    <select name="quality" required>
                        <option value="optimized">Optimized</option>
                        <option value="high_quality">High Quality</option>
                        <option value="original">Ukuran penuh</option>
                    </select>
                </label>
            </div>
            <div class="admin-media-upload-preview" data-media-preview hidden>
                <img alt="Pratinjau gambar" data-media-image-preview hidden>
                <video controls playsinline preload="metadata" data-media-video-preview hidden></video>
                <div class="admin-media-file-details" data-media-file-details></div>
            </div>
            <div class="admin-media-progress" data-media-progress hidden>
                <div><span data-media-progress-status>Menyiapkan unggahan...</span><strong data-media-progress-value>0%</strong></div>
                <progress max="100" value="0" data-media-progress-bar></progress>
                <button type="button" class="admin-button admin-button-secondary" data-media-cancel>Batalkan</button>
            </div>
            <input type="hidden" name="duration_seconds" data-media-duration>
            <input type="hidden" name="width" data-media-width>
            <input type="hidden" name="height" data-media-height>
            <div class="admin-form-actions">
                <button type="button" class="admin-button admin-button-secondary" data-media-edit disabled>Edit foto</button>
                <button type="submit" class="admin-button admin-button-primary" data-media-submit>Unggah ke Media Manager</button>
            </div>
            <p class="admin-media-status" role="status" data-media-status></p>
        </form>
    </section>

    <section class="admin-card admin-media-library">
        <form method="GET" action="{{ route('admin.media.index') }}" class="admin-search-form admin-media-filter-form">
            <label for="media-search" class="sr-only">Cari media</label>
            <input id="media-search" type="search" name="search" value="{{ $search }}" placeholder="Cari nama atau keterangan media">
            <select name="type" aria-label="Filter tipe media">
                <option value="">Semua tipe</option>
                <option value="image" @selected($type === 'image')>Foto</option>
                <option value="video" @selected($type === 'video')>Video</option>
            </select>
            <select name="category" aria-label="Filter kategori">
                <option value="">Semua kategori</option>
                @foreach ($categories as $key => $categoryOption)
                    <option value="{{ $key }}" @selected($category === $key)>{{ $categoryOption['label'] }}</option>
                @endforeach
            </select>
            <button class="admin-button admin-button-secondary" type="submit">Terapkan</button>
            @if ($search !== '' || $type !== '' || $category !== '')
                <a href="{{ route('admin.media.index') }}" class="admin-reset-search">Reset filter</a>
            @endif
        </form>

        <div class="admin-media-grid">
            @forelse ($assets as $asset)
                @php($assetUsages = $usages[$asset->id] ?? [])
                <article class="admin-media-card">
                    <div class="admin-media-preview-frame">
                        @if ($asset->media_type === 'video')
                            <video controls preload="metadata" poster="{{ $asset->poster_path ? app(\App\Support\SiteConfiguration::class)->imageUrl($asset->poster_path) : '' }}" data-media-video-card>
                                <source src="{{ app(\App\Support\SiteConfiguration::class)->imageUrl($asset->publicPath()) }}" type="{{ $asset->mime_type }}">
                            </video>
                            <button type="button" class="admin-media-capture-poster" data-media-capture-poster data-url="{{ route('admin.media.poster', $asset) }}">Jadikan frame ini thumbnail</button>
                        @else
                            <a href="{{ app(\App\Support\SiteConfiguration::class)->imageUrl($asset->original_path) }}" target="_blank" rel="noopener" aria-label="Lihat ukuran penuh: {{ $asset->filename }}">
                                <img src="{{ app(\App\Support\SiteConfiguration::class)->imageUrl($asset->publicPath()) }}" alt="{{ $asset->alt_text ?: $asset->filename }}" loading="lazy">
                            </a>
                        @endif
                    </div>
                    <div class="admin-media-card-body">
                        <h3 title="{{ $asset->filename }}">{{ $asset->filename }}</h3>
                        <dl>
                            <div><dt>Jenis</dt><dd>{{ $asset->media_type === 'image' ? 'Foto' : 'Video' }}</dd></div>
                            <div><dt>Ukuran</dt><dd>{{ \Illuminate\Support\Number::fileSize($asset->file_size) }}@if ($asset->optimized_size && $asset->optimized_size !== $asset->file_size) <span>(WebP {{ \Illuminate\Support\Number::fileSize($asset->optimized_size) }})</span>@endif</dd></div>
                            <div><dt>Resolusi</dt><dd>{{ $asset->width && $asset->height ? $asset->width.' × '.$asset->height : '—' }}@if ($asset->duration_seconds) <span>· {{ gmdate('H:i:s', $asset->duration_seconds) }}</span>@endif</dd></div>
                            <div><dt>Diunggah</dt><dd>{{ $asset->created_at->format('d M Y, H:i') }}</dd></div>
                        </dl>
                        <form
                            action="{{ route('admin.media.store') }}"
                            method="POST"
                            enctype="multipart/form-data"
                            class="admin-media-replace-form"
                            data-media-upload-form
                            data-media-upload-base="{{ route('admin.media.uploads.store') }}"
                        >
                            @csrf
                            <input type="hidden" name="replace_asset_id" value="{{ $asset->id }}">
                            <input type="hidden" name="category" value="{{ $asset->category }}">
                            <input type="hidden" name="quality" value="optimized">
                            <label class="admin-field">
                                <span>Ganti file</span>
                                <input
                                    id="replace-media-{{ $asset->id }}"
                                    type="file"
                                    name="file"
                                    accept="{{ $asset->media_type === 'image' ? 'image/jpeg,image/png,image/webp,image/avif' : 'video/mp4,video/webm,video/ogg' }}"
                                    data-media-file
                                    data-media-source-name="source_file"
                                    required
                                >
                            </label>
                            <div class="admin-media-upload-preview" data-media-preview hidden>
                                <img alt="Pratinjau gambar pengganti" data-media-image-preview hidden>
                                <video controls playsinline preload="metadata" data-media-video-preview hidden></video>
                                <div class="admin-media-file-details" data-media-file-details></div>
                            </div>
                            <input type="hidden" name="duration_seconds" data-media-duration>
                            <input type="hidden" name="width" data-media-width>
                            <input type="hidden" name="height" data-media-height>
                            <div class="admin-media-progress" data-media-progress hidden>
                                <div><span data-media-progress-status>Menyiapkan unggahan...</span><strong data-media-progress-value>0%</strong></div>
                                <progress max="100" value="0" data-media-progress-bar></progress>
                                <button type="button" class="admin-button admin-button-secondary" data-media-cancel>Batalkan</button>
                            </div>
                            <div class="admin-media-field-actions">
                                @if ($asset->media_type === 'image')
                                    <button type="button" class="admin-button admin-button-small admin-button-secondary" data-media-edit disabled>Edit foto</button>
                                @endif
                                <button type="submit" class="admin-button admin-button-small admin-button-secondary" data-media-submit>Ganti file</button>
                                <span class="admin-media-status" role="status" data-media-status></span>
                            </div>
                        </form>
                        <form method="POST" action="{{ route('admin.media.update', $asset) }}" class="admin-media-edit-form">
                            @csrf
                            @method('PUT')
                            <label class="admin-field">
                                <span>Kategori</span>
                                <select name="category">
                                    @foreach ($categories as $key => $categoryOption)
                                        <option value="{{ $key }}" @selected($asset->category === $key)>{{ $categoryOption['label'] }}</option>
                                    @endforeach
                                </select>
                            </label>
                            @if ($asset->media_type === 'image')
                                <label class="admin-field">
                                    <span>Teks alternatif</span>
                                    <input type="text" name="alt_text" value="{{ $asset->alt_text }}" maxlength="255">
                                </label>
                            @endif
                            <label class="admin-field">
                                <span>Keterangan</span>
                                <textarea name="caption" rows="2" maxlength="2000">{{ $asset->caption }}</textarea>
                            </label>
                            <button type="submit" class="admin-button admin-button-small admin-button-secondary">Simpan info</button>
                        </form>
                        <div class="admin-media-usage">
                            <strong>Penggunaan</strong>
                            @if ($assetUsages !== [])
                                <ul>
                                    @foreach ($assetUsages as $usage)
                                        <li>{{ $usage['type'] }} · {{ $usage['title'] }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <span>Belum dipakai konten website</span>
                            @endif
                        </div>
                        <form method="POST" action="{{ route('admin.media.destroy', $asset) }}" data-confirm="{{ $assetUsages !== [] ? 'Media yang digunakan konten tidak dapat dihapus.' : 'Hapus file media ini secara permanen?' }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="admin-button admin-button-small admin-button-danger" @disabled($assetUsages !== []) title="{{ $assetUsages !== [] ? 'Lepaskan semua referensi sebelum menghapus media.' : 'Hapus media' }}">Hapus media</button>
                        </form>
                    </div>
                </article>
            @empty
                <div class="admin-empty-state admin-media-empty">Belum ada media yang sesuai dengan filter.</div>
            @endforelse
        </div>

        <div class="admin-pagination">{{ $assets->links() }}</div>
    </section>

@endsection
