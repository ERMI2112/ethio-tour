@props([
    'group',
    'section',
    'sectionRoute' => null,
    'current' => null,
])

<nav aria-label="breadcrumb">
    <ol class="breadcrumb small mb-3">
        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
        <li class="breadcrumb-item">{{ $group }}</li>
        @if ($sectionRoute)
            <li class="breadcrumb-item"><a href="{{ $sectionRoute }}">{{ $section }}</a></li>
        @else
            <li class="breadcrumb-item">{{ $section }}</li>
        @endif
        @if ($current)
            <li class="breadcrumb-item active" aria-current="page">{{ $current }}</li>
        @endif
    </ol>
</nav>
