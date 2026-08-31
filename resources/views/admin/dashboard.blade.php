@extends('layouts.app')

@section('title', 'Administrator Dashboard · Executive Operations')

@section('content')
<div class="container-fluid py-4 py-lg-5 px-3 px-lg-5">
    {{-- Executive Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-0.5 d-inline-flex align-items-center gap-1.5" style="font-size: 0.72rem; font-weight: 700; letter-spacing: 0.04em;">
                    <span class="spinner-grow spinner-grow-sm text-success" style="width: 5px; height: 5px;" role="status"></span>
                    <span>Platform operations</span>
                </span>
            </div>
            <h1 class="h3 fw-bold mb-1 text-dark" style="font-family: var(--font-display); letter-spacing: -0.02em;">Administrator Dashboard</h1>
            <p class="text-secondary mb-0 small">
                Review the work that needs attention, then monitor the platform from one console.
            </p>
        </div>
        <div>
            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 rounded-pill fw-semibold shadow-sm" style="font-size: 0.78rem;">
                <i class="bi bi-shield-check me-1"></i> Operational
            </span>
        </div>
    </div>

    {{-- Attention Section --}}
    <section aria-labelledby="attention-heading" class="mb-4">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <h2 id="attention-heading" class="h6 fw-bold mb-0 text-dark text-uppercase" style="letter-spacing: 0.05em; font-size: 0.78rem;">
                Attention
            </h2>
            <span class="text-muted small">Governance action items</span>
        </div>
        <p class="text-muted small mb-3">Actionable administrator work from the current governance workflows.</p>

        @if ($pendingProviderActions > 0)
            <div class="card border-0 shadow-sm rounded-3 mb-3 overflow-hidden bg-white" style="border-left: 4px solid #e5a919 !important;">
                <div class="card-body p-3.5 d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge bg-warning text-dark fw-bold rounded-pill px-2 py-0.5" style="font-size: 0.68rem; letter-spacing: 0.04em;">Action required</span>
                        </div>
                        <h3 class="h6 fw-bold text-dark mb-1">{{ $pendingProviderActions }} provider{{ $pendingProviderActions === 1 ? '' : 's' }} waiting for activation</h3>
                        <p class="text-secondary small mb-0">These providers are Bureau-verified and ready for Administrator review. Approving one makes it operational.</p>
                    </div>
                    <a class="btn btn-warning btn-sm fw-bold rounded-3 px-3.5 py-2 shadow-sm text-dark d-inline-flex align-items-center gap-1.5" href="{{ route('admin.providers.index', ['verification' => 'verified', 'status' => 'pending']) }}">
                        <i class="bi bi-shield-check"></i>
                        <span>Review activation queue</span>
                    </a>
                </div>
            </div>
        @else
            <div class="card border-0 shadow-sm rounded-3 mb-3 bg-white" style="border-left: 4px solid #10b981 !important;">
                <div class="card-body p-3.5 d-flex align-items-center gap-3">
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 fw-bold small">Clear</span>
                    <div>
                        <h3 class="h6 fw-bold mb-0 text-dark">No provider activation actions are waiting</h3>
                        <p class="text-muted small mb-0">The Administrator activation queue has no Bureau-verified pending providers.</p>
                    </div>
                </div>
            </div>
        @endif

        @if ($pendingFinalGuides > 0)
            <div class="card border-0 shadow-sm rounded-3 mb-3 overflow-hidden bg-white" style="border-left: 4px solid #7c3aed !important;">
                <div class="card-body p-3.5 d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-0.5 mb-1" style="font-size: 0.68rem;">Final guide review</span>
                        <h3 class="h6 fw-bold text-dark mb-1">{{ $pendingFinalGuides }} guide{{ $pendingFinalGuides === 1 ? '' : 's' }} awaiting final approval</h3>
                        <p class="text-secondary small mb-0">These guides passed Tourism Bureau verification and require Administrator approval.</p>
                    </div>
                    <a class="btn btn-outline-primary btn-sm fw-semibold rounded-3 px-3" href="{{ route('admin.guides.index') }}">Review guide queue</a>
                </div>
            </div>
        @endif

        <div class="card border-0 shadow-sm rounded-3 bg-white" style="border-left: 4px solid #0284c7 !important;">
            <div class="card-body p-3.5 d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge {{ $adminUnreadNotifications ? 'bg-primary' : 'bg-success-subtle text-success border border-success-subtle' }} rounded-pill px-2 py-0.5" style="font-size: 0.68rem;">{{ $adminUnreadNotifications ? 'Unread platform alerts' : 'All alerts reviewed' }}</span>
                    </div>
                    <h3 class="h6 fw-bold mb-1 text-dark">Administrator alert center</h3>
                    <p class="text-muted small mb-0">{{ $adminUnreadNotifications ? $adminUnreadNotifications.' new '.($adminUnreadNotifications === 1 ? 'alert needs' : 'alerts need').' review.' : 'There are no unread platform alerts right now.' }}</p>
                </div>
                <a class="btn btn-outline-primary btn-sm rounded-3 px-3.5 py-2 fw-semibold d-inline-flex align-items-center gap-1.5" href="{{ route('notifications.index') }}">
                    <i class="bi bi-bell"></i>
                    <span>Open alert center</span>
                </a>
            </div>
        </div>
    </section>

    {{-- Platform Overview KPI Cards --}}
    <section aria-labelledby="overview-heading" class="mb-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h2 id="overview-heading" class="h6 fw-bold mb-0 text-dark text-uppercase" style="letter-spacing: 0.05em; font-size: 0.78rem;">
                Platform overview
            </h2>
            <span class="text-muted small">Live network metrics</span>
        </div>
        <div class="row g-3">
            @foreach ([
                ['Total users', $totalUsers, route('admin.users.index'), 'bi-people'],
                ['Active users', $activeUsers, route('admin.users.index', ['active' => '1']), 'bi-person-check'],
                ['Active providers', $activeProviders, route('admin.providers.index'), 'bi-building-check'],
                ['Approved guides', $approvedGuides, route('admin.guides.index', ['status' => 'approved']), 'bi-person-badge'],
                ['All bookings', $bookings, route('admin.reports.index'), 'bi-journal-check'],
                ['Reviews', $reviewCount, route('admin.reviews.index'), 'bi-star'],
            ] as [$label, $value, $targetUrl, $icon])
                <div class="col-6 col-md-4 col-xl-2">
                    <a class="card border shadow-sm rounded-3 h-100 text-decoration-none bg-white p-3.5 d-flex flex-column justify-content-between" href="{{ $targetUrl }}" style="border-color: #e2e8f0 !important; transition: transform 0.15s ease, box-shadow 0.15s ease;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small fw-bold text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.06em;">{{ $label }}</span>
                            <div class="d-flex align-items-center justify-content-center rounded-2 bg-light border text-secondary" style="width: 26px; height: 26px;">
                                <i class="bi {{ $icon }}" style="font-size: 0.8rem;"></i>
                            </div>
                        </div>
                        <div class="h3 fw-bold mb-0 text-dark" style="font-family: var(--font-display); font-size: 1.65rem; letter-spacing: -0.02em;">{{ $value }}</div>
                    </a>
                </div>
            @endforeach
        </div>
    </section>

    {{-- 2-Column Section: Recent Activity & Operational Insights --}}
    <div class="row g-4 mb-4">
        <section class="col-xl-7" aria-labelledby="activity-heading">
            <div class="card border shadow-sm rounded-3 h-100 bg-white overflow-hidden" style="border-color: #e2e8f0 !important;">
                <div class="card-header bg-white d-flex justify-content-between align-items-center p-3.5 border-bottom">
                    <div>
                        <h2 id="activity-heading" class="h6 fw-bold mb-0 text-dark" style="font-family: var(--font-display);">
                            <i class="bi bi-terminal text-muted me-1.5"></i> Recent activity
                        </h2>
                        <p class="text-muted small mb-0">Governance and platform changes recorded by the audit service.</p>
                    </div>
                    <a class="btn btn-light btn-sm rounded-2 px-2.5 py-1 fw-semibold small text-muted border" href="{{ route('admin.audit.index') }}">
                        Audit log &rarr;
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-uppercase small text-muted" style="font-size: 0.68rem; letter-spacing: 0.05em;">
                            <tr>
                                <th class="ps-3.5 py-2.5">Action</th>
                                <th class="py-2.5">Actor</th>
                                <th class="py-2.5">Target</th>
                                <th class="pe-3.5 py-2.5 text-end">When</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentAudit as $entry)
                                <tr>
                                    <td class="ps-3.5 py-3 fw-semibold text-dark">
                                        <span class="badge bg-light text-dark border rounded-2 px-2 py-0.5 font-monospace" style="font-size: 0.72rem;">
                                            {{ str_replace('_', ' ', ucfirst($entry->action)) }}
                                        </span>
                                    </td>
                                    <td class="py-3 text-secondary small">{{ $entry->actor?->email ?? 'System' }}</td>
                                    <td class="py-3 text-muted small font-monospace">{{ $entry->subject_type ? class_basename($entry->subject_type).' #'.$entry->subject_id : 'Platform' }}</td>
                                    <td class="pe-3.5 py-3 text-muted text-nowrap text-end small">{{ $entry->created_at?->diffForHumans() }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4 small">No administrative activity has been recorded yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section class="col-xl-5" aria-labelledby="insights-heading">
            <div class="card border shadow-sm rounded-3 h-100 bg-white overflow-hidden d-flex flex-column" style="border-color: #e2e8f0 !important;">
                <div class="card-header bg-white p-3.5 border-bottom">
                    <h2 id="insights-heading" class="h6 fw-bold mb-0 text-dark" style="font-family: var(--font-display);">
                        <i class="bi bi-pie-chart text-muted me-1.5"></i> Operational insights
                    </h2>
                    <p class="text-muted small mb-0">Current governance state derived from platform records.</p>
                </div>
                <div class="list-group list-group-flush flex-grow-1">
                    <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center p-3" href="{{ route('admin.providers.index', ['status' => 'approved']) }}">
                        <span class="d-flex align-items-center gap-2">
                            <i class="bi bi-check-circle text-success"></i>
                            <span class="fw-semibold text-dark small">Approved providers</span>
                        </span>
                        <span class="badge bg-success-subtle text-success rounded-pill px-2.5 py-1">{{ $approvedProviders }}</span>
                    </a>
                    <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center p-3" href="{{ route('admin.providers.index', ['status' => 'suspended']) }}">
                        <span class="d-flex align-items-center gap-2">
                            <i class="bi bi-pause-circle text-warning"></i>
                            <span class="fw-semibold text-dark small">Suspended providers</span>
                        </span>
                        <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill px-2.5 py-1">{{ $suspendedProviders }}</span>
                    </a>
                    <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center p-3" href="{{ route('admin.providers.index', ['status' => 'rejected']) }}">
                        <span class="d-flex align-items-center gap-2">
                            <i class="bi bi-x-circle text-danger"></i>
                            <span class="fw-semibold text-dark small">Rejected providers</span>
                        </span>
                        <span class="badge bg-danger-subtle text-danger rounded-pill px-2.5 py-1">{{ $rejectedProviders }}</span>
                    </a>
                    <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center p-3" href="{{ route('admin.subscriptions.index') }}">
                        <span class="d-flex align-items-center gap-2">
                            <i class="bi bi-credit-card text-primary"></i>
                            <span class="fw-semibold text-dark small">Subscription configuration</span>
                        </span>
                        <span class="badge bg-light text-muted border rounded-2 px-2 py-0.5 small">View</span>
                    </a>
                </div>
                <div class="card-footer bg-light-subtle small text-muted p-3 border-top">
                    <i class="bi bi-info-circle me-1"></i> Chapa payment confirmation is active; this overview does not include payment reconciliation details.
                </div>
            </div>
        </section>
    </div>

    {{-- Platform Alerts Feed --}}
    <section aria-labelledby="alerts-heading">
        <div class="card border shadow-sm rounded-3 bg-white overflow-hidden" style="border-color: #e2e8f0 !important;">
            <div class="card-header bg-white d-flex justify-content-between align-items-center p-3.5 border-bottom">
                <div>
                    <h2 id="alerts-heading" class="h6 fw-bold mb-0 text-dark" style="font-family: var(--font-display);">
                        <i class="bi bi-bell text-muted me-1.5"></i> Recent platform alerts
                    </h2>
                    <p class="text-muted small mb-0">Central notifications from booking, payment, and governance workflows.</p>
                </div>
                <a class="btn btn-light btn-sm rounded-2 px-2.5 py-1 fw-semibold small text-muted border" href="{{ route('notifications.index') }}">
                    All alerts &rarr;
                </a>
            </div>
            <div class="list-group list-group-flush">
                @forelse ($adminNotifications as $notification)
                    <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-start gap-3 p-3.5 {{ $notification->read_status ? '' : 'bg-primary-subtle bg-opacity-25' }}" href="{{ route('notifications.navigate', $notification) }}">
                        <span class="d-flex align-items-start gap-3">
                            <span class="badge bg-light text-dark border rounded-2 px-2 py-0.5 text-uppercase font-monospace" style="font-size: 0.68rem;">{{ str_replace('_', ' ', $notification->type) }}</span>
                            <span>
                                <strong class="text-dark d-block mb-1 small">{{ $notification->title }}</strong>
                                <span class="small text-muted">{{ $notification->message }}</span>
                            </span>
                        </span>
                        <time class="small text-muted text-nowrap" datetime="{{ $notification->sent_date?->toIso8601String() }}">{{ $notification->sent_date?->diffForHumans() }}</time>
                    </a>
                @empty
                    <div class="p-4 text-muted text-center small">
                        <i class="bi bi-check2-circle text-success fs-4 d-block mb-1"></i>
                        Platform alerts will appear here as real workflows need attention.
                    </div>
                @endforelse
            </div>
        </div>
    </section>
</div>
@endsection
