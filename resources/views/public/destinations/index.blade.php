@extends('layouts.app')

@section('title', 'Destinations & Spaces Directory · Discover Ethiopia')

@section('content')
<!-- Hero Section (Visit Norway / GetYourGuide Style) -->
<div class="destinations-hero-section py-4 py-lg-5 border-bottom" style="background: linear-gradient(180deg, #f4f7f6 0%, #e8f0ec 100%);">
    <div class="container">
        <div class="mb-2">
            <x-ui.back-button :href="route('home')" label="Back to Home" />
        </div>
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <span class="badge bg-success-subtle text-success text-uppercase px-2.5 py-1 mb-2 fw-bold border border-success-subtle" style="letter-spacing: 0.08em; font-size: 0.72rem;">
                    Ethiopia Land of Origins &bull; Regional Discovery
                </span>
                <h1 class="display-6 fw-bold mb-2 text-dark" style="font-family: var(--font-display); letter-spacing: -0.03em;">Discover Ethiopia's Destinations &amp; Spaces</h1>
                <p class="text-secondary mb-0" style="max-width: 800px; font-size: 1.05rem; line-height: 1.6;">
                    Explore iconic castle compounds, ancient rock-hewn sanctuaries, vibrant lakes, and dramatic highlands across Ethiopia, and connect with verified local services.
                </p>
            </div>
            <div>
                <a class="btn btn-vn-navy fw-bold px-3 py-2 shadow-sm d-inline-flex align-items-center gap-2" href="{{ route('map', ['category' => 'destinations']) }}">
                    <i class="bi bi-map-fill text-warning"></i>
                    <span>Interactive Map</span>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container py-4 py-lg-5">
    <div class="row g-4">
        <!-- Left Filter Sidebar (Visit Norway & GetYourGuide Inspired) -->
        <aside class="col-lg-3">
            <form id="destinations-filter-form" method="GET" action="{{ route('destinations.index') }}">
                <!-- Search Box -->
                <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden bg-white">
                    <div class="card-header bg-white p-3 border-bottom">
                        <h2 class="h6 fw-bold mb-0 text-dark" style="font-family: var(--font-display);">
                            <i class="bi bi-search text-success me-1"></i> Search Destinations
                        </h2>
                    </div>
                    <div class="card-body p-3">
                        <label class="form-label small fw-bold text-muted" for="destination-q">Where are you going?</label>
                        <div class="input-group mb-3">
                            <input class="form-control" id="destination-q" name="q" value="{{ $search }}" placeholder="Destination or region..." style="border-radius: 0.5rem 0 0 0.5rem;">
                            @if($search)
                                <a class="btn btn-outline-secondary" href="{{ route('destinations.index', array_filter(['category' => $category, 'region' => $region, 'amenity' => $amenity, 'sort' => $sort])) }}" title="Clear search">&times;</a>
                            @endif
                        </div>
                        <button class="btn btn-vn-red w-100 fw-bold shadow-sm" type="submit">
                            Search Spaces
                        </button>
                    </div>
                </div>

                <!-- Show on Map Teaser Card -->
                <a class="card border-0 shadow-sm rounded-4 mb-4 text-decoration-none overflow-hidden text-white position-relative" href="{{ route('map', ['category' => 'destinations']) }}" style="background: linear-gradient(135deg, #062133 0%, #0b5e42 100%); min-height: 105px;">
                    <div class="card-body p-3.5 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="badge bg-warning text-dark fw-bold mb-1" style="font-size: 0.68rem; letter-spacing: 0.05em;">Spatial Navigator</span>
                            <div class="fw-bold fs-6 text-white" style="font-family: var(--font-display);">Show on Interactive Map</div>
                            <div class="small text-white-50">View all mapped spaces &rarr;</div>
                        </div>
                        <div class="fs-1 text-white-50">
                            🗺️
                        </div>
                    </div>
                </a>

                <!-- Filter Accordions -->
                <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden bg-white">
                    <div class="card-header bg-white p-3 border-bottom d-flex justify-content-between align-items-center">
                        <h2 class="h6 fw-bold mb-0 text-dark" style="font-family: var(--font-display);">
                            <i class="bi bi-funnel-fill text-success me-1"></i> Filters
                        </h2>
                        @if($search || $category || $region || $amenity)
                            <a class="small text-danger text-decoration-none fw-semibold" href="{{ route('destinations.index') }}">
                                Reset All
                            </a>
                        @endif
                    </div>

                    <div class="accordion accordion-flush" id="destinationFiltersAccordion">
                        <!-- Space Types / Categories -->
                        @if(!empty($categoryCounts))
                            <div class="accordion-item">
                                <h3 class="accordion-header" id="headingCategory">
                                    <button class="accordion-button fw-bold py-3 text-dark bg-light-subtle" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCategory" aria-expanded="true" aria-controls="collapseCategory">
                                        Space &amp; Experience Type
                                    </button>
                                </h3>
                                <div id="collapseCategory" class="accordion-collapse collapse show" aria-labelledby="headingCategory">
                                    <div class="accordion-body p-3">
                                        <div class="d-flex flex-column gap-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="category" id="cat-all" value="" @checked(empty($category)) onchange="this.form.submit()">
                                                <label class="form-check-label d-flex justify-content-between small" for="cat-all">
                                                    <span>All Space Types</span>
                                                    <span class="badge bg-light text-muted">{{ $totalSpacesCount }}</span>
                                                </label>
                                            </div>
                                            @foreach($categoryCounts as $cKey => $cData)
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="category" id="cat-{{ $cKey }}" value="{{ $cKey }}" @checked($category === $cKey) onchange="this.form.submit()">
                                                    <label class="form-check-label d-flex justify-content-between small" for="cat-{{ $cKey }}">
                                                        <span>{{ $cData['label'] }}</span>
                                                        <span class="badge bg-light text-dark border">{{ $cData['count'] }}</span>
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Regions & Circuits -->
                        @if(!empty($regionCounts))
                            <div class="accordion-item">
                                <h3 class="accordion-header" id="headingRegion">
                                    <button class="accordion-button fw-bold py-3 text-dark bg-light-subtle" type="button" data-bs-toggle="collapse" data-bs-target="#collapseRegion" aria-expanded="true" aria-controls="collapseRegion">
                                        Region &amp; Circuit
                                    </button>
                                </h3>
                                <div id="collapseRegion" class="accordion-collapse collapse show" aria-labelledby="headingRegion">
                                    <div class="accordion-body p-3">
                                        <div class="d-flex flex-column gap-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="region" id="reg-all" value="" @checked(empty($region)) onchange="this.form.submit()">
                                                <label class="form-check-label d-flex justify-content-between small" for="reg-all">
                                                    <span>All Regions</span>
                                                    <span class="badge bg-light text-muted">{{ $totalSpacesCount }}</span>
                                                </label>
                                            </div>
                                            @foreach($regionCounts as $rKey => $rData)
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="region" id="reg-{{ \Illuminate\Support\Str::slug($rKey) }}" value="{{ $rKey }}" @checked($region === $rKey) onchange="this.form.submit()">
                                                    <label class="form-check-label d-flex justify-content-between small" for="reg-{{ \Illuminate\Support\Str::slug($rKey) }}">
                                                        <span>{{ $rKey }}</span>
                                                        <span class="badge bg-light text-dark border">{{ $rData['count'] }}</span>
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Amenities & Features -->
                        @if(!empty($amenityCounts))
                            <div class="accordion-item">
                                <h3 class="accordion-header" id="headingAmenity">
                                    <button class="accordion-button fw-bold py-3 text-dark bg-light-subtle" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAmenity" aria-expanded="false" aria-controls="collapseAmenity">
                                        Amenities
                                    </button>
                                </h3>
                                <div id="collapseAmenity" class="accordion-collapse collapse" aria-labelledby="headingAmenity">
                                    <div class="accordion-body p-3">
                                        <div class="d-flex flex-column gap-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="amenity" id="amen-all" value="" @checked(empty($amenity)) onchange="this.form.submit()">
                                                <label class="form-check-label d-flex justify-content-between small" for="amen-all">
                                                    <span>All Amenities</span>
                                                </label>
                                            </div>
                                            @foreach($amenityCounts as $amName => $amCount)
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="amenity" id="amen-{{ \Illuminate\Support\Str::slug($amName) }}" value="{{ $amName }}" @checked($amenity === $amName) onchange="this.form.submit()">
                                                    <label class="form-check-label d-flex justify-content-between small" for="amen-{{ \Illuminate\Support\Str::slug($amName) }}">
                                                        <span>{{ $amName }}</span>
                                                        <span class="badge bg-light text-dark border">{{ $amCount }}</span>
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <input type="hidden" name="sort" value="{{ $sort }}">
            </form>
        </aside>

        <!-- Right Content Area -->
        <main class="col-lg-9">
            <!-- Header Controls: Counter, Map Toggle & Sort Dropdown -->
            <div class="card border-0 shadow-sm rounded-4 p-3 mb-4 bg-white">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <span class="h5 fw-bold text-dark mb-0" style="font-family: var(--font-display);">
                            {{ $destinations->count() }} space{{ $destinations->count() === 1 ? '' : 's' }} found
                        </span>
                        @if($category || $region || $amenity || $search)
                            <span class="badge bg-success-subtle text-success border border-success-subtle ms-2">
                                Filtered
                            </span>
                        @endif
                    </div>

                    <div class="destination-toolbar d-flex flex-wrap align-items-center gap-3">
                        <a class="btn btn-outline-success btn-sm fw-semibold d-inline-flex align-items-center gap-1 rounded-3" href="{{ route('map', ['category' => 'destinations']) }}">
                            <i class="bi bi-geo-alt-fill"></i>
                            <span>Show on the map</span>
                        </a>

                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <label class="small text-muted fw-semibold text-nowrap" for="sort-dropdown">Sort by:</label>
                            <select id="sort-dropdown" class="form-select form-select-sm rounded-3" style="width: auto;" onchange="location.href='{{ route('destinations.index', array_filter(['q' => $search, 'category' => $category, 'region' => $region, 'amenity' => $amenity])) }}' + (this.value ? '&sort=' + this.value : '')">
                                <option value="recommended" @selected($sort === 'recommended')>Recommended</option>
                                <option value="name_asc" @selected($sort === 'name_asc')>Name (A – Z)</option>
                                <option value="name_desc" @selected($sort === 'name_desc')>Name (Z – A)</option>
                                <option value="attractions_count" @selected($sort === 'attractions_count')>Most Attractions</option>
                                <option value="services_count" @selected($sort === 'services_count')>Most Verified Services</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active Filter Badges -->
            @if($search || $category || $region || $amenity)
                <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                    <span class="small text-muted fw-bold">Active Filters:</span>
                    @if($search)
                        <span class="badge bg-light text-dark border d-inline-flex align-items-center gap-1 rounded-pill px-3 py-1.5">
                            Search: "{{ $search }}"
                            <a href="{{ route('destinations.index', array_filter(['category' => $category, 'region' => $region, 'amenity' => $amenity, 'sort' => $sort])) }}" class="text-muted text-decoration-none ms-1">&times;</a>
                        </span>
                    @endif
                    @if($category)
                        <span class="badge bg-success text-white d-inline-flex align-items-center gap-1 rounded-pill px-3 py-1.5">
                            Type: {{ \App\Models\Destination::CATEGORIES[$category] ?? $category }}
                            <a href="{{ route('destinations.index', array_filter(['q' => $search, 'region' => $region, 'amenity' => $amenity, 'sort' => $sort])) }}" class="text-white text-decoration-none ms-1">&times;</a>
                        </span>
                    @endif
                    @if($region)
                        <span class="badge bg-primary text-white d-inline-flex align-items-center gap-1 rounded-pill px-3 py-1.5">
                            Region: {{ $region }}
                            <a href="{{ route('destinations.index', array_filter(['q' => $search, 'category' => $category, 'amenity' => $amenity, 'sort' => $sort])) }}" class="text-white text-decoration-none ms-1">&times;</a>
                        </span>
                    @endif
                    @if($amenity)
                        <span class="badge bg-info text-dark d-inline-flex align-items-center gap-1 rounded-pill px-3 py-1.5">
                            Amenity: {{ $amenity }}
                            <a href="{{ route('destinations.index', array_filter(['q' => $search, 'category' => $category, 'region' => $region, 'sort' => $sort])) }}" class="text-dark text-decoration-none ms-1">&times;</a>
                        </span>
                    @endif
                    <a class="small text-danger fw-semibold text-decoration-none ms-2" href="{{ route('destinations.index') }}">Clear all</a>
                </div>
            @endif

            <!-- 3-Column Masterclass Experience Card Grid -->
            @if ($destinations->isEmpty())
                <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white">
                    <div class="display-4 mb-3">📍</div>
                    <h2 class="h4 fw-bold" style="font-family: var(--font-display);">No destinations or spaces found</h2>
                    <p class="text-muted mb-4">Try adjusting your search terms or filters to explore destinations.</p>
                    <div>
                        <a class="btn btn-vn-emerald fw-bold" href="{{ route('destinations.index') }}">
                            View All Destinations
                        </a>
                    </div>
                </div>
            @else
                <div class="row g-4">
                    @foreach ($destinations as $destination)
                        <div class="col-md-6 col-xl-4">
                            <article class="vn-experience-card">
                                <!-- Photo Hero with Scrim & Badges -->
                                <div class="vn-card-media">
                                    <img src="{{ $destination->heroImageUrl() }}"
                                         alt="{{ $destination->name }}"
                                         loading="lazy">

                                    <!-- Top Left: Featured Tag -->
                                    <div class="position-absolute top-0 start-0 m-3 d-flex flex-column gap-1.5" style="z-index: 5;">
                                        @if($destination->is_featured)
                                            <span class="vn-badge-frosted vn-badge-featured">
                                                ★ Featured Space
                                            </span>
                                        @endif
                                        @if($destination->category)
                                            <span class="vn-badge-frosted">
                                                {{ $destination->categoryLabel() }}
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Top Right: Wishlist Button -->
                                    <button type="button" class="vn-wishlist-btn" aria-label="Save {{ $destination->name }} to trip" onclick="this.classList.toggle('active')">
                                        <i class="bi bi-heart-fill"></i>
                                    </button>

                                    <!-- Bottom Scrim & Name Overlay -->
                                    <div class="position-absolute bottom-0 start-0 end-0 p-3" style="background: linear-gradient(to top, rgba(6, 33, 51, 0.92) 0%, rgba(6, 33, 51, 0.4) 70%, transparent 100%); z-index: 4;">
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0.5 mb-1" style="font-size: 0.68rem; font-weight: 700;">
                                            📍 {{ $destination->region ?: $destination->location }}
                                        </span>
                                        <h2 class="h5 fw-bold text-white mb-0 text-shadow">
                                            <a class="text-white text-decoration-none stretched-link-target" href="{{ route('destinations.show', $destination) }}">
                                                {{ $destination->name }}
                                            </a>
                                        </h2>
                                    </div>
                                </div>

                                <!-- Card Body -->
                                <div class="vn-card-body">
                                    @if($destination->tagline)
                                        <div class="small fw-semibold text-success mb-2 text-truncate" style="font-size: 0.82rem;">
                                            {{ $destination->tagline }}
                                        </div>
                                    @endif

                                    <p class="small text-secondary flex-grow-1 mb-3" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.5; font-size: 0.85rem;">
                                        {{ $destination->description }}
                                    </p>

                                    <!-- Live Counts -->
                                    <div class="d-flex flex-wrap gap-2 mb-3">
                                        @if($destination->attractions_count > 0)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1" style="font-size: 0.75rem;">
                                                <strong>{{ $destination->attractions_count }}</strong> {{ \Illuminate\Support\Str::plural('attraction', $destination->attractions_count) }}
                                            </span>
                                        @endif
                                        @if($destination->heritage_sites_count > 0 && $destination->attractions_count === 0)
                                            <span class="badge bg-secondary-subtle text-secondary border rounded-pill px-2.5 py-1" style="font-size: 0.75rem;">
                                                <strong>{{ $destination->heritage_sites_count }}</strong> heritage {{ \Illuminate\Support\Str::plural('site', $destination->heritage_sites_count) }}
                                            </span>
                                        @endif
                                        @if($destination->public_services_count > 0)
                                            <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1" style="font-size: 0.75rem;">
                                                <strong>{{ $destination->public_services_count }}</strong> verified {{ \Illuminate\Support\Str::plural('service', $destination->public_services_count) }}
                                            </span>
                                        @endif
                                        @if($destination->upcoming_events_count > 0)
                                            <span class="badge bg-warning-subtle text-dark border border-warning-subtle rounded-pill px-2.5 py-1" style="font-size: 0.75rem;">
                                                <strong>{{ $destination->upcoming_events_count }}</strong> {{ \Illuminate\Support\Str::plural('event', $destination->upcoming_events_count) }}
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Action Buttons Footer -->
                                    <div class="pt-3 border-top d-flex gap-2 align-items-center mt-auto">
                                        <a class="btn btn-vn-emerald btn-sm flex-grow-1 fw-bold text-center" href="{{ route('destinations.show', $destination) }}">
                                            Explore Space &rarr;
                                        </a>
                                        @if($destination->hasCoordinates())
                                            <a class="btn btn-light btn-sm border text-muted px-2.5 py-1 rounded-3" href="{{ route('map', ['category' => 'destinations', 'q' => $destination->name]) }}" title="View {{ $destination->name }} on map">
                                                📍
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </article>
                        </div>
                    @endforeach
                </div>
            @endif
        </main>
    </div>
</div>
@endsection
