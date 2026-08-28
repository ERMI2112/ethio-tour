@extends('layouts.app')

@section('title', 'Tourism Bureau Oversight')

@section('content')
<div class="container-fluid py-4 py-lg-5 px-3 px-lg-5">
    <div class="ws-page-header workspace-page-header mb-4 pb-3 border-bottom">
        <div><span class="ws-eyebrow"><span class="ws-eye-dot" aria-hidden="true"></span>Bureau oversight</span><h1 class="ws-title mb-1">Regulatory dashboard</h1><p class="ws-lead mb-0">Current verification, document, booking, and provider data.</p></div>
        <div class="d-flex gap-2"><span class="badge bg-light text-dark border align-self-center">{{ auth()->user()->email }}</span><a class="btn btn-dark btn-sm" href="{{ route('bureau.reports.index') }}">Audit reports</a></div>
    </div>
    @include('layouts.partials.flash-messages')

    <div class="row g-3 mb-4">
        @foreach([
            ['label' => 'Active tourists', 'value' => $activeTourists, 'note' => 'Active tourist accounts'],
            ['label' => 'Certified providers', 'value' => $certifiedProviders, 'note' => $pendingProviders.' awaiting Bureau verification'],
            ['label' => 'Pending documents', 'value' => $pendingDocuments, 'note' => 'Documents awaiting review'],
            ['label' => 'Pending payment amount', 'value' => number_format($activeEscrowVolume, 2).' ETB', 'note' => 'Pending payment records'],
        ] as $metric)
            <div class="col-6 col-xl-3"><x-ui.stat-card :label="$metric['label']" :icon="$metric['icon'] ?? null" :value="$metric['value']" :hint="$metric['note']" /></div>
        @endforeach
    </div>

    <h2 class="h5 mb-3">Needs attention</h2>
    <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="card border-0 shadow-sm rounded-4 h-100 p-4"><span class="text-muted small text-uppercase fw-bold">Guide verification queue</span><strong class="h4 mt-2">{{ $pendingGuides }} guide{{ $pendingGuides === 1 ? '' : 's' }} awaiting verification</strong><span class="small text-muted">{{ $pendingFinalGuides }} Bureau-verified guide(s) await Administrator approval.</span><a class="btn btn-outline-primary btn-sm mt-3" href="{{ route('bureau.guides.index', ['status' => 'pending']) }}">Review guides</a></div></div>
        <div class="col-md-4"><div class="card border-0 shadow-sm rounded-4 h-100 p-4"><span class="text-muted small text-uppercase fw-bold">Provider verification queue</span><strong class="h4 mt-2">{{ $pendingProviders }} provider{{ $pendingProviders === 1 ? '' : 's' }} awaiting verification</strong><span class="small text-muted">{{ $approvedProviders }} providers are Bureau-verified and platform-approved.</span><a class="btn btn-outline-primary btn-sm mt-3" href="{{ route('bureau.providers.index', ['status' => 'pending']) }}">Review providers</a></div></div>
        <div class="col-md-4"><div class="card border-0 shadow-sm rounded-4 h-100 p-4"><span class="text-muted small text-uppercase fw-bold">Suspended providers</span><strong class="h4 mt-2">{{ $suspendedProviders }}</strong><span class="small text-muted">Current provider records with suspended platform status.</span><a class="btn btn-outline-primary btn-sm mt-3" href="{{ route('bureau.providers.index', ['platform_status' => 'suspended']) }}">View providers</a></div></div>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-header bg-white"><h2 class="h5 mb-0">Recent decisions</h2></div><div class="list-group list-group-flush">
                @forelse($recentDecisions as $decision)<div class="list-group-item d-flex justify-content-between gap-3"><span>{{ str_replace('_', ' ', ucfirst($decision->action)) }}</span><small class="text-muted">{{ $decision->created_at?->diffForHumans() }}</small></div>@empty<div class="list-group-item text-muted">No Bureau decisions recorded yet.</div>@endforelse
            </div></div>
            <div class="card border-0 shadow-sm rounded-4"><div class="card-header bg-white"><h2 class="h5 mb-0">Booking activity</h2><small class="text-muted">Database-backed counts for the last six months</small></div><div class="card-body"><div class="row g-2">@foreach($bookingActivityMonths as $month)<div class="col-6 col-md-4"><div class="border rounded-3 p-3"><span class="small text-muted d-block">{{ $month['label'] }}</span><strong class="h5">{{ $month['total'] }}</strong><span class="small text-muted d-block">bookings</span></div></div>@endforeach</div></div></div>
        </div>
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-header bg-white"><h2 class="h5 mb-0">Verification summary</h2></div><div class="card-body"><dl class="row mb-0"><dt class="col-8">Bureau-verified guides</dt><dd class="col-4 text-end">{{ $verifiedGuides }}</dd><dt class="col-8">Museums</dt><dd class="col-4 text-end">{{ $museumCount }}</dd><dt class="col-8">Attractions</dt><dd class="col-4 text-end">{{ $attractionCount }}</dd><dt class="col-8">Pending documents</dt><dd class="col-4 text-end">{{ $pendingDocuments }}</dd></dl></div></div>
            <div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-header bg-white"><h2 class="h5 mb-0">Museum / tourism operations</h2></div><div class="card-body small text-muted">{{ $museumCount }} museum record{{ $museumCount === 1 ? '' : 's' }} and {{ $attractionCount }} attraction record{{ $attractionCount === 1 ? '' : 's' }} are currently stored.</div></div>
            <div class="card border-0 shadow-sm rounded-4"><div class="card-body small">
                <details class="workflow-details">
                    <summary class="d-flex align-items-center justify-content-between fw-semibold" style="cursor: pointer; list-style: none;">
                        <span><i class="bi bi-diagram-3 me-2 text-success" aria-hidden="true"></i>Workflow</span>
                        <i class="bi bi-chevron-down text-muted" aria-hidden="true"></i>
                    </summary>
                    <p class="mb-0 mt-2 text-muted">Bureau officers verify guide profiles and documents. Administrators make the final guide approval decision from their guide queue.</p>
                </details>
            </div></div>
        </div>
    </div>
</div>
@endsection
