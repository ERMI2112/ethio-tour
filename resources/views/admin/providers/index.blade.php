@extends('layouts.app')

@section('title', 'Provider Governance · Administrator Workspace')

@section('content')
<div class="container-fluid py-4 py-lg-5 px-3 px-lg-5">
    {{-- Breadcrumbs & Header --}}
    <nav class="mb-3" aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-success text-decoration-none fw-semibold">Admin Dashboard</a></li>
            <li class="breadcrumb-item active text-muted" aria-current="page">Provider Governance</li>
        </ol>
    </nav>

    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-0.5" style="font-size: 0.72rem; font-weight: 700; letter-spacing: 0.05em;">
                    <span class="spinner-grow spinner-grow-sm me-1" style="width: 6px; height: 6px;" role="status"></span>
                    ADMINISTRATOR WORKSPACE
                </span>
            </div>
            <h1 class="display-6 fw-bold mb-1 text-dark" style="font-family: var(--font-display); letter-spacing: -0.03em;">Provider governance</h1>
            <p class="text-secondary mb-0" style="font-size: 0.95rem;">
                Review the Bureau gate and manage the platform activation state separately.
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle px-3 py-2 rounded-pill fw-semibold shadow-sm" style="font-size: 0.82rem;">
                <i class="bi bi-shield-lock-fill me-1"></i> Bureau verification &ne; platform activation
            </span>
        </div>
    </div>

    {{-- Segmented Queue Filter Bar (Apple / Vercel Executive Style) --}}
    <div class="card border-0 shadow-sm rounded-4 p-2 mb-4 bg-white">
        <div class="d-flex flex-wrap gap-1.5 align-items-center" aria-label="Provider queues">
            <a class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5 {{ $verification === 'verified' && $status === 'pending' ? 'btn-primary shadow-sm' : 'btn-light text-dark' }}" href="{{ route('admin.providers.index', ['verification'=>'verified','status'=>'pending']) }}">
                <i class="bi bi-inbox-fill"></i>
                <span>Activation queue</span>
            </a>
            <a class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5 {{ $status === 'approved' ? 'btn-success shadow-sm' : 'btn-light text-dark' }}" href="{{ route('admin.providers.index', ['status'=>'approved']) }}">
                <i class="bi bi-check-circle-fill"></i>
                <span>Approved</span>
            </a>
            <a class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5 {{ $status === 'suspended' ? 'btn-warning shadow-sm' : 'btn-light text-dark' }}" href="{{ route('admin.providers.index', ['status'=>'suspended']) }}">
                <i class="bi bi-pause-circle-fill"></i>
                <span>Suspended</span>
            </a>
            <a class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5 {{ $status === 'rejected' ? 'btn-danger shadow-sm' : 'btn-light text-dark' }}" href="{{ route('admin.providers.index', ['status'=>'rejected']) }}">
                <i class="bi bi-x-circle-fill"></i>
                <span>Rejected</span>
            </a>
            <a class="btn btn-sm rounded-pill px-3 py-1.5 fw-semibold btn-light text-secondary ms-auto" href="{{ route('admin.providers.index') }}">
                <i class="bi bi-grid"></i>
                <span>All providers</span>
            </a>
        </div>
    </div>

    {{-- Filter Bar --}}
    <form class="card border-0 shadow-sm rounded-4 p-3.5 mb-4 bg-white" method="GET">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-bold text-muted" for="provider-search">
                    <i class="bi bi-search me-1"></i> Search Business
                </label>
                <input id="provider-search" class="form-control rounded-3" name="q" value="{{ $search }}" placeholder="Business name or email">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted" for="provider-type">
                    <i class="bi bi-building me-1"></i> Provider type
                </label>
                <select id="provider-type" class="form-select rounded-3" name="type">
                    <option value="">All types</option>
                    @foreach(['hotel','restaurant','transportation_car_rental','event_organizer'] as $option)
                        <option value="{{ $option }}" @selected($type === $option)>{{ str_replace('_',' ',ucfirst($option)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted" for="bureau-state">
                    <i class="bi bi-shield-check me-1"></i> Bureau state
                </label>
                <select id="bureau-state" class="form-select rounded-3" name="verification">
                    <option value="">All states</option>
                    @foreach(['pending','verified','rejected'] as $option)
                        <option value="{{ $option }}" @selected($verification === $option)>{{ ucfirst($option) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted" for="platform-state">
                    <i class="bi bi-toggle-on me-1"></i> Platform state
                </label>
                <select id="platform-state" class="form-select rounded-3" name="status">
                    <option value="">All states</option>
                    @foreach(['pending','approved','suspended','rejected'] as $option)
                        <option value="{{ $option }}" @selected($status === $option)>{{ ucfirst($option) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-vn-navy w-100 fw-bold shadow-sm rounded-3 py-2" type="submit">
                    Apply filters
                </button>
            </div>
        </div>
    </form>

    {{-- Executive Data Grid Table --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <div class="card-header bg-white p-3.5 border-bottom d-flex justify-content-between align-items-center">
            <h2 class="h6 fw-bold mb-0 text-dark" style="font-family: var(--font-display);">
                <i class="bi bi-list-task text-success me-1.5"></i> Registered Providers &amp; Operators
            </h2>
            <span class="badge bg-light text-secondary border px-2.5 py-1 rounded-pill small">
                Total: {{ $providers->total() }}
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <caption class="visually-hidden">Provider governance records</caption>
                <thead class="table-light text-uppercase small text-muted" style="font-size: 0.72rem; letter-spacing: 0.06em;">
                    <tr>
                        <th class="ps-4 py-3">Business</th>
                        <th class="py-3">Type</th>
                        <th class="py-3">Bureau verification</th>
                        <th class="py-3">Platform status</th>
                        <th class="py-3">Commercial plan</th>
                        <th class="py-3">Account</th>
                        <th class="py-3">Registered</th>
                        <th class="pe-4 py-3 text-end"><span class="visually-hidden">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($providers as $provider)
                        @php
                            $icon = match($provider->provider_type) {
                                'hotel' => 'bi-building text-primary',
                                'restaurant' => 'bi-cup-hot-fill text-warning',
                                'transportation_car_rental' => 'bi-truck-flatbed text-info',
                                'event_organizer' => 'bi-ticket-perforated-fill text-danger',
                                default => 'bi-shop text-success'
                            };
                            $plan = $provider->providerSubscriptions->where('status', 'active')->first()?->subscriptionPlan;
                        @endphp
                        <tr>
                            <td class="ps-4 py-3.5">
                                <div class="d-flex align-items-center gap-2.5">
                                    <div class="d-flex align-items-center justify-content-center bg-light border rounded-3" style="width: 38px; height: 38px;">
                                        <i class="bi {{ $icon }} fs-5"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark fs-6">{{ $provider->business_name }}</div>
                                        <div class="small text-muted">{{ $provider->user?->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5">
                                <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1 font-monospace" style="font-size: 0.75rem;">
                                    {{ str_replace('_',' ',ucfirst($provider->provider_type)) }}
                                </span>
                            </td>
                            <td class="py-3.5">
                                <x-ui.status-badge :status="$provider->verification_status" />
                            </td>
                            <td class="py-3.5">
                                <x-ui.status-badge :status="$provider->status" />
                            </td>
                            <td class="py-3.5">
                                @if($plan)
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1" style="font-size: 0.75rem;">
                                        {{ $plan->plan }} ({{ $plan->commission_rate }}%)
                                    </span>
                                @else
                                    <span class="text-muted small" style="font-size: 0.75rem;">Unassigned</span>
                                @endif
                            </td>
                            <td class="py-3.5">
                                @if($provider->user?->is_active)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1" style="font-size: 0.75rem;">
                                        ● Active
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1" style="font-size: 0.75rem;">
                                        ○ Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 text-nowrap text-secondary small font-monospace">
                                {{ $provider->created_at?->format('Y-m-d') }}
                            </td>
                            <td class="pe-4 py-3.5 text-end">
                                <a class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold" href="{{ route('admin.providers.show', $provider) }}">
                                    Review &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <div class="fs-1 mb-2">📋</div>
                                <div class="fw-bold fs-6">No providers match these filters.</div>
                                <div class="small">Try resetting your filters or adjusting your search term.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($providers->hasPages())
            <div class="p-3 border-top bg-light-subtle d-flex justify-content-end">
                {{ $providers->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
