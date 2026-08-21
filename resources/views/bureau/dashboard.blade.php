@extends('layouts.app')

@section('title', 'Bureau Dashboard')

@section('content')
<div class="container-fluid py-4 py-lg-5">
    <div class="ws-page-header mb-4">
        <div>
            <span class="ws-eyebrow"><span class="ws-eye-dot" aria-hidden="true"></span>Regulatory operations</span>
            <h1 class="ws-title">Tourism Bureau Workspace</h1>
            <p class="ws-lead">Review verification queues, maintain public heritage attractions, and audit accredited tourism services.</p>
        </div>
        <div class="ws-actions">
            <span class="badge text-bg-info px-3 py-2">Bureau Authority</span>
        </div>
    </div>

    @include('layouts.partials.flash-messages')

    <!-- Attention Queues -->
    <section aria-labelledby="attention-heading" class="mb-4">
        <h2 id="attention-heading" class="ws-section-title mb-3">Needs attention</h2>
        <div class="row g-3">
            <div class="col-md-6">
                <div class="card border-warning shadow-sm h-100 rounded-4">
                    <div class="card-body p-4">
                        <span class="badge text-bg-warning text-dark mb-2">Guide Queue</span>
                        <h3 class="h5 fw-bold">{{ $pendingGuides }} guide{{ $pendingGuides === 1 ? '' : 's' }} awaiting verification</h3>
                        <p class="small text-muted mb-3">Review guide credentials, licenses, and background before issuing accreditation.</p>
                        <a class="btn btn-warning fw-bold" href="{{ route('bureau.guides.index', ['status' => 'pending']) }}">
                            Review Guide Queue &rarr;
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-warning shadow-sm h-100 rounded-4">
                    <div class="card-body p-4">
                        <span class="badge text-bg-warning text-dark mb-2">Provider Queue</span>
                        <h3 class="h5 fw-bold">{{ $pendingProviders }} provider{{ $pendingProviders === 1 ? '' : 's' }} awaiting verification</h3>
                        <p class="small text-muted mb-3">Verify hotel, dining, and transportation provider regulatory standing.</p>
                        <a class="btn btn-warning fw-bold" href="{{ route('bureau.providers.index', ['status' => 'pending']) }}">
                            Review Provider Queue &rarr;
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Verification Summary Stats -->
    <section aria-labelledby="summary-heading" class="mb-4">
        <h2 id="summary-heading" class="ws-section-title mb-3">Verification summary</h2>
        <div class="row g-3">
            <div class="col-6 col-md-4 col-xl-2">
                <a class="card border-0 shadow-sm h-100 text-decoration-none rounded-4" href="{{ route('bureau.guides.index', ['status' => 'verified']) }}">
                    <div class="card-body">
                        <div class="ws-stat-label">Verified Guides</div>
                        <div class="ws-stat-value text-success">{{ $verifiedGuides }}</div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <a class="card border-0 shadow-sm h-100 text-decoration-none rounded-4" href="{{ route('bureau.providers.index', ['status' => 'verified']) }}">
                    <div class="card-body">
                        <div class="ws-stat-label">Verified Providers</div>
                        <div class="ws-stat-value text-success">{{ $approvedProviders }}</div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-4 col-xl-3">
                <a class="card border-0 shadow-sm h-100 text-decoration-none rounded-4" href="{{ route('bureau.attractions.index') }}">
                    <div class="card-body">
                        <div class="ws-stat-label">Heritage Sites / Attractions</div>
                        <div class="ws-stat-value text-success">{{ $attractionCount }}</div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <a class="card border-0 shadow-sm h-100 text-decoration-none rounded-4" href="{{ route('bureau.museums.index') }}">
                    <div class="card-body">
                        <div class="ws-stat-label">Museum Records</div>
                        <div class="ws-stat-value text-primary">{{ $museumCount }}</div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-4 col-xl-3">
                <a class="card border-0 shadow-sm h-100 text-decoration-none rounded-4" href="{{ route('bureau.reports.index') }}">
                    <div class="card-body">
                        <div class="ws-stat-label">Bureau Analytics</div>
                        <div class="ws-stat-value text-info">View Reports</div>
                    </div>
                </a>
            </div>
        </div>
    </section>

    <div class="row g-4">
        <!-- Recent Decisions -->
        <section class="col-xl-7" aria-labelledby="decisions-heading">
            <div class="card border-0 shadow-sm h-100 rounded-4">
                <div class="card-header bg-white p-4 border-bottom">
                    <h2 id="decisions-heading" class="h5 fw-bold mb-1">Recent decisions</h2>
                    <p class="small text-muted mb-0">Recorded accreditation actions from the central audit service.</p>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Action</th>
                                <th>Officer</th>
                                <th class="pe-4">When</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentDecisions as $decision)
                                <tr>
                                    <td class="ps-4 fw-semibold">{{ str_replace('_', ' ', ucfirst($decision->action)) }}</td>
                                    <td>{{ $decision->actor?->email ?? 'System' }}</td>
                                    <td class="pe-4 text-nowrap text-muted">{{ $decision->created_at?->diffForHumans() }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">No Bureau decisions have been recorded yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- Operations Hub -->
        <section class="col-xl-5" aria-labelledby="operations-heading">
            <div class="card border-0 shadow-sm h-100 rounded-4">
                <div class="card-header bg-white p-4 border-bottom">
                    <h2 id="operations-heading" class="h5 fw-bold mb-1">Museum / tourism operations</h2>
                    <p class="small text-muted mb-0">Manage published cultural landmarks and museum exhibits.</p>
                </div>
                <div class="card-body p-4 d-flex flex-column gap-3">
                    <div class="p-3 bg-light rounded-3 d-flex justify-content-between align-items-center">
                        <div>
                            <strong class="d-block text-dark">Heritage Sites &amp; Attractions</strong>
                            <span class="small text-muted">{{ $attractionCount }} attraction record{{ $attractionCount === 1 ? '' : 's' }} in catalog</span>
                        </div>
                        <a class="btn btn-outline-success btn-sm fw-semibold" href="{{ route('bureau.attractions.index') }}">
                            Manage &rarr;
                        </a>
                    </div>
                    <div class="p-3 bg-light rounded-3 d-flex justify-content-between align-items-center">
                        <div>
                            <strong class="d-block text-dark">Museum Information</strong>
                            <span class="small text-muted">{{ $museumCount }} museum record{{ $museumCount === 1 ? '' : 's' }}</span>
                        </div>
                        <a class="btn btn-outline-primary btn-sm fw-semibold" href="{{ route('bureau.museums.index') }}">
                            Manage &rarr;
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection
