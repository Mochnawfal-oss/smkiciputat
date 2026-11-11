@props([
    'teacher',
    'schoolName',
    'photoUrl',
    'featured' => false,
    'context' => 'directory',
])

@php($profileId = 'teacher-profile-'.$teacher->getKey())

<article class="teacher-card educator-card staff-id-record staff-id-record--{{ $context }} {{ $featured ? 'staff-id-record--featured' : '' }}">
    <button
        class="teacher-card-trigger staff-id-trigger"
        type="button"
        aria-expanded="false"
        aria-controls="{{ $profileId }}"
        aria-label="Lihat kartu identitas {{ $teacher->name }}"
    >
        @include('components.teacher-id-card-face', [
            'teacher' => $teacher,
            'photoUrl' => $photoUrl,
            'loading' => $featured ? 'eager' : 'lazy',
        ])
    </button>

    <button
        class="staff-id-backdrop"
        type="button"
        aria-label="Tutup profil {{ $teacher->name }}"
        tabindex="-1"
    ></button>

    <div
        class="teacher-card-details staff-profile-dialog"
        id="{{ $profileId }}"
        role="dialog"
        aria-modal="true"
        aria-labelledby="{{ $profileId }}-name"
        aria-hidden="true"
        inert
    >
        <div class="staff-profile-card-panel" aria-hidden="true">
            @include('components.teacher-id-card-face', [
                'teacher' => $teacher,
                'photoUrl' => $photoUrl,
                'loading' => 'lazy',
            ])
        </div>

        <div class="staff-profile-copy">
            <button type="button" class="teacher-card-close" aria-label="Tutup profil">×</button>
            <p class="staff-profile-school">{{ $schoolName }}</p>
            <h2 id="{{ $profileId }}-name">{{ $teacher->name }}</h2>

            @if ($teacher->position)
                <p class="staff-profile-role">{{ $teacher->position }}</p>
            @elseif ($teacher->category)
                <p class="staff-profile-role">{{ $teacher->category }}</p>
            @endif

            @if ($teacher->summary)
                <p class="staff-profile-summary">{{ $teacher->summary }}</p>
            @endif

            @if ($teacher->education)
                <div class="staff-profile-education">
                    <span>Pendidikan</span>
                    <p>{{ $teacher->education }}</p>
                </div>
            @endif

            <div class="staff-profile-footer">
                <span>Tenaga Kependidikan</span>
                <span class="staff-profile-mark" aria-hidden="true"></span>
            </div>
        </div>
    </div>
</article>
