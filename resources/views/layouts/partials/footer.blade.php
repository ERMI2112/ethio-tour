<footer class="site-footer mt-auto">
    <div class="container">
        <div class="row g-4 pb-4 border-bottom border-white-subtle">
            <div class="col-lg-4">
                <p class="fw-bold text-white mb-1">Ethio Tour</p>
                <p class="footer-muted small mb-0">A traveler-first way to discover Ethiopia — destinations, heritage, local experiences, trusted operators, bookings, and thoughtful trip planning.</p>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <h2 class="mb-3">Discover</h2>
                <ul class="list-unstyled small mb-0">
                    <li><a href="{{ route('destinations.index') }}">Destinations</a></li>
                    <li><a href="{{ route('heritage-sites.index') }}">Heritage sites</a></li>
                    <li><a href="{{ route('events.index') }}">Cultural events</a></li>
                    <li><a href="{{ route('map') }}">Interactive map</a></li>
                </ul>
            </div>
            <div class="col-6 col-md-4 col-lg-3">
                <h2 class="mb-3">Plan your trip</h2>
                <ul class="list-unstyled small mb-0">
                    <li><a href="{{ route('tour-guides.index') }}">Tour guides</a></li>
                    <li><a href="{{ route('tourism-services.index', ['provider_type' => 'hotel']) }}">Hotels</a></li>
                    <li><a href="{{ route('tourism-services.index', ['provider_type' => 'restaurant']) }}">Restaurants</a></li>
                    <li><a href="{{ route('transportation.index') }}">Transportation</a></li>
                    <li><a href="{{ route('smart-trip.index') }}">Smart Trip</a></li>
                </ul>
            </div>
            <div class="col-md-4 col-lg-3">
                <h2 class="mb-3">For tourism professionals</h2>
                <ul class="list-unstyled small mb-0">
                    <li><span class="footer-muted">Verified partners manage their services after signing in.</span></li>
                    <li><a href="{{ route('login') }}">Log in</a></li>
                    <li><a href="{{ route('register') }}">Register</a></li>
                </ul>
            </div>
        </div>
        <div class="d-flex flex-wrap justify-content-between gap-2 pt-3 small footer-muted">
            <span>&copy; {{ now()->year }} Ethio Tour. Explore, plan, and book Ethiopia with confidence.</span>
            <span>Verified operators · Traveler bookings · Secure platform</span>
        </div>
    </div>
</footer>
