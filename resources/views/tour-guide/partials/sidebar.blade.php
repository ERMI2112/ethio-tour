<aside class="card border-0 shadow-sm h-100" aria-label="Tour guide portal navigation">
    <div class="card-body p-3">
        <p class="text-uppercase text-muted small fw-semibold mb-3">Tour Guide Portal</p>
        <div class="list-group list-group-flush">
            <a href="{{ route('tour-guide.dashboard') }}" @class(['list-group-item list-group-item-action', 'active' => request()->routeIs('tour-guide.dashboard')])>
                Dashboard
            </a>
            <a href="{{ route('tour-guide.profile') }}" @class(['list-group-item list-group-item-action', 'active' => request()->routeIs('tour-guide.profile*')])>
                My Profile
            </a>
            <a href="{{ route('tour-guide.availability') }}" @class(['list-group-item list-group-item-action', 'active' => request()->routeIs('tour-guide.availability')])>
                Availability
            </a>
            <a href="{{ route('tour-guide.requests.index') }}" @class(['list-group-item list-group-item-action', 'active' => request()->routeIs('tour-guide.requests.*')])>
                Booking Requests
            </a>
            <a href="{{ route('tour-guide.tours') }}" @class(['list-group-item list-group-item-action', 'active' => request()->routeIs('tour-guide.tours')])>
                My Tours
            </a>
            <a href="{{ route('tour-guide.reviews') }}" @class(['list-group-item list-group-item-action', 'active' => request()->routeIs('tour-guide.reviews')])>
                Reviews
            </a>
            <a href="{{ route('tour-guide.earnings') }}" @class(['list-group-item list-group-item-action', 'active' => request()->routeIs('tour-guide.earnings')])>
                Earnings
            </a>
            <a href="{{ route('provider.reports') }}" @class(['list-group-item list-group-item-action', 'active' => request()->routeIs('provider.reports')])>
                Reports
            </a>
            <a href="{{ route('notifications.index') }}" @class(['list-group-item list-group-item-action', 'active' => request()->routeIs('notifications.*')])>
                Notifications
            </a>
            <a href="{{ route('tour-guide.settings') }}" @class(['list-group-item list-group-item-action', 'active' => request()->routeIs('tour-guide.settings')])>
                Settings
            </a>
            <span class="list-group-item d-flex justify-content-between align-items-center text-muted" data-tour-guide-coming-soon="true">
                Messages <span class="badge text-bg-light border">Coming soon</span>
            </span>
        </div>
    </div>
</aside>
