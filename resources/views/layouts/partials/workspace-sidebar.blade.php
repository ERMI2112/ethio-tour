<aside id="workspace-sidebar" class="workspace-sidebar offcanvas-lg offcanvas-start border-end" tabindex="-1" aria-label="Workspace navigation">
    <div class="offcanvas-header d-lg-none border-bottom p-3">
        <h2 class="h6 mb-0 fw-bold d-flex align-items-center gap-2">
            <i class="bi bi-grid-fill text-success"></i>
            <span>Navigation</span>
        </h2>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#workspace-sidebar" aria-label="Close navigation"></button>
    </div>
    <div class="offcanvas-body d-flex flex-column p-3">
        <div class="workspace-sidebar-heading mb-2 px-2">
            <span class="text-uppercase small fw-bold text-muted" style="letter-spacing: 0.08em; font-size: 0.68rem;">{{ match ($workspaceRole) {
                'administrator' => 'Platform operations',
                'bureau' => 'Governance operations',
                'service_provider' => 'Business operations',
                'tourist' => 'Traveler tools',
                default => 'Workspace',
            } }}</span>
        </div>
        <nav class="nav flex-column gap-1" aria-label="Workspace sections">
            @if ($workspaceRole === 'administrator')
                <a @class(['nav-link', 'active' => request()->routeIs('admin.dashboard')]) href="{{ route('admin.dashboard') }}">
                    <i class="bi bi-grid"></i>
                    <span>Dashboard</span>
                </a>
                <a @class(['nav-link', 'active' => request()->routeIs('admin.providers.*')]) href="{{ route('admin.providers.index') }}">
                    <i class="bi bi-shield-check"></i>
                    <span>Provider governance</span>
                </a>
                <a @class(['nav-link', 'active' => request()->routeIs('admin.guides.*')]) href="{{ route('admin.guides.index') }}">
                    <i class="bi bi-person-badge"></i>
                    <span>Final guide approval</span>
                </a>
                <a @class(['nav-link', 'active' => request()->routeIs('admin.users.*')]) href="{{ route('admin.users.index') }}">
                    <i class="bi bi-people"></i>
                    <span>Users</span>
                </a>
                <a @class(['nav-link', 'active' => request()->routeIs('admin.subscriptions.*')]) href="{{ route('admin.subscriptions.index') }}">
                    <i class="bi bi-credit-card"></i>
                    <span>Subscriptions</span>
                </a>
                <a @class(['nav-link', 'active' => request()->routeIs('admin.audit.*')]) href="{{ route('admin.audit.index') }}">
                    <i class="bi bi-terminal"></i>
                    <span>Audit log</span>
                </a>
                <a @class(['nav-link', 'active' => request()->routeIs('admin.reviews.*')]) href="{{ route('admin.reviews.index') }}">
                    <i class="bi bi-star"></i>
                    <span>Review moderation</span>
                </a>
                <a @class(['nav-link', 'active' => request()->routeIs('admin.reports.*')]) href="{{ route('admin.reports.index') }}">
                    <i class="bi bi-graph-up"></i>
                    <span>Reports</span>
                </a>
                @php($adminUnreadNotifications = auth()->user()->notifications()->where('read_status', false)->count())
                <a @class(['nav-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('notifications.*')]) href="{{ route('notifications.index') }}">
                    <span class="d-flex align-items-center gap-2">
                        <i class="bi bi-bell"></i>
                        <span>Notifications</span>
                    </span>
                    @if($adminUnreadNotifications)
                        <span class="badge bg-danger text-white rounded-pill px-2 py-0.5" style="font-size: 0.7rem;">{{ $adminUnreadNotifications }}</span>
                    @endif
                </a>
            @elseif ($workspaceRole === 'bureau')
                <a @class(['nav-link', 'active' => request()->routeIs('bureau.dashboard')]) href="{{ route('bureau.dashboard') }}"><i class="bi bi-grid"></i><span>Dashboard</span></a>
                <a @class(['nav-link', 'active' => request()->routeIs('bureau.guides.*')]) href="{{ route('bureau.guides.index') }}"><i class="bi bi-person-badge"></i><span>Guide verification</span></a>
                <a @class(['nav-link', 'active' => request()->routeIs('bureau.providers.*')]) href="{{ route('bureau.providers.index') }}"><i class="bi bi-shop"></i><span>Provider verification</span></a>
                <a @class(['nav-link', 'active' => request()->routeIs('bureau.museums.*')]) href="{{ route('bureau.museums.index') }}"><i class="bi bi-bank"></i><span>Museum information</span></a>
                <a @class(['nav-link', 'active' => request()->routeIs('bureau.reports.*')]) href="{{ route('bureau.reports.index') }}"><i class="bi bi-graph-up"></i><span>Reports</span></a>
                @php($bureauUnreadNotifications = auth()->user()->notifications()->where('read_status', false)->count())
                <a @class(['nav-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('notifications.*')]) href="{{ route('notifications.index') }}"><span class="d-flex align-items-center gap-2"><i class="bi bi-bell"></i><span>Notifications</span></span>@if($bureauUnreadNotifications)<span class="badge bg-danger rounded-pill">{{ $bureauUnreadNotifications }}</span>@endif</a>
            @elseif ($workspaceRole === 'service_provider')
                @php($providerType = auth()->user()->serviceProvider?->provider_type)
                @php($operational = auth()->user()->serviceProvider?->isOperational())
                @if ($operational && $providerType === 'hotel')
                    <a @class(['nav-link', 'active' => request()->routeIs('hotel.dashboard')]) href="{{ route('hotel.dashboard') }}"><i class="bi bi-grid"></i><span>Dashboard</span></a>
                    <a @class(['nav-link', 'active' => request()->routeIs('hotel.profile*')]) href="{{ route('hotel.profile') }}"><i class="bi bi-building"></i><span>Profile</span></a>
                    <a @class(['nav-link', 'active' => request()->routeIs('hotel.services.*')]) href="{{ route('hotel.services.index') }}"><i class="bi bi-door-open"></i><span>Room types</span></a>
                    <a @class(['nav-link', 'active' => request()->routeIs('hotel.rooms.*')]) href="{{ route('hotel.rooms.index') }}"><i class="bi bi-key"></i><span>Rooms</span></a>
                    <a @class(['nav-link', 'active' => request()->routeIs('hotel.reservations.*')]) href="{{ route('hotel.reservations.index') }}"><i class="bi bi-calendar-check"></i><span>Reservations</span></a>
                    <a @class(['nav-link', 'active' => request()->routeIs('provider.reports')]) href="{{ route('provider.reports') }}"><i class="bi bi-graph-up"></i><span>Reports</span></a>
                    @php($hotelUnreadNotifications = auth()->user()->notifications()->where('read_status', false)->count())
                    <a @class(['nav-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('notifications.*')]) href="{{ route('notifications.index') }}"><span class="d-flex align-items-center gap-2"><i class="bi bi-bell"></i><span>Notifications</span></span>@if($hotelUnreadNotifications)<span class="badge bg-danger rounded-pill">{{ $hotelUnreadNotifications }}</span>@endif</a>
                @elseif ($operational && $providerType === 'restaurant')
                    <a @class(['nav-link', 'active' => request()->routeIs('restaurant.dashboard')]) href="{{ route('restaurant.dashboard') }}"><i class="bi bi-grid"></i><span>Dashboard</span></a>
                    <a @class(['nav-link', 'active' => request()->routeIs('restaurant.services.*')]) href="{{ route('restaurant.services.index') }}"><i class="bi bi-egg-fried"></i><span>Menu and service offerings</span></a>
                    <a @class(['nav-link', 'active' => request()->routeIs('restaurant.reservations.*')]) href="{{ route('restaurant.reservations.index') }}"><i class="bi bi-calendar-check"></i><span>Reservations</span></a>
                    <a @class(['nav-link', 'active' => request()->routeIs('restaurant.tables.*')]) href="{{ route('restaurant.tables.index') }}"><i class="bi bi-layout-three-columns"></i><span>Table inventory</span></a>
                    <a @class(['nav-link', 'active' => request()->routeIs('restaurant.reviews.*')]) href="{{ route('restaurant.reviews.index') }}"><i class="bi bi-star"></i><span>Guest feedback</span></a>
                    <a @class(['nav-link', 'active' => request()->routeIs('restaurant.revenue.*')]) href="{{ route('restaurant.revenue.index') }}"><i class="bi bi-cash-stack"></i><span>Revenue</span></a>
                    <a @class(['nav-link', 'active' => request()->routeIs('provider.reports')]) href="{{ route('provider.reports') }}"><i class="bi bi-graph-up"></i><span>Reports</span></a>
                    @php($restaurantUnreadNotifications = auth()->user()->notifications()->where('read_status', false)->count())
                    <a @class(['nav-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('notifications.*')]) href="{{ route('notifications.index') }}"><span class="d-flex align-items-center gap-2"><i class="bi bi-bell"></i><span>Notifications</span></span>@if($restaurantUnreadNotifications)<span class="badge bg-danger rounded-pill">{{ $restaurantUnreadNotifications }}</span>@endif</a>
                    <div class="mt-3 p-2.5 rounded-3 bg-light border text-start d-none d-lg-block">
                        <div class="small fw-bold text-dark" style="font-size: 0.72rem; letter-spacing: 0.05em;">GONDAR PILOT</div>
                        <div class="text-muted" style="font-size: 0.68rem; line-height: 1.3;">Government Supervision Integrated Gateway</div>
                    </div>
                @elseif ($operational && $providerType === 'transportation_car_rental')
                    <a @class(['nav-link', 'active' => request()->routeIs('transportation.dashboard')]) href="{{ route('transportation.dashboard') }}"><i class="bi bi-grid"></i><span>Dashboard</span></a>
                    <a @class(['nav-link', 'active' => request()->routeIs('transportation.profile*')]) href="{{ route('transportation.profile') }}"><i class="bi bi-truck"></i><span>Profile</span></a>
                    <a @class(['nav-link', 'active' => request()->routeIs('transportation.services.*')]) href="{{ route('transportation.services.index') }}"><i class="bi bi-signpost-split"></i><span>Services</span></a>
                    <a @class(['nav-link', 'active' => request()->routeIs('transportation.vehicles.*')]) href="{{ route('transportation.vehicles.index') }}"><i class="bi bi-car-front"></i><span>Vehicles</span></a>
                    <a @class(['nav-link', 'active' => request()->routeIs('transportation.reservations.*')]) href="{{ route('transportation.reservations.index') }}"><i class="bi bi-calendar-check"></i><span>Reservations</span></a>
                    <a @class(['nav-link', 'active' => request()->routeIs('provider.reports')]) href="{{ route('provider.reports') }}"><i class="bi bi-graph-up"></i><span>Reports</span></a>
                    @php($transportUnreadNotifications = auth()->user()->notifications()->where('read_status', false)->count())
                    <a @class(['nav-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('notifications.*')]) href="{{ route('notifications.index') }}"><span class="d-flex align-items-center gap-2"><i class="bi bi-bell"></i><span>Notifications</span></span>@if($transportUnreadNotifications)<span class="badge bg-danger rounded-pill">{{ $transportUnreadNotifications }}</span>@endif</a>
                @elseif ($operational && $providerType === 'event_organizer')
                    <a @class(['nav-link', 'active' => request()->routeIs('event-organizer.dashboard')]) href="{{ route('event-organizer.dashboard') }}"><i class="bi bi-grid"></i><span>Dashboard</span></a>
                    <a @class(['nav-link', 'active' => request()->routeIs('event-organizer.profile*')]) href="{{ route('event-organizer.profile') }}"><i class="bi bi-calendar-event"></i><span>Profile</span></a>
                    <a @class(['nav-link', 'active' => request()->routeIs('event-organizer.events.*')]) href="{{ route('event-organizer.events.index') }}"><i class="bi bi-ticket-perforated"></i><span>Events</span></a>
                    <a @class(['nav-link', 'active' => request()->routeIs('event-organizer.events.bookings')]) href="{{ route('event-organizer.events.bookings') }}"><i class="bi bi-people"></i><span>Bookings</span></a>
                    <a @class(['nav-link', 'active' => request()->routeIs('provider.reports')]) href="{{ route('provider.reports') }}"><i class="bi bi-graph-up"></i><span>Reports</span></a>
                    @php($eventUnreadNotifications = auth()->user()->notifications()->where('read_status', false)->count())
                    <a @class(['nav-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('notifications.*')]) href="{{ route('notifications.index') }}"><span class="d-flex align-items-center gap-2"><i class="bi bi-bell"></i><span>Notifications</span></span>@if($eventUnreadNotifications)<span class="badge bg-danger rounded-pill">{{ $eventUnreadNotifications }}</span>@endif</a>
                @else
                    <a @class(['nav-link', 'active' => request()->routeIs('provider.status')]) href="{{ route('provider.status') }}"><i class="bi bi-hourglass-split"></i><span>Application status</span></a>
                    <a @class(['nav-link', 'active' => request()->routeIs('provider.profile.*')]) href="{{ route('provider.profile.edit') }}"><i class="bi bi-pencil-square"></i><span>Business profile</span></a>
                    <a class="nav-link px-2 text-muted d-flex align-items-center gap-2 small mt-2" href="{{ route('account') }}"><i class="bi bi-gear"></i><span>Account settings</span></a>
                    <div class="mt-3 p-2.5 rounded-3 bg-warning-subtle border border-warning-subtle text-start d-none d-lg-block">
                        <div class="small fw-bold text-dark d-flex align-items-center gap-1" style="font-size: 0.72rem;"><i class="bi bi-info-circle-fill text-warning"></i> ONBOARDING</div>
                        <div class="text-muted" style="font-size: 0.68rem; line-height: 1.4;">Complete your Business Profile to proceed with Bureau verification.</div>
                    </div>
                @endif
            @elseif ($workspaceRole === 'tourist')
                <a @class(['nav-link', 'active' => request()->routeIs('tourist.dashboard')]) href="{{ route('tourist.dashboard') }}"><i class="bi bi-grid"></i><span>Dashboard</span></a>
                <a class="nav-link" href="{{ route('destinations.index') }}"><i class="bi bi-compass"></i><span>Explore Ethiopia</span></a>
                <a class="nav-link" href="{{ route('map') }}"><i class="bi bi-map"></i><span>Discovery map</span></a>
                <a @class(['nav-link', 'active' => request()->routeIs('tourist.reservations.*')]) href="{{ route('tourist.reservations.index') }}"><i class="bi bi-journal-bookmark"></i><span>My Bookings</span></a>
                @php($touristUnreadNotifications = auth()->user()->notifications()->where('read_status', false)->count())
                <a @class(['nav-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('notifications.*')]) href="{{ route('notifications.index') }}"><span class="d-flex align-items-center gap-2"><i class="bi bi-bell"></i><span>Notifications</span></span>@if($touristUnreadNotifications)<span class="badge bg-danger rounded-pill">{{ $touristUnreadNotifications }}</span>@endif</a>
                <a @class(['nav-link', 'active' => request()->routeIs('tourist.reviews.*')]) href="{{ route('tourist.reviews.index') }}"><i class="bi bi-star"></i><span>My Reviews</span></a>
                <a @class(['nav-link', 'active' => request()->routeIs('smart-trip.*')]) href="{{ route('smart-trip.index') }}"><i class="bi bi-map"></i><span>Smart Trip</span></a>
                <a @class(['nav-link', 'active' => request()->routeIs('tourist.profile*')]) href="{{ route('tourist.profile') }}"><i class="bi bi-person"></i><span>My Profile</span></a>
            @elseif ($workspaceRole === 'tour_guide')
                <a @class(['nav-link', 'active' => request()->routeIs('tour-guide.dashboard')]) href="{{ route('tour-guide.dashboard') }}"><i class="bi bi-grid"></i><span>Dashboard</span></a>
                <a @class(['nav-link', 'active' => request()->routeIs('tour-guide.profile*')]) href="{{ route('tour-guide.profile') }}"><i class="bi bi-person-badge"></i><span>My Profile</span></a>
                <a @class(['nav-link', 'active' => request()->routeIs('tour-guide.availability')]) href="{{ route('tour-guide.availability') }}"><i class="bi bi-calendar-event"></i><span>Availability</span></a>
                <a @class(['nav-link', 'active' => request()->routeIs('tour-guide.requests.*')]) href="{{ route('tour-guide.requests.index') }}"><i class="bi bi-inbox"></i><span>Booking Requests</span></a>
                <a @class(['nav-link', 'active' => request()->routeIs('tour-guide.tours') || request()->routeIs('tour-guide.packages.*')]) href="{{ route('tour-guide.tours') }}"><i class="bi bi-map"></i><span>My Tours</span></a>
                <a @class(['nav-link', 'active' => request()->routeIs('tour-guide.reviews')]) href="{{ route('tour-guide.reviews') }}"><i class="bi bi-star"></i><span>Reviews</span></a>
                <a @class(['nav-link', 'active' => request()->routeIs('tour-guide.earnings')]) href="{{ route('tour-guide.earnings') }}"><i class="bi bi-wallet2"></i><span>Earnings</span></a>
                @php($guideUnreadNotifications = auth()->user()->notifications()->where('read_status', false)->count())
                <a @class(['nav-link d-flex justify-content-between align-items-center', 'active' => request()->routeIs('notifications.*')]) href="{{ route('notifications.index') }}"><span class="d-flex align-items-center gap-2"><i class="bi bi-bell"></i><span>Notifications</span></span>@if($guideUnreadNotifications)<span class="badge bg-danger rounded-pill">{{ $guideUnreadNotifications }}</span>@endif</a>
                <a @class(['nav-link', 'active' => request()->routeIs('tour-guide.settings')]) href="{{ route('tour-guide.settings') }}"><i class="bi bi-gear"></i><span>Settings</span></a>
            @endif
        </nav>
        <div class="mt-auto pt-3 border-top">
            @if ($workspaceRole !== 'tourist')
                <a class="nav-link px-2 text-muted d-flex align-items-center gap-2 small" href="{{ route('account') }}">
                    <i class="bi bi-gear"></i>
                    <span>Account settings</span>
                </a>
            @endif
            @if ($workspaceRole === 'tourist')
                <a class="nav-link px-2 text-muted d-flex align-items-center gap-2 small" href="{{ route('account') }}">
                    <i class="bi bi-person-gear"></i>
                    <span>Account settings</span>
                </a>
                <a class="nav-link px-2 text-muted d-flex align-items-center gap-2 small" href="{{ route('home') }}">
                    <i class="bi bi-compass"></i>
                    <span>View Public Site</span>
                </a>
            @endif
        </div>
    </div>
</aside>
