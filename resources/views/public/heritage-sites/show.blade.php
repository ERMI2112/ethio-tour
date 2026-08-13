@extends('layouts.app')
@section('title', $heritageSite->heritage_type)
@section('content')
    <div class="container py-5"><a class="text-decoration-none small" href="{{ route('heritage-sites.index') }}">&larr; All heritage sites</a><article class="card border-0 shadow-sm mt-3"><div class="card-body p-4 p-md-5"><p class="text-uppercase text-muted small">Heritage site · <a href="{{ route('destinations.show', $heritageSite->destination) }}">{{ $heritageSite->destination->name }}</a></p><h1 class="h2">{{ $heritageSite->heritage_type }}</h1><dl class="row mb-0"><dt class="col-sm-3">Opening hours</dt><dd class="col-sm-9">{{ $heritageSite->opening_hours }}</dd><dt class="col-sm-3">Entrance fee</dt><dd class="col-sm-9">{{ number_format($heritageSite->entrance_fee, 2) }}</dd></dl></div></article></div>
@endsection
