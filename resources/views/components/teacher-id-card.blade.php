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
        aria-label="Lihat profil {{ $teacher->name }}"
    >
        @include('components.teacher-id-card-face', [
            'teacher' => $teacher,
            'photoUrl' => $photoUrl,
            'loading' => $featured ? 'eager' : 'lazy',
        ])
        @if (in_array($context, ['directory', 'home'], true))
            <span class="educator-card-caption">
                <strong class="educator-card-name">{{ $teacher->name }}</strong>
                @if ($teacher->position || $teacher->category)
                    <span class="educator-card-position">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M20 21v-1.8a3.7 3.7 0 0 0-3.7-3.7H7.7A3.7 3.7 0 0 0 4 19.2V21M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        {{ $teacher->position ?: $teacher->category }}
                    </span>
                @endif
            </span>
        @endif
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
