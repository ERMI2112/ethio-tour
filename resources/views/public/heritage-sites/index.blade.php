@extends('layouts.app')

@section('title', 'Heritage Sites')

@section('content')
    <div class="container public-catalog-page py-4 py-lg-5">
        <header class="public-page-hero mb-4" data-aos="fade-up"><p class="landing-eyebrow mb-2">Stories written in stone</p><h1 class="display-6 fw-bold mb-2">Heritage Sites</h1><p class="lead text-secondary mb-0">Explore historical and cultural sites connected to Ethiopia's remarkable destinations.</p></header>
        <form class="public-filter-card mb-4" method="GET" action="{{ route('heritage-sites.index') }}" data-aos="fade-up" data-aos-delay="80"><div class="card-body p-3 p-lg-4"><div class="row g-3 align-items-end"><div class="col-md-9"><label class="form-label" for="heritage-search">Search heritage sites</label><input class="form-control" id="heritage-search" name="q" placeholder="Search site type or destination" value="{{ $search }}"></div><div class="col-md-3"><x-ui.button class="w-100" variant="success" type="submit">Search</x-ui.button></div></div></div></form>
        @if($heritageSites->isEmpty())
            <x-ui.empty-state title="No heritage sites found" message="Try another search term or return later for published heritage information." />
        @else
            <div class="row g-4">@foreach($heritageSites as $heritageSite)<div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="{{ ($loop->index % 3) * 70 }}"><article class="public-catalog-card h-100"><div class="public-catalog-card__media public-catalog-card__media--heritage"><div><div class="public-catalog-card__media-kicker">Heritage site</div><div class="public-catalog-card__media-label">{{ $heritageSite->destination?->name ?? 'Ethiopia' }}</div></div></div><div class="public-catalog-card__body"><span class="badge badge-verified align-self-start mb-2">Cultural landmark</span><h2 class="mb-2"><a class="public-catalog-card__title" href="{{ route('heritage-sites.show', $heritageSite) }}">{{ $heritageSite->heritage_type }}</a></h2><p class="public-catalog-card__meta mb-3">{{ $heritageSite->destination?->name }}</p><div class="public-catalog-card__footer"><span class="public-catalog-card__meta">Open: {{ $heritageSite->opening_hours }}</span><a class="btn btn-outline-success btn-sm" href="{{ route('heritage-sites.show', $heritageSite) }}">Explore <span aria-hidden="true">→</span></a></div></div></article></div>@endforeach</div>
        @endif
    </div>
@endsection
