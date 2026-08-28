@props([
    'label',
    'value' => null,
    'hint' => null,
    'icon' => null,
])

<div class="card border-0 shadow-sm rounded-4 h-100 p-3 stat-card">
    <div class="d-flex align-items-center justify-content-between gap-2">
        <span class="small text-muted text-uppercase fw-bold stat-card-label">{{ $label }}</span>
        @if ($icon)
            <span class="stat-card-icon d-inline-flex align-items-center justify-content-center rounded-3 flex-shrink-0" aria-hidden="true">
                <i class="bi bi-{{ $icon }}"></i>
            </span>
        @endif
    </div>
    @if ($value !== null)
        <strong class="h3 mt-2 mb-1 stat-card-value">{{ $value }}</strong>
    @else
        {{ $slot }}
    @endif
    @if ($hint)
        <span class="small text-muted stat-card-hint">{{ $hint }}</span>
    @endif
</div>
