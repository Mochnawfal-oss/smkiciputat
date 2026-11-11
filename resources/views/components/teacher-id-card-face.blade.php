@props([
    'teacher',
    'photoUrl',
    'loading' => 'lazy',
])

<div class="staff-id-object" aria-hidden="true">
    <span class="staff-id-strap"></span>
    <span class="staff-id-clip">
        <span class="staff-id-clip-pin"></span>
    </span>

    <div class="staff-id-card-face">
        <div
            class="staff-id-photo"
            style="--staff-focal-x: {{ $teacher->photo_focal_point_x ?? 50 }}%; --staff-focal-y: {{ $teacher->photo_focal_point_y ?? 24 }}%;"
        >
            @if ($teacher->image_path)
                <img
                    src="{{ $photoUrl }}"
                    alt=""
                    loading="{{ $loading }}"
                    onload="this.closest('.staff-id-photo').classList.toggle('is-landscape', this.naturalWidth > this.naturalHeight * 1.15)"
                    onerror="this.style.display='none';this.nextElementSibling?.classList.add('is-visible')"
                >
                <span class="staff-id-photo-placeholder" aria-hidden="true"></span>
            @else
                <span class="staff-id-photo-placeholder is-visible" aria-hidden="true"></span>
            @endif
        </div>

    </div>
</div>
