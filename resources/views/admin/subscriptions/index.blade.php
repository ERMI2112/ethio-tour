@extends('layouts.app')

@section('title', 'Subscription Plans & Tiers · Administrator')

@section('content')
<div class="container-fluid py-4 py-lg-5 px-3 px-lg-5">
    {{-- Breadcrumbs & Header --}}
    <nav class="mb-3" aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-success text-decoration-none fw-semibold">Admin Dashboard</a></li>
            <li class="breadcrumb-item active text-muted" aria-current="page">Subscriptions &amp; Tiers</li>
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
            <h1 class="display-6 fw-bold mb-1 text-dark" style="font-family: var(--font-display); letter-spacing: -0.03em;">Subscription Plans</h1>
            <p class="text-secondary mb-0" style="font-size: 0.95rem;">
                Plan availability and commission rates are configuration only; payment settlement is deferred.
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-3 py-2 rounded-pill fw-semibold shadow-sm" style="font-size: 0.82rem;">
                <i class="bi bi-credit-card-2-front-fill me-1"></i> Commission &amp; Tier Governance
            </span>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4 d-flex align-items-center gap-2">
            <i class="bi bi-check-circle-fill fs-5"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Create Plan Card --}}
    <form method="POST" action="{{ route('admin.subscriptions.store') }}" class="card border-0 shadow-sm rounded-4 p-3.5 mb-4 bg-white">
        @csrf
        <h2 class="h6 fw-bold mb-3 text-dark" style="font-family: var(--font-display);">
            <i class="bi bi-plus-circle-fill text-success me-1.5"></i> Create New Subscription Plan
        </h2>
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted">Plan name</label>
                <input class="form-control rounded-3" name="plan" value="{{ old('plan') }}" placeholder="e.g. Premium Partner" required>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted">Price (ETB)</label>
                <input class="form-control rounded-3" name="price" type="number" min="0" step="0.01" value="{{ old('price') }}" placeholder="0.00">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted">Commission %</label>
                <input class="form-control rounded-3" name="commission_rate" type="number" min="0" max="100" step="0.01" value="{{ old('commission_rate') }}" placeholder="10.00" required>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted">Duration (days)</label>
                <input class="form-control rounded-3" name="duration" type="number" min="1" value="{{ old('duration') }}" placeholder="30" required>
            </div>
            <div class="col-md-3">
                <button class="btn btn-vn-emerald w-100 fw-bold shadow-sm rounded-3 py-2">
                    <i class="bi bi-plus-lg me-1"></i> Add plan
                </button>
            </div>
        </div>
    </form>

    {{-- Plan Lifecycle Table --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
        <div class="card-header bg-white p-3.5 border-bottom">
            <h2 class="h6 fw-bold mb-0 text-dark" style="font-family: var(--font-display);">
                <i class="bi bi-toggles text-primary me-1.5"></i> Plan Lifecycle &amp; Configuration
            </h2>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-uppercase small text-muted" style="font-size: 0.72rem; letter-spacing: 0.06em;">
                    <tr>
                        <th class="ps-4 py-3">Plan</th>
                        <th class="py-3">Price</th>
                        <th class="py-3">Commission</th>
                        <th class="py-3">Duration</th>
                        <th class="py-3">State</th>
                        <th class="py-3">Subscribers</th>
                        <th class="pe-4 py-3 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($plans as $plan)
                        @php($updateForm = 'plan-update-'.$plan->plan_id)
                        <tr>
                            <td class="ps-4 py-3">
                                <form id="{{ $updateForm }}" method="POST" action="{{ route('admin.subscriptions.update',$plan) }}">
                                    @csrf @method('PUT')
                                </form>
                                <label class="visually-hidden" for="{{ $updateForm }}-name">Plan name</label>
                                <input id="{{ $updateForm }}-name" form="{{ $updateForm }}" class="form-control form-control-sm rounded-3 fw-bold" name="plan" value="{{ $plan->plan }}">
                            </td>
                            <td class="py-3">
                                <label class="visually-hidden" for="{{ $updateForm }}-price">Price</label>
                                <input id="{{ $updateForm }}-price" form="{{ $updateForm }}" class="form-control form-control-sm rounded-3 font-monospace" name="price" type="number" min="0" step="0.01" value="{{ $plan->price }}" style="max-width: 100px;">
                            </td>
                            <td class="py-3">
                                <label class="visually-hidden" for="{{ $updateForm }}-commission">Commission</label>
                                <input id="{{ $updateForm }}-commission" form="{{ $updateForm }}" class="form-control form-control-sm rounded-3 font-monospace" name="commission_rate" type="number" min="0" max="100" step="0.01" value="{{ $plan->commission_rate }}" style="max-width: 90px;">
                            </td>
                            <td class="py-3">
                                <label class="visually-hidden" for="{{ $updateForm }}-duration">Duration</label>
                                <input id="{{ $updateForm }}-duration" form="{{ $updateForm }}" class="form-control form-control-sm rounded-3 font-monospace" name="duration" type="number" min="1" value="{{ $plan->duration }}" style="max-width: 90px;">
                            </td>
                            <td class="py-3">
                                <x-ui.status-badge :status="$plan->active ? 'active' : 'inactive'" />
                            </td>
                            <td class="py-3 font-monospace fw-bold">
                                {{ $plan->provider_subscriptions_count }}
                            </td>
                            <td class="pe-4 py-3 text-end">
                                <div class="d-inline-flex gap-2">
                                    <button form="{{ $updateForm }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold">
                                        Save
                                    </button>
                                    <form method="POST" action="{{ route('admin.subscriptions.status',$plan) }}">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="active" value="{{ $plan->active ? 0 : 1 }}">
                                        <button class="btn btn-sm {{ $plan->active ? 'btn-outline-warning' : 'btn-outline-success' }} rounded-pill px-3 fw-bold">
                                            {{ $plan->active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No plans configured.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Provider Subscriptions Table --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <div class="card-header bg-white p-3.5 border-bottom">
            <h2 class="h6 fw-bold mb-0 text-dark" style="font-family: var(--font-display);">
                <i class="bi bi-person-check-fill text-success me-1.5"></i> Provider Subscriptions
            </h2>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-uppercase small text-muted" style="font-size: 0.72rem; letter-spacing: 0.06em;">
                    <tr>
                        <th class="ps-4 py-3">Provider</th>
                        <th class="py-3">Plan</th>
                        <th class="py-3">State</th>
                        <th class="py-3">Start</th>
                        <th class="pe-4 py-3 text-end">End</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subscriptions as $subscription)
                        <tr>
                            <td class="ps-4 py-3 fw-bold text-dark">{{ $subscription->serviceProvider?->business_name ?: 'Unknown provider' }}</td>
                            <td class="py-3"><span class="badge bg-light text-dark border rounded-pill px-2.5 py-1">{{ $subscription->subscriptionPlan?->plan ?: 'Unknown plan' }}</span></td>
                            <td class="py-3"><x-ui.status-badge :status="$subscription->status" /></td>
                            <td class="py-3 text-muted small font-monospace">{{ $subscription->start_date?->format('Y-m-d') }}</td>
                            <td class="pe-4 py-3 text-muted small font-monospace text-end">{{ $subscription->end_date?->format('Y-m-d') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No provider subscriptions recorded.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
