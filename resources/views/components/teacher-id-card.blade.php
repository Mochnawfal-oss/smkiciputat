@props([
    'teacher',
    'schoolName',
    'photoUrl',
    'featured' => false,
    'context' => 'directory',
])

@php
    $profileId = 'teacher-profile-'.$teacher->getKey();
    $isPlaceholderText = static fn (?string $value): bool => $value !== null
        && preg_match('/\b(?:data|jabatan)\s+contoh\b/i', $value) === 1;
    $profileName = $isPlaceholderText($teacher->name) ? 'Profil Pendidik' : $teacher->name;
    $profilePosition = $isPlaceholderText($teacher->position) ? null : $teacher->position;
    $profileCategory = $isPlaceholderText($teacher->category) ? null : $teacher->category;
    $profileSummary = $isPlaceholderText($teacher->summary) ? null : $teacher->summary;
    $profileEducation = $isPlaceholderText($teacher->education) ? null : $teacher->education;
@endphp

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
            <p class="staff-profile-school staff-profile-panel-school">{{ $schoolName }}</p>
        </div>

        <button type="button" class="teacher-card-close" aria-label="Tutup profil">×</button>

        <div class="staff-profile-copy">
            <p class="staff-profile-eyebrow">Profil Pendidik &amp; Kependidikan</p>
            <h2 id="{{ $profileId }}-name">{{ $profileName }}</h2>

            @if ($profilePosition || $profileCategory)
                <div class="staff-profile-tags">
                    @if ($profilePosition)
                        <span class="staff-profile-role">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M8 7V5.8A1.8 1.8 0 0 1 9.8 4h4.4A1.8 1.8 0 0 1 16 5.8V7M4 8.5h16v10A1.5 1.5 0 0 1 18.5 20h-13A1.5 1.5 0 0 1 4 18.5v-10ZM4 12h16M10 12v2h4v-2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            {{ $profilePosition }}
                        </span>
                    @endif

                    @if ($profileCategory)
                        <span class="staff-profile-category">{{ $profileCategory }}</span>
                    @endif
                </div>
            @endif

            @if ($profileSummary)
                <div class="staff-profile-summary">
                    <div class="staff-profile-section-heading">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M20 21v-1.8a3.7 3.7 0 0 0-3.7-3.7H7.7A3.7 3.7 0 0 0 4 19.2V21M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span>Profil Singkat</span>
                    </div>
                    <p>{{ $profileSummary }}</p>
                </div>
            @endif

            @if ($profileEducation)
                <div class="staff-profile-education">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="m3 9 9-4.5L21 9l-9 4.5L3 9Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                        <path d="M6.5 11v4.2c3 2.4 8 2.4 11 0V11M21 9v5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <div>
                        <span>Pendidikan</span>
                        <p>{{ $profileEducation }}</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</article>
