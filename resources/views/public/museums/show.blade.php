@extends('layouts.app')

@section('title', $museum->museum_name)

@section('content')
<div class="container py-4">
    <a class="link-secondary d-inline-block mb-3" href="{{ route('museums.index') }}">&larr; Back to museums</a>
    <article class="card border-0 shadow-sm">
        <div class="card-body p-4 p-lg-5">
            <p class="text-uppercase small text-primary fw-semibold mb-1">Museum information</p>
            <h1 class="h2">{{ $museum->museum_name }}</h1>
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
                                <dt>Entrance fee</dt>
                                <dd>{{ $museum->entrance_fee }} ETB</dd>
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
