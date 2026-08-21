@extends('layouts.app')

@section('title', 'Cultural Events')

@section('content')
    <div class="container public-catalog-page py-4 py-lg-5">
        <header class="public-page-hero mb-4" data-aos="fade-up">
            <p class="landing-eyebrow mb-2">Culture, music, and celebration</p>
            <h1 class="display-6 fw-bold mb-2">Cultural Events</h1>
            <p class="lead text-secondary mb-0">Find published festivals and cultural events with real dates, venues, and ticket information.</p>
        </header>

        <form class="public-filter-card mb-4" method="GET" action="{{ route('events.index') }}" data-aos="fade-up" data-aos-delay="80">
            <div class="card-body p-3 p-lg-4">
                <div class="row g-3 align-items-end">
                    <div class="col-md-9"><label class="form-label" for="event-search">Search events</label><input id="event-search" name="q" value="{{ $search }}" class="form-control" placeholder="Festival, event, or venue"></div>
                    <div class="col-md-3"><button class="btn btn-success w-100" type="submit">Search</button></div>
                </div>
            </div>
        </form>

        @if($events->isEmpty())
            <x-ui.empty-state title="No upcoming events found" message="Try another search or check back for newly published festivals and cultural events." />
        @else
            <div class="row g-4">
                @foreach($events as $event)
                    @php
                        $lowerEvent = strtolower($event->event_name);
                        $eventImg = str_contains($lowerEvent, 'meskel')
                            ? asset('images/events/meskel-festival.jpg')
                            : asset('images/events/timkat-festival.jpg');
                    @endphp
                    <div class="col-md-6 col-xl-4" data-aos="fade-up" data-aos-delay="{{ ($loop->index % 3) * 70 }}">
                        <article class="public-catalog-card h-100 overflow-hidden">
                            <div class="public-catalog-card__media position-relative" style="height: 180px; overflow: hidden; background: #0d3824;">
                                <img src="{{ $eventImg }}" alt="{{ $event->event_name }}" style="width: 100%; height: 100%; object-fit: cover;" loading="lazy">
                                <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(180deg, rgba(0,0,0,0.1) 0%, rgba(0,0,0,0.7) 100%);"></div>
                                <div class="position-absolute bottom-0 start-0 m-3 text-white">
                                    <div class="public-catalog-card__media-kicker text-warning">{{ optional($event->event_date)->format('d M Y') }}</div>
                                    <div class="public-catalog-card__media-label fw-bold text-white">{{ $event->destination?->name ?? 'Ethiopia' }}</div>
                                </div>
                            </div>
                            <div class="public-catalog-card__body">
                                <span class="badge badge-verified align-self-start mb-2">Published event</span>
                                <h2 class="mb-2"><a class="public-catalog-card__title text-dark text-decoration-none" href="{{ route('events.show', $event) }}">{{ $event->event_name }}</a></h2>
                                <p class="public-catalog-card__meta mb-2">{{ $event->venue }}</p>
                                <p class="public-catalog-card__description line-clamp-3 mb-3 text-secondary">{{ $event->description }}</p>
                                <div class="public-catalog-card__footer mt-auto pt-3 border-top d-flex justify-content-between align-items-center">
                                    <span class="public-catalog-card__meta small text-muted">{{ $event->ticketTypes->count() }} ticket {{ \Illuminate\Support\Str::plural('type', $event->ticketTypes->count()) }}</span>
                                    <a class="btn btn-outline-success btn-sm" href="{{ route('events.show', $event) }}">View event <span aria-hidden="true">→</span></a>
                                </div>
                            </div>
                        </article>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
