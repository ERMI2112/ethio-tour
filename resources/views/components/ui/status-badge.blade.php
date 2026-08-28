@props(['status'])

@php
    $map = [
        'pending' => ['Pending', 'bg-warning-subtle text-warning-emphasis border border-warning-subtle'],
        'accepted' => ['Accepted', 'bg-info-subtle text-info-emphasis border border-info-subtle'],
        'payment_pending' => ['Awaiting Payment', 'bg-primary-subtle text-primary border border-primary-subtle'],
        'confirmed' => ['Confirmed', 'bg-success-subtle text-success border border-success-subtle'],
        'cancelled' => ['Cancelled', 'bg-secondary-subtle text-secondary border border-secondary-subtle'],
        'rejected' => ['Rejected', 'bg-danger-subtle text-danger border border-danger-subtle'],
        'completed' => ['Completed', 'bg-success-subtle text-success border border-success-subtle'],
        'verified' => ['Verified', 'bg-success-subtle text-success border border-success-subtle'],
        'approved' => ['Approved', 'bg-success-subtle text-success border border-success-subtle'],
        'active' => ['Active', 'bg-success-subtle text-success border border-success-subtle'],
        'available' => ['Available', 'bg-success-subtle text-success border border-success-subtle'],
        'inactive' => ['Inactive', 'bg-secondary-subtle text-secondary border border-secondary-subtle'],
        'unavailable' => ['Unavailable', 'bg-secondary-subtle text-secondary border border-secondary-subtle'],
        'suspended' => ['Suspended', 'bg-danger-subtle text-danger border border-danger-subtle'],
        'expired' => ['Expired', 'bg-danger-subtle text-danger border border-danger-subtle'],
        'failed' => ['Failed', 'bg-danger-subtle text-danger border border-danger-subtle'],
        'success' => ['Successful', 'bg-success-subtle text-success border border-success-subtle'],
        'published' => ['Published', 'bg-success-subtle text-success border border-success-subtle'],
        'draft' => ['Draft', 'bg-light text-secondary border'],
    ];

    $status = (string) $status;
    $label = $map[$status][0] ?? ($status !== '' ? ucfirst(str_replace('_', ' ', $status)) : 'Not specified');
    $class = $map[$status][1] ?? 'bg-light text-secondary border';
@endphp

<span class="badge {{ $class }}" title="{{ $label }}" role="status" aria-label="Status: {{ $label }}">{{ $label }}</span>
