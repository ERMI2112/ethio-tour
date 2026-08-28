@extends('layouts.app')

@section('title', 'Guide Verification')

@section('content')
<div class="container-fluid py-4 py-lg-5">
    <div class="ws-page-header mb-4">
        <div>
            <span class="ws-eyebrow"><span class="ws-eye-dot" aria-hidden="true"></span>Regulatory operations</span>
            <h1 class="ws-title">Guide verification</h1>
            <p class="ws-lead">Review guide credentials, licenses, languages, and record official Bureau verification decisions.</p>
        </div>
    </div>

    <form class="card card-body border-0 shadow-sm mb-4" method="GET">
        <div class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label" for="guide-search">Search</label>
                <input id="guide-search" class="form-control" name="q" value="{{ $search }}" placeholder="Name, email, license, or expertise">
            </div>
            <div class="col-md-5">
                <label class="form-label" for="guide-status">Verification status</label>
                <select id="guide-status" class="form-select" name="status">
                    <option value="">All statuses</option>
                    @foreach(['pending','verified','rejected'] as $option)
                        <option value="{{ $option }}" @selected($status === $option)>{{ ucfirst($option) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100">Apply filters</button>
            </div>
        </div>
    </form>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Tour Guide</th>
                        <th>License</th>
                        <th>Destination</th>
                        <th>Languages &amp; Exp</th>
                        <th>Status</th>
                        <th>Updated</th>
                        <th><span class="visually-hidden">Action</span></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($guides as $guide)
                        @php($displayName = $guide->full_name ?: ($guide->user?->email ?? 'Tour Guide'))
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <img src="{{ $guide->profileImageUrl() }}"
                                         alt="{{ $displayName }}"
                                         class="rounded-circle border"
                                         style="width: 44px; height: 44px; object-fit: cover;"
                                         width="44" height="44" loading="lazy" decoding="async">
                                    <div>
                                        <div class="fw-bold text-dark">{{ $displayName }}</div>
                                        <div class="small text-muted">{{ $guide->user?->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $guide->license_number ?: 'Pending' }}</span>
                            </td>
                            <td>
                                <span class="small">{{ $guide->destination?->name ?? 'Regional' }}</span>
                            </td>
                            <td>
                                <div class="small fw-semibold">{{ $guide->years_of_experience ? $guide->years_of_experience . ' yrs exp' : 'N/A' }}</div>
                                <div class="small text-muted">{{ implode(', ', array_slice($guide->languagesList(), 0, 2)) }}</div>
                            </td>
                            <td>
                                <x-ui.status-badge :status="$guide->verification_status" />
                            </td>
                            <td class="text-nowrap text-muted small">
                                {{ $guide->updated_at?->format('Y-m-d') }}
                            </td>
                            <td>
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('bureau.guides.show', $guide) }}">
                                    Review
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                No guides match these filters. Pending submissions will appear here when available.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($guides->hasPages())
            <div class="p-3">
                {{ $guides->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
