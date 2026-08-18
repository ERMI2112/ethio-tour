@props(['status'])

@php
    $map = [
        'pending' => ['Pending', 'bg-warning text-dark'],
        'accepted' => ['Accepted', 'bg-info text-dark'],
        'payment_pending' => ['Awaiting Payment', 'bg-primary text-white'],
        'confirmed' => ['Confirmed', 'bg-success text-white'],
        'cancelled' => ['Cancelled', 'bg-secondary text-white'],
        'rejected' => ['Rejected', 'bg-danger text-white'],
        'completed' => ['Completed', 'bg-success text-white'],
    ];

    $status = (string) $status;
    $label = $map[$status][0] ?? ucfirst(str_replace('_', ' ', $status));
    $class = $map[$status][1] ?? 'bg-light text-dark';
@endphp

<span class="badge {{ $class }}" title="{{ $label }}" role="status" aria-label="Status: {{ $label }}">{{ $label }}</span>
