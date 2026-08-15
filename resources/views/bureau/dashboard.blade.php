@extends('layouts.app')

@section('title', 'Bureau Dashboard')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h1 class="h3 mb-1">Tourism Bureau</h1><p class="text-muted mb-0">Verification and information management.</p></div>
    </div>
    <div class="row g-3 mb-4">
        @foreach ([['Pending guides', $pendingGuides, 'bureau.guides.index', 'pending'], ['Verified guides', $verifiedGuides, 'bureau.guides.index', 'verified'], ['Pending providers', $pendingProviders, 'bureau.providers.index', 'pending'], ['Approved providers', $approvedProviders, 'bureau.providers.index', 'verified'], ['Museum records', $museumCount, 'bureau.museums.index', null]] as [$label, $count, $route, $filter])
            <div class="col-sm-6 col-lg-4"><div class="card h-100 shadow-sm"><div class="card-body"><div class="text-muted small">{{ $label }}</div><div class="display-6 fw-semibold">{{ $count }}</div><a class="stretched-link" href="{{ route($route, $filter ? ['status' => $filter] : []) }}">Review</a></div></div></div>
        @endforeach
    </div>
    <div class="d-flex gap-2"><a class="btn btn-primary" href="{{ route('bureau.guides.index') }}">Guide verification</a><a class="btn btn-outline-primary" href="{{ route('bureau.providers.index') }}">Provider verification</a><a class="btn btn-outline-secondary" href="{{ route('bureau.museums.index') }}">Museum information</a></div>
</div>
@endsection
