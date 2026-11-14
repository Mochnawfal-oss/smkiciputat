@extends('admin.layout')

@php($isEditing = $entry->exists)
@section('title', ($isEditing ? 'Edit ' : 'Tambah ').$typeConfig['singular'])
@section('page-title', $isEditing ? 'Edit '.$typeConfig['singular'] : 'Tambah '.$typeConfig['singular'])
@section('breadcrumb', $typeConfig['plural'])

@section('content')
    <form
        method="POST"
        action="{{ $isEditing ? route('admin.content.update', ['type' => $type, 'entry' => $entry]) : route('admin.content.store', $type) }}"
        enctype="multipart/form-data"
        class="admin-form admin-card admin-content-form"
    >
        @csrf
        @if ($isEditing)
            @method('PUT')
        @endif

        <div class="admin-form-heading">
            <div>
                <h2>{{ $isEditing ? 'Perbarui informasi konten' : 'Informasi konten baru' }}</h2>
                <p>
                    @if ($typeConfig['type'] === 'teacher')
                        Lengkapi informasi tenaga pendidik. Data dengan status Published akan langsung tampil di website.
                    @elseif ($typeConfig['type'] === 'program')
                        Isi nama bidang, ringkasan beranda, foto program, deskripsi detail, kompetensi, fasilitas, dan peluang karier.
                    @else
                        Lengkapi informasi berikut. Gunakan status Published agar konten terlihat di website.
                    @endif
                </p>
            </div>
        </div>

        @if ($typeConfig['type'] === 'teacher')
            {{-- Form Khusus Tenaga Pendidik: Sederhana & Langsung Sesuai Web --}}
            <div class="admin-form-grid">
                <div class="admin-field admin-field-full">
                    <label for="title">Nama Lengkap & Gelar <span>*</span></label>
                    <input id="title" name="title" type="text" value="{{ old('title', $entry->title) }}" placeholder="Contoh: Drs. H. Ahmad Fauzi, M.Pd" maxlength="180" required>
                    <small>Nama lengkap guru atau tenaga pendidik beserta gelar akademik.</small>
                    @error('title')<small class="admin-field-error">{{ $message }}</small>@enderror
                </div>

                <div class="admin-field">
                    <label for="metadata-position">Jabatan / Mata Pelajaran <span>*</span></label>
                    <input id="metadata-position" name="metadata[position]" type="text" value="{{ old('metadata.position', data_get($entry->metadata, 'position')) }}" placeholder="Contoh: Guru Matematika / Kepala Lab" maxlength="255" required>
                    <small>Mata pelajaran yang diampu atau jabatan di sekolah.</small>
                    @error('metadata.position')<small class="admin-field-error">{{ $message }}</small>@enderror
                </div>

                <div class="admin-field">
                    <label for="metadata-education">Pendidikan Terakhir (Opsional)</label>
                    <input id="metadata-education" name="metadata[education]" type="text" value="{{ old('metadata.education', data_get($entry->metadata, 'education')) }}" placeholder="Contoh: S1 Pendidikan Matematika - UNJ" maxlength="255">
                    <small>Kualifikasi atau riwayat pendidikan.</small>
                    @error('metadata.education')<small class="admin-field-error">{{ $message }}</small>@enderror
                </div>

                @include('admin.media._upload-field', [
                    'id' => 'image',
                    'inputName' => 'image',
                    'sourceName' => 'source_image',
                    'assetInputName' => 'media_asset_id',
                    'category' => 'teacher',
                    'existingPath' => $entry->image_path,
                    'label' => 'Foto Guru / Tenaga Pendidik',
                    'errorKey' => 'image',
                ])

                <div class="admin-field admin-field-full">
                    <label for="summary">Keterangan / Sambutan Singkat (Opsional)</label>
                    <textarea id="summary" name="summary" rows="3" placeholder="Contoh: Mengabdi sejak tahun 2016, berkomitmen mendampingi siswa berprestasi...">{{ old('summary', $entry->summary) }}</textarea>
                    <small>Tampil saat pengunjung melihat profil atau detail guru.</small>
                    @error('summary')<small class="admin-field-error">{{ $message }}</small>@enderror
                </div>

                <div class="admin-field">
                    <label for="status">Status Tampil di Web <span>*</span></label>
                    <select id="status" name="status" required>
                        <option value="published" @selected(old('status', $entry->status ?? 'published') === 'published')>Langsung Tampil di Web (Published)</option>
                        <option value="draft" @selected(old('status', $entry->status) === 'draft')>Simpan Sementara (Draft)</option>
                    </select>
                    @error('status')<small class="admin-field-error">{{ $message }}</small>@enderror
                </div>

                <div class="admin-field">
                    <label for="sort_order">Urutan Tampil (Opsional)</label>
                    <input id="sort_order" name="sort_order" type="number" value="{{ old('sort_order', $entry->sort_order ?? 0) }}" placeholder="0">
                    <small>Nomor urutan (angka lebih kecil muncul lebih awal).</small>
                    @error('sort_order')<small class="admin-field-error">{{ $message }}</small>@enderror
                </div>
            </div>
        @elseif ($typeConfig['type'] === 'program')
            <div class="admin-form-grid">
                <div class="admin-field admin-field-full">
                    <label for="title">{{ $typeConfig['title_label'] }} <span>*</span></label>
                    <input id="title" name="title" type="text" value="{{ old('title', $entry->title) }}" maxlength="180" required>
                    @error('title')<small class="admin-field-error">{{ $message }}</small>@enderror
                </div>

                @include('admin.media._upload-field', [
                    'id' => 'image',
                    'inputName' => 'image',
                    'sourceName' => 'source_image',
                    'assetInputName' => 'media_asset_id',
                    'category' => 'program',
                    'existingPath' => $entry->image_path,
                    'label' => 'Foto Program Keahlian',
                    'errorKey' => 'image',
                ])

                <div class="admin-field admin-field-full">
                    <label for="summary">{{ $typeConfig['summary_label'] }}</label>
                    <textarea id="summary" name="summary" rows="3" maxlength="80">{{ old('summary', $entry->summary) }}</textarea>
                    <small>Maksimal 80 karakter. Teks singkat ini hanya ditampilkan pada kartu program di beranda.</small>
                    @error('summary')<small class="admin-field-error">{{ $message }}</small>@enderror
                </div>

                <div class="admin-field admin-field-full admin-form-section-title">
                    <h3>Konten Halaman Detail</h3>
                    <small>Isi deskripsi panjang untuk bagian Tentang Program di halaman detail. Ringkasan di atas hanya tampil pada kartu program di beranda.</small>
                </div>

                <div class="admin-field admin-field-full">
                    <label for="body">{{ $typeConfig['body_label'] }}</label>
                    <textarea id="body" name="body" rows="10" maxlength="50000">{{ old('body', $entry->body) }}</textarea>
                    <small>Foto utama yang sama digunakan di beranda dan hero halaman detail. Foto bagian Tentang Program dikelola melalui Galeri yang dikaitkan ke program.</small>
                    @error('body')<small class="admin-field-error">{{ $message }}</small>@enderror
                </div>

                <div class="admin-field admin-field-full admin-form-section-title">
                    <h3>Informasi Program</h3>
                    <small>Data berikut mengisi kartu informasi dan daftar kompetensi serta fasilitas pada halaman detail.</small>
                </div>

                <div class="admin-field">
                    <label for="metadata-primary-competence">{{ $typeConfig['fields']['primary_competence']['label'] }}</label>
                    <input id="metadata-primary-competence" name="metadata[primary_competence]" type="text" value="{{ old('metadata.primary_competence', data_get($entry->metadata, 'primary_competence')) }}" maxlength="255">
                    @error('metadata.primary_competence')<small class="admin-field-error">{{ $message }}</small>@enderror
                </div>

                <div class="admin-field">
                    <label for="metadata-career-opportunities">{{ $typeConfig['fields']['career_opportunities']['label'] }}</label>
                    <input id="metadata-career-opportunities" name="metadata[career_opportunities]" type="text" value="{{ old('metadata.career_opportunities', data_get($entry->metadata, 'career_opportunities')) }}" maxlength="255">
                    @error('metadata.career_opportunities')<small class="admin-field-error">{{ $message }}</small>@enderror
                </div>

                <div class="admin-field admin-field-full">
                    <label for="metadata-facilities">{{ $typeConfig['fields']['facilities']['label'] }}</label>
                    <textarea id="metadata-facilities" name="metadata[facilities]" rows="4" maxlength="10000">{{ old('metadata.facilities', data_get($entry->metadata, 'facilities')) }}</textarea>
                    <small>Tulis satu fasilitas per baris. Data ini tampil pada kartu Fasilitas Pendukung dan daftar di bagian Tentang Program.</small>
                    @error('metadata.facilities')<small class="admin-field-error">{{ $message }}</small>@enderror
                </div>

                <div class="admin-field admin-field-full">
                    <label for="metadata-advantages">{{ $typeConfig['fields']['advantages']['label'] }}</label>
                    <textarea id="metadata-advantages" name="metadata[advantages]" rows="4" maxlength="10000">{{ old('metadata.advantages', data_get($entry->metadata, 'advantages')) }}</textarea>
                    <small>Tulis satu kompetensi per baris. Kompetensi pertama juga tampil pada kartu informasi.</small>
                    @error('metadata.advantages')<small class="admin-field-error">{{ $message }}</small>@enderror
                </div>
            </div>
        @else
            {{-- Form Konten Standar (Berita, Lowongan, Galeri) --}}
            <div class="admin-form-grid">
                <div class="admin-field admin-field-full">
                    <label for="title">{{ $typeConfig['title_label'] ?? 'Judul / nama' }} <span>*</span></label>
                    <input id="title" name="title" type="text" value="{{ old('title', $entry->title) }}" maxlength="180" required>
                    @error('title')<small class="admin-field-error">{{ $message }}</small>@enderror
                </div>

                @if ($typeConfig['type'] !== 'principal_welcome')
                    <div class="admin-field">
                        <label for="slug">Slug URL</label>
                        <input id="slug" name="slug" type="text" value="{{ old('slug', $entry->slug) }}" maxlength="180">
                        <small>Dibuat otomatis dari judul jika dikosongkan.</small>
                        @error('slug')<small class="admin-field-error">{{ $message }}</small>@enderror
                    </div>
                @endif

                <div class="admin-field">
                    <label for="category">{{ $typeConfig['category_label'] ?? 'Kategori' }}</label>
                    <input id="category" name="category" type="text" value="{{ old('category', $entry->category) }}" maxlength="100">
                    @error('category')<small class="admin-field-error">{{ $message }}</small>@enderror
                </div>

                <div class="admin-field admin-field-full">
                    <label for="summary">{{ $typeConfig['summary_label'] ?? 'Ringkasan' }}</label>
                    <textarea id="summary" name="summary" rows="3" maxlength="600">{{ old('summary', $entry->summary) }}</textarea>
                    @error('summary')<small class="admin-field-error">{{ $message }}</small>@enderror
                </div>

                <div class="admin-field admin-field-full">
                    <label for="body">{{ $typeConfig['body_label'] ?? 'Isi lengkap' }}</label>
                    <textarea id="body" name="body" rows="9">{{ old('body', $entry->body) }}</textarea>
                    <small>Gunakan teks biasa. Baris baru akan dipertahankan pada website.</small>
                    @error('body')<small class="admin-field-error">{{ $message }}</small>@enderror
                </div>

                @foreach ($typeConfig['fields'] as $name => $field)
                    @php($fieldValue = $name === 'route_key' ? $entry->route_key : data_get($entry->metadata, $name))
                    @if ($typeConfig['type'] === 'news' && $name === 'homepage_group' && ! in_array($fieldValue, array_keys($field['options']), true))
                        @php($fieldValue = 'auto')
                    @endif
                    <div class="admin-field {{ $field['type'] === 'textarea' ? 'admin-field-full' : '' }}">
                        <label for="metadata-{{ $name }}">{{ $field['label'] }} @if ($field['required'])<span>*</span>@endif</label>
                        @if ($field['type'] === 'textarea')
                            <textarea id="metadata-{{ $name }}" name="metadata[{{ $name }}]" rows="5" @required($field['required'])>{{ old('metadata.'.$name, $fieldValue) }}</textarea>
                        @elseif ($field['type'] === 'select')
                            <select id="metadata-{{ $name }}" name="metadata[{{ $name }}]" @required($field['required'])>
                                <option value="">{{ $typeConfig['type'] === 'news' ? 'Pilih posisi' : 'Pilih tampilan' }}</option>
                                @foreach ($field['options'] as $option => $label)
                                    <option value="{{ $option }}" @selected(old('metadata.'.$name, $fieldValue) === $option)>{{ $label }}</option>
                                @endforeach
                            </select>
                        @else
                            <input id="metadata-{{ $name }}" name="metadata[{{ $name }}]" type="{{ $field['type'] }}" value="{{ old('metadata.'.$name, $fieldValue) }}" @required($field['required'])>
                        @endif
                        @if (! empty($field['help']))
                            <small>{{ $field['help'] }}</small>
                        @endif
                        @error('metadata.'.$name)<small class="admin-field-error">{{ $message }}</small>@enderror
                    </div>
                @endforeach

                @include('admin.media._upload-field', [
                    'id' => 'image',
                    'inputName' => 'image',
                    'sourceName' => 'source_image',
                    'assetInputName' => 'media_asset_id',
                    'category' => match ($typeConfig['type']) {
                        'program' => 'program',
                        'news' => 'news',
                        'gallery' => 'gallery',
                        default => 'general',
                    },
                    'existingPath' => $entry->image_path,
                    'label' => 'Foto / gambar',
                    'errorKey' => 'image',
                ])

                <div class="admin-field">
                    <label for="status">Status publikasi</label>
                    <select id="status" name="status" required>
                        <option value="draft" @selected(old('status', $entry->status) === 'draft')>Draft</option>
                        <option value="published" @selected(old('status', $entry->status) === 'published')>Published</option>
                    </select>
                    @error('status')<small class="admin-field-error">{{ $message }}</small>@enderror
                </div>

                @if ($typeConfig['type'] !== 'principal_welcome')
                    <div class="admin-field">
                        <label for="sort_order">Urutan tampil</label>
                        <input id="sort_order" name="sort_order" type="number" value="{{ old('sort_order', $entry->sort_order ?? 0) }}">
                        @error('sort_order')<small class="admin-field-error">{{ $message }}</small>@enderror
                    </div>

                    <div class="admin-field">
                        <label for="published_at">Tanggal publikasi</label>
                        <input id="published_at" name="published_at" type="datetime-local" min="1970-01-02T00:00" max="2038-01-18T23:59" value="{{ old('published_at', $entry->published_at?->format('Y-m-d\TH:i')) }}">
                        @error('published_at')<small class="admin-field-error">{{ $message }}</small>@enderror
                    </div>

                    @if ($typeConfig['type'] !== 'news')
                        <label class="admin-checkbox-label admin-field">
                            <input type="hidden" name="featured" value="0">
                            <input type="checkbox" name="featured" value="1" @checked(old('featured', $entry->featured))>
                            <span>Tandai sebagai unggulan</span>
                        </label>
                    @endif

                    <div class="admin-field admin-field-full admin-form-section-title"><h3>Pengaturan SEO (opsional)</h3></div>
                    <div class="admin-field">
                        <label for="meta_title">SEO title</label>
                        <input id="meta_title" name="meta_title" type="text" value="{{ old('meta_title', $entry->meta_title) }}" maxlength="180">
                    </div>
                    <div class="admin-field">
                        <label for="meta_description">Meta description</label>
                        <textarea id="meta_description" name="meta_description" rows="3" maxlength="320">{{ old('meta_description', $entry->meta_description) }}</textarea>
                    </div>
                @endif
            </div>
        @endif

        <div class="admin-form-actions">
            <a href="{{ route('admin.content.index', $type) }}" class="admin-button admin-button-secondary">Batal</a>
            <button type="submit" class="admin-button admin-button-primary">{{ $isEditing ? 'Simpan perubahan' : 'Simpan konten' }}</button>
        </div>
    </form>

    <script>
        const titleInput = document.querySelector('#title');
        const slugInput = document.querySelector('#slug');

        if (titleInput && slugInput && !slugInput.value) {
            titleInput.addEventListener('input', () => {
                slugInput.value = titleInput.value
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '')
                    .toLowerCase()
                    .trim()
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/^-|-$/g, '');
            });
        }
    </script>
@endsection
