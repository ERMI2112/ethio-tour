@extends('layouts.app')

@section('title', 'Cultural Events')

@section('content')
    <div class="container public-catalog-page py-4 py-lg-5">
        <header class="public-page-hero mb-4" data-aos="fade-up">
            <p class="landing-eyebrow mb-2">Culture, music, and celebration</p>
            <h1 class="display-6 fw-bold mb-2">Cultural Events</h1>
            <p class="lead text-secondary mb-0">Find published festivals and cultural events with real dates, venues, and ticket information.</p>
        </header>

        <form class="public-filter-card mb-4" method="GET" action="{{ route('events.index') }}" data-aos="fade-up" data-aos-delay="80"><div class="card-body p-3 p-lg-4"><div class="row g-3 align-items-end"><div class="col-md-9"><label class="form-label" for="event-search">Search events</label><input id="event-search" name="q" value="{{ $search }}" class="form-control" placeholder="Festival, event, or venue"></div><div class="col-md-3"><button class="btn btn-success w-100" type="submit">Search</button></div></div></div></form>

        @if($events->isEmpty())
            <x-ui.empty-state title="No upcoming events found" message="Try another search or check back for newly published festivals and cultural events." />
        @else
            <div class="row g-4">
                @foreach($events as $event)
                    @php($eventImage = '/images/events/'.str($event->event_name)->slug().'.jpg')
                    @php($hasEventImage = file_exists(public_path(ltrim($eventImage, '/'))))
                    <div class="col-md-6 col-xl-4" data-aos="fade-up" data-aos-delay="{{ ($loop->index % 3) * 70 }}">
                        <article class="public-catalog-card h-100">
                            <div class="public-catalog-card__media public-catalog-card__media--event">
                                @if($hasEventImage)<img src="{{ asset(ltrim($eventImage, '/')) }}" alt="{{ $event->event_name }}" loading="lazy"><span class="public-catalog-card__media-overlay" aria-hidden="true"></span>@endif
                                <div><div class="public-catalog-card__media-kicker">{{ optional($event->event_date)->format('d M Y') }}</div><div class="public-catalog-card__media-label">{{ $event->destination?->name ?? 'Ethiopia' }}</div></div>
                            </div>
                            <div class="public-catalog-card__body"><span class="badge badge-verified align-self-start mb-2">Published event</span><h2 class="mb-2"><a class="public-catalog-card__title" href="{{ route('events.show', $event) }}">{{ $event->event_name }}</a></h2><p class="public-catalog-card__meta mb-2">{{ $event->venue }}</p><p class="public-catalog-card__description line-clamp-3 mb-3">{{ $event->description }}</p><div class="public-catalog-card__footer"><span class="public-catalog-card__meta">{{ $event->ticketTypes->count() }} ticket {{ \Illuminate\Support\Str::plural('type', $event->ticketTypes->count()) }}</span><a class="btn btn-outline-success btn-sm" href="{{ route('events.show', $event) }}">View event <span aria-hidden="true">→</span></a></div></div>
                        </article>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
