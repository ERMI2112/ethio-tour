<aside id="workspace-sidebar" class="workspace-sidebar offcanvas-lg offcanvas-start" tabindex="-1" aria-label="Workspace navigation">
    <div class="offcanvas-header d-lg-none border-bottom">
        <h2 class="h6 mb-0">Workspace navigation</h2>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#workspace-sidebar" aria-label="Close navigation"></button>
    </div>
    <div class="offcanvas-body d-flex flex-column p-3 p-lg-4">
        <div class="workspace-sidebar-heading mb-3">
            <span class="text-uppercase small fw-semibold text-muted">{{ match ($workspaceRole) {
                'administrator' => 'Platform operations',
                'bureau' => 'Governance operations',
                'service_provider' => 'Business operations',
                'tourist' => 'Traveler tools',
                default => 'Workspace',
            } }}</span>
        </div>
        <nav class="nav nav-pills flex-column gap-1" aria-label="Workspace sections">
            @if ($workspaceRole === 'administrator')
                <a class="nav-link" href="{{ route('admin.dashboard') }}" @class(['active' => request()->routeIs('admin.dashboard')])>Dashboard</a>
                <a class="nav-link" href="{{ route('admin.providers.index') }}" @class(['active' => request()->routeIs('admin.providers.*')])>Provider governance</a>
                <a class="nav-link" href="{{ route('admin.users.index') }}" @class(['active' => request()->routeIs('admin.users.*')])>Users</a>
                <a class="nav-link" href="{{ route('admin.subscriptions.index') }}" @class(['active' => request()->routeIs('admin.subscriptions.*')])>Subscriptions</a>
                <a class="nav-link" href="{{ route('admin.audit.index') }}" @class(['active' => request()->routeIs('admin.audit.*')])>Audit log</a>
                <a class="nav-link" href="{{ route('admin.reviews.index') }}" @class(['active' => request()->routeIs('admin.reviews.*')])>Review moderation</a>
                <a class="nav-link" href="{{ route('admin.reports.index') }}" @class(['active' => request()->routeIs('admin.reports.*')])>Reports</a>
                @php($adminUnreadNotifications = auth()->user()->notifications()->where('read_status', false)->count())
                <a class="nav-link d-flex justify-content-between align-items-center" href="{{ route('notifications.index') }}" @class(['active' => request()->routeIs('notifications.*')])><span>Notifications</span>@if($adminUnreadNotifications)<span class="badge text-bg-primary">{{ $adminUnreadNotifications }}</span>@endif</a>
            @elseif ($workspaceRole === 'bureau')
                <a class="nav-link" href="{{ route('bureau.dashboard') }}" @class(['active' => request()->routeIs('bureau.dashboard')])>Dashboard</a>
                <a class="nav-link" href="{{ route('bureau.guides.index') }}" @class(['active' => request()->routeIs('bureau.guides.*')])>Guide verification</a>
                <a class="nav-link" href="{{ route('bureau.providers.index') }}" @class(['active' => request()->routeIs('bureau.providers.*')])>Provider verification</a>
                <a class="nav-link" href="{{ route('bureau.museums.index') }}" @class(['active' => request()->routeIs('bureau.museums.*')])>Museum information</a>
                <a class="nav-link" href="{{ route('bureau.reports.index') }}" @class(['active' => request()->routeIs('bureau.reports.*')])>Reports</a>
                @php($bureauUnreadNotifications = auth()->user()->notifications()->where('read_status', false)->count())
                <a class="nav-link d-flex justify-content-between align-items-center" href="{{ route('notifications.index') }}" @class(['active' => request()->routeIs('notifications.*')])><span>Notifications</span>@if($bureauUnreadNotifications)<span class="badge text-bg-primary">{{ $bureauUnreadNotifications }}</span>@endif</a>
            @elseif ($workspaceRole === 'service_provider')
                @php($providerType = auth()->user()->serviceProvider?->provider_type)
                @php($operational = auth()->user()->serviceProvider?->isOperational())
                @if ($operational && $providerType === 'hotel')
                    <a class="nav-link" href="{{ route('hotel.dashboard') }}" @class(['active' => request()->routeIs('hotel.dashboard')])>Dashboard</a>
                    <a class="nav-link" href="{{ route('hotel.profile') }}" @class(['active' => request()->routeIs('hotel.profile*')])>Profile</a>
                    <a class="nav-link" href="{{ route('hotel.services.index') }}" @class(['active' => request()->routeIs('hotel.services.*')])>Room types</a>
                    <a class="nav-link" href="{{ route('hotel.rooms.index') }}" @class(['active' => request()->routeIs('hotel.rooms.*')])>Rooms</a>
                    <a class="nav-link" href="{{ route('hotel.reservations.index') }}" @class(['active' => request()->routeIs('hotel.reservations.*')])>Reservations</a>
                    <a class="nav-link" href="{{ route('provider.reports') }}" @class(['active' => request()->routeIs('provider.reports')])>Reports</a>
                    @php($hotelUnreadNotifications = auth()->user()->notifications()->where('read_status', false)->count())
                    <a class="nav-link d-flex justify-content-between align-items-center" href="{{ route('notifications.index') }}" @class(['active' => request()->routeIs('notifications.*')])><span>Notifications</span>@if($hotelUnreadNotifications)<span class="badge text-bg-primary">{{ $hotelUnreadNotifications }}</span>@endif</a>
                @elseif ($operational && $providerType === 'restaurant')
                    <a class="nav-link" href="{{ route('restaurant.dashboard') }}" @class(['active' => request()->routeIs('restaurant.dashboard')])>Dashboard</a>
                    <a class="nav-link" href="{{ route('restaurant.profile') }}" @class(['active' => request()->routeIs('restaurant.profile*')])>Profile</a>
                    <a class="nav-link" href="{{ route('restaurant.services.index') }}" @class(['active' => request()->routeIs('restaurant.services.*')])>Menu and services</a>
                    <a class="nav-link" href="{{ route('restaurant.tables.index') }}" @class(['active' => request()->routeIs('restaurant.tables.*')])>Tables</a>
                    <a class="nav-link" href="{{ route('restaurant.reservations.index') }}" @class(['active' => request()->routeIs('restaurant.reservations.*')])>Reservations</a>
                    <a class="nav-link" href="{{ route('provider.reports') }}" @class(['active' => request()->routeIs('provider.reports')])>Reports</a>
                    @php($restaurantUnreadNotifications = auth()->user()->notifications()->where('read_status', false)->count())
                    <a class="nav-link d-flex justify-content-between align-items-center" href="{{ route('notifications.index') }}" @class(['active' => request()->routeIs('notifications.*')])><span>Notifications</span>@if($restaurantUnreadNotifications)<span class="badge text-bg-primary">{{ $restaurantUnreadNotifications }}</span>@endif</a>
                @elseif ($operational && $providerType === 'transportation_car_rental')
                    <a class="nav-link" href="{{ route('transportation.dashboard') }}" @class(['active' => request()->routeIs('transportation.dashboard')])>Dashboard</a>
                    <a class="nav-link" href="{{ route('transportation.profile') }}" @class(['active' => request()->routeIs('transportation.profile*')])>Profile</a>
                    <a class="nav-link" href="{{ route('transportation.services.index') }}" @class(['active' => request()->routeIs('transportation.services.*')])>Services</a>
                    <a class="nav-link" href="{{ route('transportation.vehicles.index') }}" @class(['active' => request()->routeIs('transportation.vehicles.*')])>Vehicles</a>
                    <a class="nav-link" href="{{ route('transportation.reservations.index') }}" @class(['active' => request()->routeIs('transportation.reservations.*')])>Reservations</a>
                    <a class="nav-link" href="{{ route('provider.reports') }}" @class(['active' => request()->routeIs('provider.reports')])>Reports</a>
                    @php($transportUnreadNotifications = auth()->user()->notifications()->where('read_status', false)->count())
                    <a class="nav-link d-flex justify-content-between align-items-center" href="{{ route('notifications.index') }}" @class(['active' => request()->routeIs('notifications.*')])><span>Notifications</span>@if($transportUnreadNotifications)<span class="badge text-bg-primary">{{ $transportUnreadNotifications }}</span>@endif</a>
                @elseif ($operational && $providerType === 'event_organizer')
                    <a class="nav-link" href="{{ route('event-organizer.dashboard') }}" @class(['active' => request()->routeIs('event-organizer.dashboard')])>Dashboard</a>
                    <a class="nav-link" href="{{ route('event-organizer.profile') }}" @class(['active' => request()->routeIs('event-organizer.profile*')])>Profile</a>
                    <a class="nav-link" href="{{ route('event-organizer.events.index') }}" @class(['active' => request()->routeIs('event-organizer.events.*')])>Events</a>
                    <a class="nav-link" href="{{ route('event-organizer.events.bookings') }}" @class(['active' => request()->routeIs('event-organizer.events.bookings')])>Bookings</a>
                    <a class="nav-link" href="{{ route('provider.reports') }}" @class(['active' => request()->routeIs('provider.reports')])>Reports</a>
                @else
                    <a class="nav-link" href="{{ route('provider.status') }}" @class(['active' => request()->routeIs('provider.status')])>Application status</a>
                    <a class="nav-link" href="{{ route('provider.profile.edit') }}" @class(['active' => request()->routeIs('provider.profile.*')])>Business profile</a>
                @endif
            @elseif ($workspaceRole === 'tourist')
                <a class="nav-link" href="{{ route('tourist.reservations.index') }}" @class(['active' => request()->routeIs('tourist.reservations.*')])>My Bookings</a>
                <a class="nav-link" href="{{ route('smart-trip.index') }}" @class(['active' => request()->routeIs('smart-trip.*')])>Smart Trip</a>
            @elseif ($workspaceRole === 'tour_guide')
                <a class="nav-link" href="{{ route('tour-guide.dashboard') }}" @class(['active' => request()->routeIs('tour-guide.dashboard')])>Dashboard</a>
                <a class="nav-link" href="{{ route('tour-guide.profile') }}" @class(['active' => request()->routeIs('tour-guide.profile*')])>My Profile</a>
                <a class="nav-link" href="{{ route('tour-guide.availability') }}" @class(['active' => request()->routeIs('tour-guide.availability')])>Availability</a>
                <a class="nav-link" href="{{ route('tour-guide.requests.index') }}" @class(['active' => request()->routeIs('tour-guide.requests.*')])>Booking Requests</a>
            @endif
        </nav>
        <div class="mt-auto pt-4">
            <a class="nav-link px-0 text-muted" href="{{ route('account') }}">Account settings</a>
            <a class="nav-link px-0 text-muted" href="{{ route('home') }}">View public site</a>
        </div>
    </div>
</aside>
