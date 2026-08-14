<nav class="navbar navbar-expand-lg bg-white border-bottom shadow-sm" aria-label="Primary navigation">
    <div class="container">
        <a class="navbar-brand" href="{{ route('home') }}">Ethio Tour</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#primary-navigation" aria-controls="primary-navigation" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="primary-navigation">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="{{ route('destinations.index') }}">Destinations</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('heritage-sites.index') }}">Heritage Sites</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('tourism-services.index') }}">Tourism Services</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('categories.index') }}">Categories</a></li>
                <li class="nav-item"><x-ui.nav-placeholder label="Map" /></li>
                @auth
                    @if (auth()->user()->role === 'tourist')
                        <li class="nav-item"><a class="nav-link" href="{{ route('tourist.reservations.index') }}">My Bookings</a></li>
                    @else
                        <li class="nav-item"><x-ui.nav-placeholder label="Bookings" /></li>
                    @endif
                    @if (in_array(auth()->user()->role, ['tourist', 'tour_guide', 'service_provider', 'tourism_bureau_officer', 'administrator'], true))
                        <li class="nav-item"><x-ui.nav-placeholder label="Reports" /></li>
                    @endif
                    @if (auth()->user()->role === 'tourist')
                        <li class="nav-item"><x-ui.nav-placeholder label="Tourist Portal" /></li>
                    @elseif (auth()->user()->role === 'tour_guide')
                        <li class="nav-item"><a class="nav-link" href="{{ route('tour-guide.dashboard') }}">Tour Guide Portal</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('tour-guide.profile') }}">My Profile</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('tour-guide.availability') }}">Availability</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('tour-guide.requests.index') }}">Booking Requests</a></li>
                    @elseif (auth()->user()->role === 'service_provider')
                        @if (auth()->user()->serviceProvider?->provider_type === 'hotel')
                            <li class="nav-item"><a class="nav-link" href="{{ route('hotel.dashboard') }}">Hotel Dashboard</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('hotel.profile') }}">Profile</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('hotel.services.index') }}">Room Types</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('hotel.rooms.index') }}">Rooms</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('hotel.reservations.index') }}">Reservations</a></li>
                            <li class="nav-item"><x-ui.nav-placeholder label="Reports" /></li>
                        @else
                            <li class="nav-item"><x-ui.nav-placeholder label="Service Provider Portal" /></li>
                        @endif
                    @elseif (auth()->user()->role === 'tourism_bureau_officer')
                        <li class="nav-item"><x-ui.nav-placeholder label="Tourism Bureau Portal" /></li>
                    @elseif (auth()->user()->role === 'administrator')
                        <li class="nav-item"><x-ui.nav-placeholder label="Administrator Portal" /></li>
                    @endif
                @endauth
            </ul>

            <div class="d-flex align-items-center gap-2">
                <span class="navbar-text text-muted d-none d-xl-inline">Smart Tourism Services</span>
                @auth
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
