@extends('layouts.app')

@section('title', 'Administrator Dashboard')

@section('content')
<div class="container-fluid py-4 py-lg-5">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <p class="text-uppercase text-muted small fw-semibold mb-1">Platform operations</p>
            <h1 class="h2 mb-1">Administrator Dashboard</h1>
            <p class="text-muted mb-0">Review the work that needs attention, then monitor the platform from one console.</p>
        </div>
        <span class="badge text-bg-success px-3 py-2">Operational</span>
    </div>

    <section aria-labelledby="attention-heading" class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div><h2 id="attention-heading" class="h5 mb-1">Attention</h2><p class="text-muted small mb-0">Actionable administrator work from the current governance workflows.</p></div>
        </div>
        @if ($pendingProviderActions > 0)
            <div class="card border-warning shadow-sm"><div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div><span class="badge text-bg-warning text-dark mb-2">Action required</span><h3 class="h5 mb-1">{{ $pendingProviderActions }} provider{{ $pendingProviderActions === 1 ? '' : 's' }} waiting for activation</h3><p class="text-muted mb-0">These providers are Bureau-verified and ready for Administrator review. Approving one makes it operational.</p></div>
                <a class="btn btn-warning" href="{{ route('admin.providers.index', ['verification' => 'verified', 'status' => 'pending']) }}">Review activation queue</a>
            </div></div>
        @else
            <div class="card border-success shadow-sm"><div class="card-body d-flex align-items-center gap-3"><span class="badge text-bg-success">Clear</span><div><h3 class="h6 mb-1">No provider activation actions are waiting</h3><p class="text-muted small mb-0">The Administrator activation queue has no Bureau-verified pending providers.</p></div></div></div>
        @endif
    </section>

    <section aria-labelledby="overview-heading" class="mb-4">
        <h2 id="overview-heading" class="h5 mb-3">Platform overview</h2>
        <div class="row g-3">
            @foreach ([
                ['Total users', $totalUsers, 'admin.users.index', 'text-primary'],
                ['Active users', $activeUsers, 'admin.users.index', 'text-success'],
                ['Active providers', $activeProviders, 'admin.providers.index', 'text-success'],
                ['Verified guides', $verifiedGuides, 'bureau.guides.index', 'text-info'],
                ['Active bookings', $bookings, 'admin.reports.index', 'text-primary'],
                ['Reviews', $reviewCount, 'admin.reviews.index', 'text-warning'],
            ] as [$label, $value, $route, $color])
                <div class="col-6 col-md-4 col-xl-2"><a class="card border-0 shadow-sm h-100 text-decoration-none" href="{{ route($route) }}"><div class="card-body"><div class="small text-muted">{{ $label }}</div><div class="display-6 fw-semibold {{ $color }}">{{ $value }}</div></div></a></div>
            @endforeach
        </div>
    </section>

    <div class="row g-4">
        <section class="col-xl-7" aria-labelledby="activity-heading">
            <div class="card border-0 shadow-sm h-100"><div class="card-header bg-white d-flex justify-content-between align-items-center py-3"><div><h2 id="activity-heading" class="h5 mb-1">Recent activity</h2><p class="text-muted small mb-0">Governance and platform changes recorded by the audit service.</p></div><a class="small" href="{{ route('admin.audit.index') }}">View audit log</a></div><div class="table-responsive"><table class="table align-middle mb-0"><thead class="table-light"><tr><th>Action</th><th>Actor</th><th>Target</th><th>When</th></tr></thead><tbody>
                @forelse ($recentAudit as $entry)
                    <tr><td class="fw-semibold">{{ str_replace('_', ' ', ucfirst($entry->action)) }}</td><td>{{ $entry->actor?->email ?? 'System' }}</td><td class="text-muted">{{ $entry->subject_type ? class_basename($entry->subject_type).' #'.$entry->subject_id : 'Platform' }}</td><td class="text-muted text-nowrap">{{ $entry->created_at?->diffForHumans() }}</td></tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">No administrative activity has been recorded yet.</td></tr>
                @endforelse
            </tbody></table></div></div>
        </section>
        <section class="col-xl-5" aria-labelledby="insights-heading">
            <div class="card border-0 shadow-sm h-100"><div class="card-header bg-white py-3"><h2 id="insights-heading" class="h5 mb-1">Operational insights</h2><p class="text-muted small mb-0">Current governance state derived from platform records.</p></div><div class="list-group list-group-flush">
                <a class="list-group-item list-group-item-action d-flex justify-content-between" href="{{ route('admin.providers.index', ['status' => 'approved']) }}"><span>Approved providers</span><strong>{{ $approvedProviders }}</strong></a>
                <a class="list-group-item list-group-item-action d-flex justify-content-between" href="{{ route('admin.providers.index', ['status' => 'suspended']) }}"><span>Suspended providers</span><strong>{{ $suspendedProviders }}</strong></a>
                <a class="list-group-item list-group-item-action d-flex justify-content-between" href="{{ route('admin.providers.index', ['status' => 'rejected']) }}"><span>Rejected providers</span><strong>{{ $rejectedProviders }}</strong></a>
                <a class="list-group-item list-group-item-action d-flex justify-content-between" href="{{ route('admin.subscriptions.index') }}"><span>Subscription configuration</span><strong>View</strong></a>
            </div><div class="card-footer bg-white small text-muted">Payment and revenue reporting remain unavailable until the centralized payment phase.</div></div>
        </section>
    </div>
</div>
@endsection
