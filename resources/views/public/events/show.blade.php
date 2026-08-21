@extends('layouts.app')

@section('title', $event->event_name)

@section('content')
    @php
        $lowerEvent = strtolower($event->event_name);
        $eventImg = str_contains($lowerEvent, 'meskel')
            ? asset('images/events/meskel-festival.jpg')
            : asset('images/events/timkat-festival.jpg');
    @endphp
    <div class="container public-catalog-page py-4 py-lg-5">
        <a class="link-secondary d-inline-block mb-3" href="{{ route('events.index') }}">&larr; Back to events</a>
        <div class="row g-4 mt-1">
            <div class="col-lg-7" data-aos="fade-up">
                <article class="public-catalog-card overflow-hidden">
                    <div class="public-catalog-card__media position-relative" style="height: 260px; overflow: hidden; background: #0d3824;">
                        <img src="{{ $eventImg }}" alt="{{ $event->event_name }}" style="width: 100%; height: 100%; object-fit: cover;" loading="lazy">
                        <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(180deg, rgba(0,0,0,0.1) 0%, rgba(0,0,0,0.75) 100%);"></div>
                        <div class="position-absolute bottom-0 start-0 m-4 text-white">
                            <div class="public-catalog-card__media-kicker text-warning fs-6 mb-1">{{ optional($event->event_date)->format('d F Y') }}</div>
                            <div class="public-catalog-card__media-label fw-bold text-white fs-5">📍 {{ $event->destination->name }}</div>
                        </div>
                    </div>
                    <div class="public-catalog-card__body p-4 p-md-5">
                        <span class="badge badge-verified align-self-start mb-3">Published event</span>
                        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                            <h1 class="h2 mb-0 fw-bold">{{ $event->event_name }}</h1>
                            @if($event->latitude !== null && $event->longitude !== null)
                                <a class="btn btn-outline-success btn-sm" href="{{ route('map', ['category' => 'events', 'q' => $event->event_name]) }}">View on map</a>
                            @endif
                        </div>
                        <p class="public-catalog-card__meta text-muted mb-3">📍 {{ $event->destination->name }} · {{ $event->venue }}</p>
                        <p class="public-catalog-card__description leading-relaxed mb-4 text-secondary">{{ $event->description }}</p>
                        <div class="p-3 bg-light rounded-3 border">
                            <dl class="row mb-0">
                                <dt class="col-sm-4 text-muted small text-uppercase">Event Date</dt>
                                <dd class="col-sm-8 fw-semibold mb-2">{{ $event->event_date->format('F d, Y') }}</dd>
                                <dt class="col-sm-4 text-muted small text-uppercase">Schedule</dt>
                                <dd class="col-sm-8 fw-semibold mb-0">{{ $event->start_time }} – {{ $event->end_time }}</dd>
                            </dl>
                        </div>
                    </div>
                </article>
            </div>
            <div class="col-lg-5" data-aos="fade-up" data-aos-delay="80">
                <div class="public-catalog-card h-100">
                    <div class="public-catalog-card__body p-4">
                        <h2 class="h5 mb-3 fw-bold">Tickets &amp; Admission</h2>
                        @forelse($event->ticketTypes as $ticket)
                            <div class="border-bottom py-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <strong class="fs-6">{{ $ticket->name }}</strong>
                                    <span class="fw-bold text-success fs-5">{{ number_format($ticket->price, 2) }} ETB</span>
                                </div>
                                <div class="small text-muted mb-3">{{ app(\App\Services\EventInventoryService::class)->availableQuantity($ticket) }} remaining</div>
                                @auth
                                    @if(auth()->user()->role === 'tourist')
                                        <form method="POST" action="{{ route('tourist.event-reservations.store', $event) }}">
                                            @csrf
                                            <input type="hidden" name="ticket_type_id" value="{{ $ticket->ticket_type_id }}">
                                            <div class="input-group">
                                                <input class="form-control" type="number" name="quantity" min="1" value="1">
                                                <button class="btn btn-success fw-bold">Book tickets</button>
                                            </div>
                                        </form>
                                    @endif
                                @else
                                    <p class="small text-muted mb-0">Log in as a tourist to book.</p>
                                @endauth
                            </div>
                        @empty
                            <p class="text-muted mb-0">Tickets are not available yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
        <section class="public-catalog-card mt-4" data-aos="fade-up">
            <div class="public-catalog-card__body p-4 p-md-5">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="h5 mb-0 fw-bold">Attendee Reviews</h2>
                    <x-reviews.rating-summary :average="$reviewAverage" :count="$reviewCount" />
                </div>
                <x-reviews.review-list :reviews="$reviews" />
            </div>
        </section>
    </div>
@endsection
