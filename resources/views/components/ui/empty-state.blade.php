@props(['title', 'message', 'icon' => 'bi-inbox'])

<div {{ $attributes->merge(['class' => 'card border-0 bg-light']) }}>
    <div class="card-body text-center py-5">
        <div class="empty-state-icon mx-auto mb-3" aria-hidden="true">
            <i class="bi {{ $icon }}"></i>
        </div>
        <h2 class="h5">{{ $title }}</h2>
        <p class="text-muted mb-0">{{ $message }}</p>
        @if (trim((string) $slot) !== '')
            <div class="mt-4">{{ $slot }}</div>
        @endif
    </div>
</div>
