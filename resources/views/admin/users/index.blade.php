@extends('layouts.app')

@section('title', 'User Management & 360° Inspection · Administrator')

@section('content')
<div class="container-fluid py-4 py-lg-5 px-3 px-lg-5">
    {{-- Breadcrumbs & Header --}}
    <nav class="mb-3" aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-success text-decoration-none fw-semibold">Admin Dashboard</a></li>
            <li class="breadcrumb-item active text-muted" aria-current="page">Users &amp; 360° Inspection</li>
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
            <h1 class="display-6 fw-bold mb-1 text-dark" style="font-family: var(--font-display); letter-spacing: -0.03em;">Platform Users</h1>
            <p class="text-secondary mb-0" style="font-size: 0.95rem;">
                Inspect 360-degree user profiles, role assignments, operational activity, and account access across all 9 portals.
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-light text-dark border px-3 py-2 rounded-pill fw-semibold shadow-sm" style="font-size: 0.82rem;">
                <i class="bi bi-people-fill text-primary me-1"></i> Total Users: {{ $users->total() }}
            </span>
        </div>
    </div>

    <!-- Filter Form -->
    <form class="card border-0 shadow-sm rounded-4 p-3.5 mb-4 bg-white" method="GET" action="{{ route('admin.users.index') }}">
        <div class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label small fw-bold text-muted" for="user-search">
                    <i class="bi bi-search me-1"></i> Search by Email
                </label>
                <input id="user-search" class="form-control rounded-3" name="q" value="{{ $search }}" placeholder="name@example.com">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted" for="user-role">
                    <i class="bi bi-person-badge me-1"></i> Platform Role
                </label>
                <select id="user-role" class="form-select rounded-3" name="role">
                    <option value="">All roles</option>
                    @foreach(['tourist' => 'Tourist', 'tour_guide' => 'Tour Guide', 'service_provider' => 'Service Provider', 'tourism_bureau_officer' => 'Tourism Bureau Officer', 'administrator' => 'Administrator'] as $key => $label)
                        <option value="{{ $key }}" @selected($role === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted" for="user-status">
                    <i class="bi bi-toggle-on me-1"></i> Account State
                </label>
                <select id="user-status" class="form-select rounded-3" name="active">
                    <option value="">All accounts</option>
                    <option value="1" @selected($active === '1')>Active</option>
                    <option value="0" @selected($active === '0')>Inactive</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-vn-navy fw-bold flex-grow-1 rounded-3 py-2" type="submit">
                    Apply
                </button>
                <a class="btn btn-light border rounded-3 py-2 text-muted px-3" href="{{ route('admin.users.index') }}" title="Clear filters">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </div>
    </form>

    <!-- Users Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <div class="card-header bg-white p-3.5 border-bottom d-flex justify-content-between align-items-center">
            <h2 class="h6 fw-bold mb-0 text-dark" style="font-family: var(--font-display);">
                <i class="bi bi-people text-success me-1.5"></i> User Directory &amp; Access Controls
            </h2>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <caption class="visually-hidden">Platform users</caption>
                <thead class="table-light text-uppercase small text-muted" style="font-size: 0.72rem; letter-spacing: 0.06em;">
                    <tr>
                        <th class="ps-4 py-3">User / Name</th>
                        <th class="py-3">Platform Role</th>
                        <th class="py-3">Profile / Entity Details</th>
                        <th class="py-3">Account State</th>
                        <th class="py-3">Registered</th>
                        <th class="text-end pe-4 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        @php
                            $displayName = match($user->role) {
                                'tourist' => $user->tourist?->full_name,
                                'tour_guide' => $user->tourGuide?->full_name,
                                'service_provider' => $user->serviceProvider?->business_name,
                                'tourism_bureau_officer' => 'Bureau Officer #'.$user->tourismBureauOfficer?->officer_id,
                                'administrator' => 'System Administrator',
                                default => null,
                            };
                            $roleBadgeColor = match($user->role) {
                                'administrator' => 'bg-danger-subtle text-danger border border-danger-subtle',
                                'tourism_bureau_officer' => 'bg-info-subtle text-info-emphasis border border-info-subtle',
                                'tour_guide' => 'bg-success-subtle text-success border border-success-subtle',
                                'service_provider' => 'bg-primary-subtle text-primary border border-primary-subtle',
                                default => 'bg-light text-dark border',
                            };
                        @endphp
                        <tr>
                            <td class="ps-4 py-3.5">
                                <div class="d-flex align-items-center gap-2.5">
                                    <div class="bg-light border text-dark rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 38px; height: 38px; font-size: 0.85rem;">
                                        {{ strtoupper(substr($user->email, 0, 2)) }}
                                    </div>
                                    <div>
                                        <a href="{{ route('admin.users.show', $user) }}" class="fw-bold text-dark text-decoration-none">
                                            {{ $user->email }}
                                        </a>
                                        @if($displayName)
                                            <div class="small text-muted">{{ $displayName }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5">
                                <span class="badge {{ $roleBadgeColor }} rounded-pill px-2.5 py-1" style="font-size: 0.75rem;">
                                    {{ str_replace('_', ' ', ucfirst($user->role)) }}
                                </span>
                            </td>
                            <td class="py-3.5">
                                @if($user->role === 'tourist')
                                    <span class="small text-secondary">{{ $user->tourist?->nationality ?? 'Traveler' }}</span>
                                @elseif($user->role === 'tour_guide')
                                    <span class="small text-secondary">
                                        {{ $user->tourGuide?->destination?->name ?? 'Ethiopia' }} &bull; 
                                        <span class="badge bg-{{ $user->tourGuide?->verification_status === 'verified' ? 'success' : 'warning' }}-subtle text-{{ $user->tourGuide?->verification_status === 'verified' ? 'success' : 'dark' }} rounded-pill px-2 py-0.5" style="font-size: 0.7rem;">
                                            {{ ucfirst($user->tourGuide?->verification_status ?? 'pending') }}
                                        </span>
                                    </span>
                                @elseif($user->role === 'service_provider')
                                    <span class="small text-secondary">
                                        {{ str($user->serviceProvider?->provider_type ?? 'provider')->replace('_', ' ')->title() }} &bull; 
                                        <span class="badge bg-{{ $user->serviceProvider?->status === 'approved' ? 'success' : 'secondary' }}-subtle text-{{ $user->serviceProvider?->status === 'approved' ? 'success' : 'secondary' }} rounded-pill px-2 py-0.5" style="font-size: 0.7rem;">
                                            {{ ucfirst($user->serviceProvider?->status ?? 'pending') }}
                                        </span>
                                    </span>
                                @elseif($user->role === 'tourism_bureau_officer')
                                    <span class="small text-secondary">
                                        {{ $user->tourismBureauOfficer?->destinations->pluck('name')->join(', ') ?: 'National Bureau' }}
                                    </span>
                                @else
                                    <span class="small text-muted">Platform Admin</span>
                                @endif
                            </td>
                            <td class="py-3.5">
                                <x-ui.status-badge :status="$user->is_active ? 'active' : 'inactive'" />
                            </td>
                            <td class="text-nowrap small text-muted py-3.5 font-monospace">
                                {{ $user->created_at?->format('Y-m-d') }}
                            </td>
                            <td class="text-end pe-4 py-3.5">
                                <div class="d-inline-flex gap-2 align-items-center">
                                    <a class="btn btn-vn-emerald btn-sm rounded-pill px-3 fw-bold" href="{{ route('admin.users.show', $user) }}">
                                        View Details &rarr;
                                    </a>
                                    @if($user->user_id !== auth()->id())
                                        <form method="POST" action="{{ route('admin.users.toggle', $user) }}" onsubmit="return confirm('{{ $user->is_active ? 'Deactivate this user account?' : 'Activate this user account?' }}');">
                                            @csrf @method('PATCH')
                                            <button class="btn btn-sm btn-outline-{{ $user->is_active ? 'secondary' : 'success' }} rounded-pill px-3">
                                                {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                                            </button>
                                        </form>
                                    @else
                                        <span class="badge bg-light text-muted border rounded-pill px-2.5 py-1">Current Admin</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <div class="fs-1 mb-2">👥</div>
                                <div class="fw-bold fs-6">No users match the specified search or filter criteria.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3 border-top bg-light-subtle d-flex justify-content-end">
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection
