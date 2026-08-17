<nav class="navbar navbar-expand-lg bg-white border-bottom shadow-sm" aria-label="Primary navigation">
    <div class="container">
        <a class="navbar-brand" href="{{ route('home') }}">Ethio Tour</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#primary-navigation" aria-controls="primary-navigation" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="primary-navigation">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Explore Ethiopia</a>
                    <ul class="dropdown-menu">
                        <li><h6 class="dropdown-header">Places and heritage</h6></li>
                        <li><a class="dropdown-item" href="{{ route('destinations.index') }}">Destinations</a></li>
                        <li><a class="dropdown-item" href="{{ route('heritage-sites.index') }}">Heritage Sites</a></li>
                        <li><a class="dropdown-item" href="{{ route('museums.index') }}">Museums</a></li>
                        <li><a class="dropdown-item" href="{{ route('categories.index') }}">Categories</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><x-ui.nav-placeholder label="National Parks (coming soon)" /></li>
                        <li><x-ui.nav-placeholder label="World Heritage (coming soon)" /></li>
                        <li><x-ui.nav-placeholder label="Explore on Map (coming soon)" /></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Things to Do</a>
                    <ul class="dropdown-menu">
                        <li><h6 class="dropdown-header">Experiences</h6></li>
                        <li><a class="dropdown-item" href="{{ route('tour-guides.index') }}">Tour Guides</a></li>
                        <li><a class="dropdown-item" href="{{ route('events.index') }}">Cultural Experiences</a></li>
                        <li><a class="dropdown-item" href="{{ route('tourism-services.index') }}">Tours &amp; Activities</a></li>
                        <li><x-ui.nav-placeholder label="Nature &amp; Wildlife (coming soon)" /></li>
                        <li><x-ui.nav-placeholder label="Adventure &amp; Outdoors (coming soon)" /></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Stay &amp; Eat</a>
                    <ul class="dropdown-menu">
                        <li><h6 class="dropdown-header">Accommodation and dining</h6></li>
                        <li><a class="dropdown-item" href="{{ route('tourism-services.index') }}">Hotels &amp; Restaurants</a></li>
                        <li><a class="dropdown-item" href="{{ route('tourism-services.index') }}">Tourism Services</a></li>
                        <li><x-ui.nav-placeholder label="Food &amp; Coffee (coming soon)" /></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Travel &amp; Transport</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('transportation.index') }}">Transportation</a></li>
                        <li><a class="dropdown-item" href="{{ route('transportation.index') }}">Car Rental</a></li>
                        <li><x-ui.nav-placeholder label="Travel Information (coming soon)" /></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Events</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('events.index') }}">Events &amp; Festivals</a></li>
                        <li><a class="dropdown-item" href="{{ route('events.index') }}">Cultural Events</a></li>
                        <li><a class="dropdown-item" href="{{ route('events.index') }}">Upcoming Events</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Plan Your Trip</a>
                    <ul class="dropdown-menu">
                        <li><x-ui.nav-placeholder label="Trip Planner (coming soon)" /></li>
                        <li><x-ui.nav-placeholder label="Travel Tips (coming soon)" /></li>
                        <li><x-ui.nav-placeholder label="Practical Information (coming soon)" /></li>
                    </ul>
                </li>
                <li class="nav-item"><a class="nav-link" href="{{ route('map') }}">Map</a></li>
                @auth
                    @if (auth()->user()->role === 'tourist')
                        <li class="nav-item"><a class="nav-link" href="{{ route('tourist.reservations.index') }}">My Bookings</a></li>
                    @endif
                    @if (auth()->user()->role === 'tourist')
                        {{-- Tourist actions are exposed through My Bookings and Smart Trip. --}}
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

            <div class="d-flex align-items-center gap-2">
                <a class="nav-link text-nowrap" href="{{ route('smart-trip.index') }}">Smart Trip</a>
                <a class="btn btn-outline-secondary btn-sm" href="{{ route('search') }}">Search</a>
                @auth
                    @php($unreadNotifications = auth()->user()->notifications()->where('read_status', false)->count())
                    <a class="btn btn-outline-primary btn-sm" href="{{ route('notifications.index') }}" aria-label="Notifications{{ $unreadNotifications ? ', '.$unreadNotifications.' unread' : '' }}">Notifications @if($unreadNotifications)<span class="badge text-bg-primary">{{ $unreadNotifications }}</span>@endif</a>
                    <a class="btn btn-outline-secondary btn-sm" href="{{ route('account') }}">Account</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="btn btn-outline-danger btn-sm" type="submit">Log out</button>
                    </form>
                @else
                    <a class="btn btn-outline-primary btn-sm" href="{{ route('login') }}">Log in</a>
                    <a class="btn btn-primary btn-sm" href="{{ route('register') }}">Register</a>
                @endauth
            </div>
        </div>
    </div>
</nav>
