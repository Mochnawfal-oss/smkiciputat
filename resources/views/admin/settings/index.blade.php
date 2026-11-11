@extends('admin.layout')

@section('title', 'Pengaturan Website')
@section('page-title', 'Pengaturan Website')
@section('breadcrumb', 'Pengaturan Website')

@section('content')
    <div class="admin-page-intro">
        <div>
            <h2>Atur tampilan dan informasi publik</h2>
            <p>Perubahan akan langsung digunakan oleh halaman website.</p>
        </div>
    </div>

    <div class="admin-settings-layout">
        <nav class="admin-settings-nav" aria-label="Kategori pengaturan">
            @foreach ($sections as $sectionKey => $sectionLabel)
                <a href="{{ route('admin.settings.index', ['section' => $sectionKey]) }}" class="{{ $section === $sectionKey ? 'is-active' : '' }}">{{ $sectionLabel }}</a>
            @endforeach
        </nav>

        <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="admin-card admin-form admin-settings-form">
            @csrf
            @method('PUT')
            <input type="hidden" name="section" value="{{ $section }}">

            <div class="admin-card-heading">
                <div><h2>{{ $sections[$section] }}</h2><p>Isi bagian yang ingin diubah, lalu simpan.</p></div>
            </div>

            <div class="admin-form-grid">
                @foreach ($fields as $key => $field)
                    @php
                        $fieldName = \Illuminate\Support\Str::after($key, $section.'.');
                        $inputName = 'settings['.$section.']['.str_replace('.', '][', $fieldName).']';
                        $value = old('settings.'.$key, $values[$key] ?? null);
                    @endphp
                    <div class="admin-field {{ in_array($field['type'], ['textarea', 'image'], true) ? 'admin-field-full' : '' }}">
                        @if ($field['type'] === 'checkbox')
                            <input type="hidden" name="{{ $inputName }}" value="0">
                            <label class="admin-checkbox-label">
                                <input type="checkbox" name="{{ $inputName }}" value="1" @checked((bool) $value)>
                                <span>{{ $field['label'] }}</span>
                            </label>
                        @else
                            <label for="setting-{{ str_replace('.', '-', $key) }}">{{ $field['label'] }}</label>
                            @if ($field['type'] === 'textarea')
                                <textarea id="setting-{{ str_replace('.', '-', $key) }}" name="{{ $inputName }}" rows="4">{{ $value }}</textarea>
                            @elseif ($field['type'] === 'image')
                                @php
                                    $category = in_array(\Illuminate\Support\Str::before($fieldName, '.'), ['hero', 'news', 'program', 'gallery', 'teacher'], true)
                                        ? \Illuminate\Support\Str::before($fieldName, '.')
                                        : 'general';
                                @endphp
                                @include('admin.media._upload-field', [
                                    'id' => 'setting-'.str_replace('.', '-', $key),
                                    'inputName' => 'uploads['.$section.']['.str_replace('.', '][', $fieldName).']',
                                    'sourceName' => 'source_uploads['.$section.']['.str_replace('.', '][', $fieldName).']',
                                    'assetInputName' => 'media_asset_ids['.$section.']['.str_replace('.', '][', $fieldName).']',
                                    'category' => $category,
                                    'existingPath' => $value,
                                    'label' => $field['label'],
                                    'errorKey' => 'uploads.'.$key,
                                    'embedded' => true,
                                ])
                                @if ($value)
                                    <label class="admin-checkbox-label admin-remove-image">
                                        <input type="checkbox" name="remove_images[{{ $section }}][{{ str_replace('.', '][', $fieldName) }}]" value="1">
                                        <span>Hapus gambar saat ini</span>
                                    </label>
                                @endif
                            @else
                                <input id="setting-{{ str_replace('.', '-', $key) }}" name="{{ $inputName }}" type="{{ in_array($field['type'], ['link', 'youtube'], true) ? 'text' : $field['type'] }}" value="{{ $value }}" @if ($field['type'] === 'link') inputmode="url" placeholder="/path atau https://..." @elseif ($field['type'] === 'youtube') placeholder="ID video YouTube 11 karakter" @endif>
                            @endif
                        @endif
                        @error('settings.'.$key)<small class="admin-field-error">{{ $message }}</small>@enderror
                        @error('uploads.'.$key)<small class="admin-field-error">{{ $message }}</small>@enderror
                    </div>
                @endforeach
            </div>

            <div class="admin-form-actions">
                <button type="submit" class="admin-button admin-button-primary">Simpan pengaturan</button>
            </div>
        </form>
    </div>
@endsection
