@props([
    'eyebrow',
    'title',
    'description' => null,
])

<header class="public-page-heading">
    <span class="public-page-heading__eyebrow">{{ $eyebrow }}</span>
    <h1>{{ $title }}</h1>
    @if (filled($description))
        <p>{{ $description }}</p>
    @endif
    {{ $slot }}
</header>
