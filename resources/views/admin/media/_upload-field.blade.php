<div
    class="{{ ($embedded ?? false) ? 'admin-managed-media-field' : 'admin-field admin-field-full admin-managed-media-field' }}"
    data-media-field
    data-media-category="{{ $category }}"
    data-media-upload-url="{{ route('admin.media.store') }}"
    data-media-upload-base="{{ route('admin.media.uploads.store') }}"
>
    @unless ($embedded ?? false)
        <label for="{{ $id }}">{{ $label }}</label>
    @endunless
    @if ($existingPath)
        <img class="admin-image-preview" src="{{ app(\App\Support\SiteConfiguration::class)->imageUrl($existingPath) }}" alt="Media saat ini">
    @endif
    <input
        id="{{ $id }}"
        name="{{ $inputName }}"
        type="file"
        accept="image/jpeg,image/png,image/webp,image/avif"
        data-media-managed
        data-media-source-name="{{ $sourceName }}"
        data-media-asset-name="{{ $assetInputName }}"
        data-media-preview="#{{ $id }}-preview"
        data-media-details="#{{ $id }}-details"
    >
    <input type="file" name="{{ $sourceName }}" hidden disabled data-media-source-for="{{ $id }}">
    <input type="hidden" name="{{ $assetInputName }}" value="" data-media-asset>
    <input type="hidden" name="media_quality" value="optimized" data-media-quality>
    <div class="admin-media-upload-preview" data-media-preview hidden>
        <img id="{{ $id }}-preview" alt="Pratinjau foto baru" hidden>
        <div id="{{ $id }}-details" class="admin-media-file-details"></div>
    </div>
    <div class="admin-media-progress" data-media-progress hidden>
        <div><span data-media-progress-status>Menyiapkan unggahan...</span><strong data-media-progress-value>0%</strong></div>
        <progress max="100" value="0" data-media-progress-bar></progress>
        <button type="button" class="admin-button admin-button-secondary" data-media-cancel>Batalkan</button>
    </div>
    <div class="admin-media-field-actions">
        <button type="button" class="admin-button admin-button-small admin-button-secondary" data-media-edit disabled>Edit foto</button>
        <span class="admin-media-status" role="status" data-media-status></span>
    </div>
    @unless ($embedded ?? false)
        <small>JPG, PNG, WebP, atau AVIF. Batas upload {{ config('media.max_upload_mb') }} MB; file asli tetap disimpan.</small>
        @error($errorKey)<small class="admin-field-error">{{ $message }}</small>@enderror
    @endunless
</div>
