<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Itinerary: {{ $trip->title }} — Ethio Tour</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        @page {
            size: A4;
            margin: 1.5cm;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: #1a1a1a;
            background: #f8faf9;
            padding: 2rem 0;
        }
        .print-container {
            max-width: 860px;
            margin: 0 auto;
            background: #ffffff;
            padding: 3rem;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
        }
        .brand-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 700;
            color: #0d3824;
            font-size: 1.25rem;
            letter-spacing: -0.02em;
        }
        .timeline-day-header {
            background: #eef7f2;
            border-left: 4px solid #198754;
            padding: 0.75rem 1rem;
            border-radius: 0 8px 8px 0;
            margin-top: 2rem;
            margin-bottom: 1rem;
        }
        .timeline-stop-card {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 1rem 1.25rem;
            margin-bottom: 1rem;
            background: #ffffff;
        }
        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }
            .print-container {
                box-shadow: none;
                padding: 0;
                max-width: 100%;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="container no-print mb-4 d-flex justify-content-between align-items-center" style="max-width: 860px;">
        <a href="{{ route('smart-trip.show', $trip) }}" class="btn btn-outline-secondary btn-sm">
            &larr; Back to itinerary
        </a>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-success btn-sm fw-bold">
                <i class="bi bi-printer me-1"></i> Print / Save as PDF
            </button>
        </div>
    </div>

    <main class="print-container">
        <!-- Header -->
        <header class="border-bottom pb-4 mb-4">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="brand-badge mb-2">
                        <span>🇪🇹 Ethio Tour</span>
                        <span class="text-muted fw-normal fs-6">· Travel Itinerary</span>
                    </div>
                    <h1 class="h2 fw-bold text-dark mb-1">{{ $trip->title }}</h1>
                    <p class="text-secondary mb-0">
                        <i class="bi bi-geo-alt me-1"></i> {{ $trip->destinations->pluck('name')->join(', ') }}
                    </p>
                </div>
                <div class="text-end">
                    <span class="badge bg-success-subtle text-success fs-6 px-3 py-2 border border-success-subtle">
                        {{ $trip->start_date->format('M d, Y') }} – {{ $trip->end_date->format('M d, Y') }}
                    </p>
                    <div class="small text-muted mt-1">
                        {{ $trip->start_date->diffInDays($trip->end_date) + 1 }} Days · {{ count($items) }} Stops
                    </div>
                </div>
            </div>
        </header>

        <!-- Trip Overview Stats -->
        <section class="row g-3 mb-4">
            <div class="col-sm-4">
                <div class="p-3 bg-light rounded-3 border">
                    <div class="text-muted small text-uppercase">Destinations</div>
                    <div class="fw-bold fs-6">{{ $trip->destinations->pluck('name')->join(', ') ?: 'Ethiopia' }}</div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="p-3 bg-light rounded-3 border">
                    <div class="text-muted small text-uppercase">Interests & Goals</div>
                    <div class="fw-bold fs-6">
                        {{ collect($trip->preferences ?? [])->map(fn ($p) => ucfirst($p))->join(', ') ?: 'General Exploration' }}
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="p-3 bg-light rounded-3 border">
                    <div class="text-muted small text-uppercase">Itinerary Status</div>
                    <div class="fw-bold fs-6 text-success text-capitalize">{{ $trip->status }}</div>
                </div>
            </div>
        </section>

        <!-- Day by Day Timetable -->
        @php($grouped = collect($items)->groupBy(fn ($item) => $item['planned_date'] ?: 'Flexible'))
        
        <section class="mb-5">
            <h2 class="h5 fw-bold text-uppercase text-secondary mb-3">Daily Travel Schedule</h2>
            
            @forelse($grouped as $day => $dayItems)
                <div class="timeline-day-header d-flex justify-content-between align-items-center">
                    <h3 class="h6 fw-bold mb-0 text-success">
                        <i class="bi bi-calendar3 me-2"></i>
                        {{ $day === 'Flexible' ? 'Flexible Schedule / Unscheduled Stops' : date('l, F j, Y', strtotime($day)) }}
                    </h3>
                    <span class="badge bg-white text-dark border">{{ count($dayItems) }} {{ \Illuminate\Support\Str::plural('stop', count($dayItems)) }}</span>
                </div>

                @foreach($dayItems as $item)
                    <article class="timeline-stop-card">
                        <div class="d-flex justify-content-between align-items-start gap-3">
                            <div>
                                <span class="badge bg-light text-success border mb-1">
                                    <i class="bi {{ $item['icon'] ?? 'bi-pin-map' }} me-1"></i>
                                    {{ $item['type_label'] }}
                                </span>
                                <h4 class="h6 fw-bold mb-1">{{ $item['title'] }}</h4>
                                <p class="small text-muted mb-2">
                                    <i class="bi bi-geo-alt me-1"></i> {{ $item['destination'] ?: 'Ethiopia' }}
                                </p>
                            </div>
                            @if(!empty($item['price_hint']))
                                <span class="badge bg-success-subtle text-success border border-success-subtle fw-semibold">
                                    {{ $item['price_hint'] }}
                                </span>
                            @endif
                        </div>

                        <p class="small text-secondary mb-2">{{ $item['summary'] }}</p>

                        @if(!empty($item['notes']))
                            <div class="p-2 bg-light rounded small border-start border-3 border-warning mt-2">
                                <strong>Note:</strong> {{ $item['notes'] }}
                            </div>
                        @endif
                    </article>
                @endforeach
            @empty
                <div class="p-4 text-center text-muted border rounded">
                    No itinerary stops added to this trip yet.
                </div>
            @endforelse
        </section>

        <!-- Travel Checklist & Footer -->
        <footer class="border-top pt-4 mt-5 text-muted small">
            <div class="row g-4">
                <div class="col-md-6">
                    <h5 class="h6 fw-bold text-dark">Important Travel Reminders</h5>
                    <ul class="ps-3 mb-0">
                        <li>Carry local currency (ETB) for smaller attractions and artisan markets.</li>
                        <li>Keep your reservation voucher codes handy for hotel and tour guide check-in.</li>
                        <li>Respect local customs and photography regulations at religious heritage sites.</li>
                    </ul>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-1"><strong>Ethio Tour — Land of Origins</strong></p>
                    <p class="mb-0">Verified travel planning &amp; local experience marketplace.</p>
                    <p class="small text-muted mt-2">Generated on {{ now()->format('F d, Y · H:i') }}</p>
                </div>
            </div>
        </footer>
    </main>
</body>
</html>
