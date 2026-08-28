@extends('layouts.app')
@section('title', $heritageSite->heritage_type)
@section('content')
<div class="container public-catalog-page py-4 py-lg-5">
    <a class="text-decoration-none small text-secondary" href="{{ route('heritage-sites.index') }}">&larr; All heritage sites</a>
    <article class="public-catalog-card mt-3" data-aos="fade-up">
        <div class="card-body p-4 p-md-5">
            <p class="text-uppercase text-muted small mb-2">
                Heritage site &middot; <a class="text-decoration-none fw-semibold" href="{{ route('destinations.show', $heritageSite->destination) }}">{{ $heritageSite->destination->name }}</a>
            </p>
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-4">
                <h1 class="h2 mb-0 text-dark">{{ $heritageSite->heritage_type }}</h1>
                @if($heritageSite->latitude !== null && $heritageSite->longitude !== null)
                    <a class="btn btn-outline-success btn-sm" href="{{ route('map', ['category' => 'heritage_sites', 'q' => $heritageSite->heritage_type]) }}">View on Map</a>
                @endif
            </div>

            <div class="p-3 rounded-3 bg-light border mb-4">
                <dl class="row mb-0 small">
                    <dt class="col-sm-3 text-dark fw-bold">Opening hours</dt>
                    <dd class="col-sm-9 text-muted mb-2">{{ $heritageSite->opening_hours }}</dd>
                    <dt class="col-sm-3 text-dark fw-bold">Entrance fee</dt>
                    <dd class="col-sm-9 mb-0">
                        @if((float) $heritageSite->entrance_fee > 0)
                            <span class="text-dark fw-semibold">{{ number_format($heritageSite->entrance_fee, 2) }} ETB</span>
                            <span class="text-muted ms-1">— paid at the site</span>
                        @else
                            <span class="badge bg-success-subtle text-success border border-success-subtle">Free Admission</span>
                        @endif
                    </dd>
                </dl>
            </div>

            <div class="text-muted small d-flex align-items-start gap-1.5 p-2.5 rounded bg-body-tertiary border">
                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="currentColor" class="bi bi-info-circle flex-shrink-0 mt-0.5 text-primary" viewBox="0 0 16 16" aria-hidden="true">
                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                    <path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0"/>
                </svg>
                <span>Admission fees are paid directly at the heritage site entrance. Ethio Tour provides heritage preservation and visitor information.</span>
            </div>
        </div>
    </article>
</div>
@endsection
