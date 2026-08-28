<aside id="workspace-sidebar" class="tour-guide-sidebar workspace-sidebar offcanvas-lg offcanvas-start" tabindex="-1" aria-label="Tour guide portal navigation">
    <div class="offcanvas-header d-lg-none border-bottom p-3">
        <h2 class="h6 mb-0 fw-bold d-flex align-items-center gap-2"><i class="bi bi-compass text-success" aria-hidden="true"></i>Tour Guide Portal</h2>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#workspace-sidebar" aria-label="Close navigation"></button>
    </div>
    <div class="offcanvas-body d-flex flex-column p-3">
        <p class="text-uppercase text-muted small fw-semibold mb-3">Tour Guide Portal</p>
        <nav class="list-group list-group-flush" aria-label="Tour guide sections">
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
        </nav>
    </div>
</aside>
