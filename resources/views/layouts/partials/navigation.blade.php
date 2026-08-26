<nav class="navbar navbar-expand-xl main-navbar shadow-sm sticky-top" aria-label="Primary navigation">
    <div class="container d-flex align-items-center justify-content-between">
        {{-- Brand Logo with Ethiopian Land of Origins style icon --}}
        <a class="navbar-brand py-0" href="{{ route('home') }}">
            <svg width="32" height="32" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="24" cy="24" r="22" fill="#0b3825" stroke="#e5a919" stroke-width="2"/>
                <circle cx="24" cy="24" r="14" fill="#0f5132"/>
                <circle cx="24" cy="24" r="6" fill="#e5a919"/>
                <circle cx="24" cy="11" r="3.5" fill="#e5a919"/>
                <circle cx="24" cy="37" r="3.5" fill="#198754"/>
                <circle cx="11" cy="24" r="3.5" fill="#dc3545"/>
                <circle cx="37" cy="24" r="3.5" fill="#e5a919"/>
            </svg>
            <div>
                <div>Ethio Tour</div>
                <span class="brand-origin-badge">Land of Origins</span>
            </div>
        </a>

        <button class="navbar-toggler border-0 text-white" type="button" data-bs-toggle="collapse" data-bs-target="#primary-navigation" aria-controls="primary-navigation" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon" style="filter: invert(1);"></span>
        </button>

        <div class="collapse navbar-collapse" id="primary-navigation">
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0 gap-1 gap-xl-2 align-items-lg-center">
                <li class="nav-item"><a class="nav-link" href="{{ route('home') }}">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('destinations.index') }}">Destinations</a></li>
                {{-- Plan Your Trip --}}
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Plan Your Trip</a>
                    <ul class="dropdown-menu">
                        <li><h6 class="dropdown-header text-uppercase small">Plan with real public services</h6></li>
                        <li><a class="dropdown-item" href="{{ route('tour-guides.index') }}">Tour Guides</a></li>
                        <li><a class="dropdown-item" href="{{ route('tourism-services.index', ['provider_type' => 'hotel']) }}">Hotels</a></li>
                        <li><a class="dropdown-item" href="{{ route('tourism-services.index', ['provider_type' => 'restaurant']) }}">Restaurants</a></li>
                        <li><a class="dropdown-item" href="{{ route('transportation.index') }}">Transportation &amp; Car Rental</a></li>
                        <li><a class="dropdown-item" href="{{ route('map') }}">Map</a></li>
                        <li><a class="dropdown-item" href="{{ route('museums.index') }}">Museums</a></li>
                    </ul>
                </li>

                {{-- Events --}}
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Events</a>
                    <ul class="dropdown-menu">
                        <li><h6 class="dropdown-header text-uppercase small">Celebrations &amp; Gatherings</h6></li>
                        <li><a class="dropdown-item" href="{{ route('events.index') }}">Cultural Events</a></li>
                        <li><a class="dropdown-item" href="{{ route('events.index') }}">Festivals / Upcoming Events</a></li>
                    </ul>
                </li>

                {{-- Things to Do --}}
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Things to Do</a>
                    <ul class="dropdown-menu">
                        <li><h6 class="dropdown-header text-uppercase small">Experiences</h6></li>
                        <li><a class="dropdown-item" href="{{ route('heritage-sites.index') }}">Culture &amp; Heritage</a></li>
                        <li><a class="dropdown-item" href="{{ route('map', ['category' => 'destinations']) }}">Highlands &amp; Nature</a></li>
                        <li><a class="dropdown-item" href="{{ route('tourism-services.index', ['provider_type' => 'restaurant']) }}">Food &amp; Dining</a></li>
                    </ul>
                </li>

                {{-- Smart Trip (Includes Search Directory) --}}
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Smart Trip</a>
                    <ul class="dropdown-menu">
                        <li><h6 class="dropdown-header text-uppercase small">Itinerary &amp; Planning</h6></li>
                        <li><a class="dropdown-item" href="{{ auth()->user()?->role === 'tourist' ? route('smart-trip.create') : route('smart-trip.index') }}">Plan a Trip</a></li>
                        @auth
                            @if (auth()->user()->role === 'tourist')
                                <li><a class="dropdown-item" href="{{ route('smart-trip.index') }}">My Trips</a></li>
                            @endif
                        @endauth
                        <li><hr class="dropdown-divider"></li>
                        <li><h6 class="dropdown-header text-uppercase small">Explore &amp; Discover</h6></li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('search') }}">
                                <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/></svg>
                                <span>Search Directory &amp; Catalog</span>
                            </a>
                        </li>
                    </ul>
                </li>

                {{-- Authenticated Role Workspaces --}}
                @auth
                    @if (auth()->user()->role === 'tourist')
                        <li class="nav-item"><a class="nav-link" href="{{ route('tourist.reservations.index') }}">My Bookings</a></li>
                    @elseif (auth()->user()->role === 'tour_guide')
                        <li class="nav-item"><a class="nav-link" href="{{ route('tour-guide.dashboard') }}">Tour Guide Portal</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('tour-guide.profile') }}">My Profile</a></li>
                    @elseif (auth()->user()->role === 'service_provider')
                        @if (auth()->user()->serviceProvider?->isOperational())
                            @if (auth()->user()->serviceProvider?->provider_type === 'hotel')
                                <li class="nav-item"><a class="nav-link" href="{{ route('hotel.dashboard') }}">Hotel Dashboard</a></li>
                                <li class="nav-item"><a class="nav-link" href="{{ route('hotel.profile') }}">Profile</a></li>
                                <li class="nav-item"><a class="nav-link" href="{{ route('hotel.services.index') }}">Room Types</a></li>
                                <li class="nav-item"><a class="nav-link" href="{{ route('hotel.rooms.index') }}">Rooms</a></li>
                                <li class="nav-item"><a class="nav-link" href="{{ route('hotel.reservations.index') }}">Reservations</a></li>
                                <li class="nav-item"><a class="nav-link" href="{{ route('provider.reports') }}">Reports</a></li>
                            @elseif (auth()->user()->serviceProvider?->provider_type === 'restaurant')
                                <li class="nav-item"><a class="nav-link" href="{{ route('restaurant.dashboard') }}">Restaurant Dashboard</a></li>
                                <li class="nav-item"><a class="nav-link" href="{{ route('restaurant.services.index') }}">Menu &amp; Services</a></li>
                                <li class="nav-item"><a class="nav-link" href="{{ route('restaurant.tables.index') }}">Tables</a></li>
                                <li class="nav-item"><a class="nav-link" href="{{ route('restaurant.reservations.index') }}">Reservations</a></li>
                                <li class="nav-item"><a class="nav-link" href="{{ route('provider.reports') }}">Reports</a></li>
                            @elseif (auth()->user()->serviceProvider?->provider_type === 'transportation_car_rental')
                                <li class="nav-item"><a class="nav-link" href="{{ route('transportation.dashboard') }}">Transportation Dashboard</a></li>
                                <li class="nav-item"><a class="nav-link" href="{{ route('transportation.profile') }}">Profile</a></li>
                                <li class="nav-item"><a class="nav-link" href="{{ route('transportation.services.index') }}">Services</a></li>
                                <li class="nav-item"><a class="nav-link" href="{{ route('transportation.vehicles.index') }}">Vehicles</a></li>
                                <li class="nav-item"><a class="nav-link" href="{{ route('transportation.reservations.index') }}">Reservations</a></li>
                                <li class="nav-item"><a class="nav-link" href="{{ route('provider.reports') }}">Reports</a></li>
                            @elseif (auth()->user()->serviceProvider?->provider_type === 'event_organizer')
                                <li class="nav-item"><a class="nav-link" href="{{ route('event-organizer.dashboard') }}">Event Dashboard</a></li>
                                <li class="nav-item"><a class="nav-link" href="{{ route('event-organizer.events.index') }}">My Events</a></li>
                                <li class="nav-item"><a class="nav-link" href="{{ route('event-organizer.events.bookings') }}">Bookings</a></li>
                                <li class="nav-item"><a class="nav-link" href="{{ route('provider.reports') }}">Reports</a></li>
                            @endif
                        @endif
                        <li class="nav-item"><a class="nav-link" href="{{ route('provider.status') }}">Application Status</a></li>
                    @elseif (auth()->user()->role === 'tourism_bureau_officer')
                        <li class="nav-item"><a class="nav-link" href="{{ route('bureau.dashboard') }}">Bureau Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('bureau.guides.index') }}">Guide Verification</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('bureau.providers.index') }}">Provider Verification</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('bureau.museums.index') }}">Museum Information</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('bureau.reports.index') }}">Reports</a></li>
                    @elseif (auth()->user()->role === 'administrator')
                        <li class="nav-item"><a class="nav-link" href="{{ route('admin.dashboard') }}">Admin Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('admin.providers.index') }}">Providers</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('admin.users.index') }}">Users</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('admin.subscriptions.index') }}">Subscriptions</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('admin.audit.index') }}">Audit</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('admin.reviews.index') }}">Reviews</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('admin.reports.index') }}">Reports</a></li>
                    @endif
                @endauth
            </ul>

            {{-- Right Controls: Dark/Light Theme Toggle + Auth Buttons --}}
            <div class="d-flex align-items-center gap-2 ms-lg-2">
                {{-- Theme Toggle Button --}}
                <button class="nav-theme-toggle-btn d-flex align-items-center justify-content-center" type="button" id="theme-toggle-btn" data-theme-toggle aria-pressed="false" title="Switch to dark mode" aria-label="Switch to dark mode">
                    <svg data-theme-icon="light" class="d-none" aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"></circle><path d="M12 2v2"></path><path d="M12 20v2"></path><path d="m4.93 4.93 1.41 1.41"></path><path d="m17.66 17.66 1.41 1.41"></path><path d="M2 12h2"></path><path d="M20 12h2"></path><path d="m6.34 17.66-1.41 1.41"></path><path d="m19.07 4.93-1.41 1.41"></path></svg>
                    <svg data-theme-icon="dark" aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
                </button>

                @auth
                    @php($unreadNotifications = auth()->user()->notifications()->where('read_status', false)->count())
                    <a class="btn btn-outline-light btn-sm d-flex align-items-center gap-1" href="{{ route('notifications.index') }}" aria-label="Notifications{{ $unreadNotifications ? ', '.$unreadNotifications.' unread' : '' }}">
                        <svg width="13" height="13" fill="currentColor" viewBox="0 0 16 16"><path d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2M8 1.918l-.797.161A4 4 0 0 0 4 6c0 .628-.134 2.197-.459 3.742-.16.767-.376 1.566-.663 2.258h10.244c-.287-.692-.502-1.49-.663-2.258C12.134 8.197 12 6.628 12 6a4 4 0 0 0-3.203-3.92zM14.22 12c.223.447.481.801.78 1H1c.299-.199.557-.553.78-1C2.68 10.2 3 6.88 3 6c0-2.42 1.72-4.44 4.005-4.901a1 1 0 1 1 1.99 0A5 5 0 0 1 13 6c0 .88.32 4.2 1.22 6"/></svg>
                        <span class="d-none d-xl-inline">Alerts</span>
                        @if($unreadNotifications)<span class="badge text-bg-warning ms-1">{{ $unreadNotifications }}</span>@endif
                    </a>
                    <a class="btn btn-outline-light btn-sm" href="{{ route('account') }}">Account</a>
                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                        @csrf
                        <button class="btn btn-outline-danger btn-sm" type="submit">Log out</button>
                    </form>
                @else
                    <a class="btn btn-outline-light btn-sm px-3" href="{{ route('login') }}">Log in</a>
                    <a class="btn btn-warning btn-sm fw-bold px-3 text-dark" href="{{ route('register') }}">Register</a>
                @endauth
            </div>
        </div>
    </div>
</nav>
