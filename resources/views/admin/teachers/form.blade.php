@extends('admin.layout')

@php($isEditing = $entry->exists)
@section('title', ($isEditing ? 'Edit ' : 'Tambah ').$typeConfig['singular'])
@section('page-title', $isEditing ? 'Edit '.$typeConfig['singular'] : 'Tambah '.$typeConfig['singular'])
@section('breadcrumb', $typeConfig['plural'])

@section('content')
    <form
        method="POST"
        action="{{ $isEditing ? route('admin.teachers.update', $entry) : route('admin.teachers.store') }}"
        enctype="multipart/form-data"
        class="admin-form admin-card admin-content-form"
        data-disable-submit
    >
        @csrf
        @if ($isEditing)
            @method('PUT')
        @endif

        <div class="admin-form-heading">
            <div>
                <h2>{{ $isEditing ? 'Perbarui informasi tenaga kependidikan' : 'Informasi tenaga kependidikan baru' }}</h2>
                <p>Data disimpan di database terpisah (tenaga pendidik). Status Published akan tampil di website.</p>
            </div>
        </div>

        <div class="admin-form-grid">
            <div class="admin-field admin-field-full">
                <label for="title">Nama Lengkap & Gelar <span>*</span></label>
                <input id="title" name="title" type="text" value="{{ old('title', $entry->title) }}" placeholder="Contoh: Drs. H. Ahmad Fauzi, M.Pd" maxlength="180" required>
                <small>Nama lengkap guru atau tenaga pendidik beserta gelar akademik.</small>
                @error('title')<small class="admin-field-error">{{ $message }}</small>@enderror
            </div>

            <div class="admin-field">
                <label for="slug">Slug URL</label>
                <input id="slug" name="slug" type="text" value="{{ old('slug', $entry->slug) }}" maxlength="180">
                <small>Dibuat otomatis dari nama jika dikosongkan.</small>
                @error('slug')<small class="admin-field-error">{{ $message }}</small>@enderror
            </div>

            <div class="admin-field">
                <label for="metadata-position">Jabatan / Mata Pelajaran <span>*</span></label>
                <input id="metadata-position" name="metadata[position]" type="text" value="{{ old('metadata.position', data_get($entry->metadata, 'position', $entry->position ?? null)) }}" placeholder="Contoh: Guru Matematika / Kepala Lab" maxlength="255" required>
                <small>Mata pelajaran yang diampu atau jabatan di sekolah.</small>
                @error('metadata.position')<small class="admin-field-error">{{ $message }}</small>@enderror
            </div>

            <div class="admin-field">
                <label for="metadata-education">Pendidikan Terakhir (Opsional)</label>
                <input id="metadata-education" name="metadata[education]" type="text" value="{{ old('metadata.education', data_get($entry->metadata, 'education', $entry->education ?? null)) }}" placeholder="Contoh: S1 Pendidikan Matematika - UNJ" maxlength="255">
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
                <label for="focal-point-x">Posisi fokus foto</label>
                <div class="staff-photo-focus-controls" data-photo-focus-controls>
                    <label for="focal-point-x">Horizontal <output data-focus-output="x">{{ old('focal_point_x', $photoFocus['x']) }}%</output></label>
                    <input id="focal-point-x" name="focal_point_x" type="range" min="0" max="100" step="1" value="{{ old('focal_point_x', $photoFocus['x']) }}" data-focus-axis="x">
                    <label for="focal-point-y">Vertikal <output data-focus-output="y">{{ old('focal_point_y', $photoFocus['y']) }}%</output></label>
                    <input id="focal-point-y" name="focal_point_y" type="range" min="0" max="100" step="1" value="{{ old('focal_point_y', $photoFocus['y']) }}" data-focus-axis="y">
                </div>
                <small>Geser posisi fokus agar foto tampil proporsional pada kartu identitas. Foto asli tidak diubah.</small>
                @if ($entry->image_path && ! $photoFocus['available'])
                    <small>Foto lama ini belum terhubung ke Media Manager; pengaturan fokus akan aktif setelah foto diunggah atau dipilih dari Media Manager.</small>
                @endif
                @error('focal_point_x')<small class="admin-field-error">{{ $message }}</small>@enderror
                @error('focal_point_y')<small class="admin-field-error">{{ $message }}</small>@enderror
            </div>

            <div class="admin-field admin-field-full">
                <label for="summary">Keterangan / Sambutan Singkat (Opsional)</label>
                <textarea id="summary" name="summary" rows="3" placeholder="Contoh: Mengabdi sejak tahun 2016...">{{ old('summary', $entry->summary) }}</textarea>
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
                @error('sort_order')<small class="admin-field-error">{{ $message }}</small>@enderror
            </div>

            <div class="admin-field">
                <label for="published_at">Tanggal publikasi</label>
                <input id="published_at" name="published_at" type="datetime-local" value="{{ old('published_at', $entry->published_at?->format('Y-m-d\TH:i')) }}">
                @error('published_at')<small class="admin-field-error">{{ $message }}</small>@enderror
            </div>
        </div>

        <div class="admin-form-actions">
            <a href="{{ route('admin.teachers.index') }}" class="admin-button admin-button-secondary">Batal</a>
            <button type="submit" class="admin-button admin-button-primary">{{ $isEditing ? 'Simpan perubahan' : 'Simpan tenaga kependidikan' }}</button>
        </div>
    </form>

    <script>
        const titleInput = document.querySelector('#title');
        const slugInput = document.querySelector('#slug');
        const focusControls = document.querySelector('[data-photo-focus-controls]');

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

        if (focusControls) {
            const focusX = focusControls.querySelector('[data-focus-axis="x"]');
            const focusY = focusControls.querySelector('[data-focus-axis="y"]');
            const applyPhotoFocus = () => {
                const position = `${focusX.value}% ${focusY.value}%`;
                document.querySelectorAll('.admin-image-preview, #image-preview').forEach((image) => {
                    image.style.objectPosition = position;
                });
                focusControls.querySelector('[data-focus-output="x"]').textContent = `${focusX.value}%`;
                focusControls.querySelector('[data-focus-output="y"]').textContent = `${focusY.value}%`;
            };

            focusX.addEventListener('input', applyPhotoFocus);
            focusY.addEventListener('input', applyPhotoFocus);
            document.querySelectorAll('.admin-image-preview, #image-preview').forEach((image) => {
                image.addEventListener('load', applyPhotoFocus);
            });
            applyPhotoFocus();
        }

        document.querySelectorAll('form[data-disable-submit]').forEach((form) => {
            form.addEventListener('submit', () => {
                const button = form.querySelector('button[type="submit"]');
                if (button && !button.disabled) {
                    button.disabled = true;
                    button.dataset.originalText = button.textContent;
                    button.textContent = 'Menyimpan...';
                }
            });
        });
    </script>
@endsection
