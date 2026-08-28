@extends('layouts.app')

@section('title', $museum->museum_name)

@section('content')
<div class="container public-catalog-page py-4 py-lg-5">
    <a class="link-secondary d-inline-block mb-3" href="{{ route('museums.index') }}">&larr; Back to museums</a>
    <article class="public-catalog-card" data-aos="fade-up">
        <div class="card-body p-4 p-lg-5">
            <p class="text-uppercase small text-success fw-semibold mb-1">Museum collection</p>
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2"><h1 class="h2 mb-0">{{ $museum->museum_name }}</h1>@if($museum->latitude !== null && $museum->longitude !== null)<a class="btn btn-outline-success btn-sm" href="{{ route('map', ['category' => 'museums', 'q' => $museum->museum_name]) }}">View on Map</a>@endif</div>
            <p class="text-muted mb-4">{{ $museum->location }}</p>
            <div class="row g-4">
                <div class="col-lg-8">
                    <h2 class="h5">About this museum</h2>
                    <p class="mb-0">{!! nl2br(e($museum->description)) !!}</p>
                </div>
                <div class="col-lg-4">
                    <div class="bg-light rounded p-3">
                        <h2 class="h6">Visitor information</h2>
                        <dl class="mb-0 small">
                            <dt>Opening hours</dt>
                            <dd>{{ $museum->opening_hours }}</dd>
                            @if ($museum->entrance_fee !== null)
                                <dt>Admission</dt>
                                <dd>
                                    @if((float) $museum->entrance_fee > 0)
                                        <span class="text-dark">Fee applies — paid at the site</span>
                                    @else
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">Free Admission</span>
                                    @endif
                                </dd>
                            @endif
                            @if ($museum->contact_information)
                                <dt>Contact</dt>
                                <dd class="mb-0">{{ $museum->contact_information }}</dd>
                            @endif
                        </dl>
                    </div>
                </div>
            </div>
            @if ($museum->media_path && (str_starts_with($museum->media_path, 'http://') || str_starts_with($museum->media_path, 'https://') || str_starts_with($museum->media_path, '/')))
                <div class="mt-4"><a href="{{ $museum->media_path }}" target="_blank" rel="noopener">View museum media</a></div>
            @endif
        </div>
    </article>
</div>
@endsection
