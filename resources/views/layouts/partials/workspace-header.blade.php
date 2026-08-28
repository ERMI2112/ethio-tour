<header class="workspace-header border-bottom sticky-top bg-white" aria-label="{{ $workspaceLabel }} header" style="height: 58px; z-index: 1020;">
    <div class="container-fluid px-3 px-lg-4 h-100">
        <div class="d-flex align-items-center justify-content-between h-100 gap-3">
            {{-- Brand & Workspace Label --}}
            <div class="d-flex align-items-center gap-2.5">
                @if (!isset($workspaceUsesLegacyGuideSidebar) || ! $workspaceUsesLegacyGuideSidebar)
                    <button class="btn btn-sm btn-light border d-lg-none rounded-2 p-1.5" type="button" data-bs-toggle="offcanvas" data-bs-target="#workspace-sidebar" aria-controls="workspace-sidebar" aria-label="Open workspace navigation">
                        <i class="bi bi-list fs-5"></i>
                    </button>
                @endif

                <a class="navbar-brand d-flex align-items-center gap-2 text-decoration-none m-0" href="{{ route($workspaceDashboardRoute) }}" aria-label="{{ $workspaceLabel }} home">
                    <svg width="24" height="24" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="24" cy="24" r="22" fill="#062133" stroke="#e5a919" stroke-width="2"/>
                        <circle cx="24" cy="24" r="14" fill="#0b5e42"/>
                        <circle cx="24" cy="24" r="6" fill="#e5a919"/>
                    </svg>
                    <span class="fw-bold fs-6 text-dark" style="font-family: var(--font-display); letter-spacing: -0.02em;">Ethio Tour</span>
                </a>

                <span class="text-muted opacity-50 d-none d-md-inline" style="font-size: 0.9rem;">/</span>
                <span class="d-none d-md-inline small fw-semibold text-secondary" style="font-size: 0.82rem;">{{ $workspaceLabel }}</span>

                @if($workspaceRole === 'administrator')
                    <span class="d-none d-xl-inline-flex align-items-center gap-1.5 badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 ms-1" style="font-size: 0.72rem; font-weight: 600;">
                        <span class="spinner-grow spinner-grow-sm text-success" style="width: 6px; height: 6px;" role="status"></span>
                        <span>Operational</span>
                    </span>
                @endif
            </div>

            {{-- Right Controls --}}
            <div class="d-flex align-items-center gap-2">
                {{-- Dark Mode Toggle Button --}}
                <button class="workspace-theme-toggle btn btn-sm btn-light border rounded-3 d-inline-flex align-items-center justify-content-center gap-1.5 px-2.5" type="button" data-theme-toggle aria-pressed="false" aria-label="Switch to dark mode" title="Switch to dark mode" style="height: 36px;">
                    <svg data-theme-icon="light" class="d-none" aria-hidden="true" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"></circle><path d="M12 2v2"></path><path d="M12 20v2"></path><path d="m4.93 4.93 1.41 1.41"></path><path d="m17.66 17.66 1.41 1.41"></path><path d="M2 12h2"></path><path d="M20 12h2"></path><path d="m6.34 17.66-1.41 1.41"></path><path d="m19.07 4.93-1.41 1.41"></path></svg>
                    <svg data-theme-icon="dark" aria-hidden="true" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
                    <span class="d-none d-md-inline small text-secondary" data-theme-label>Dark mode</span>
                </button>

                {{-- Notifications --}}
                @php($unreadNotifications = auth()->user()->notifications()->where('read_status', false)->count())
                <a class="btn btn-sm btn-light border rounded-3 position-relative d-inline-flex align-items-center justify-content-center text-secondary" href="{{ route('notifications.index') }}" aria-label="Notifications{{ $unreadNotifications ? ', '.$unreadNotifications.' unread' : '' }}" style="width: 36px; height: 36px;">
                    <i class="bi bi-bell fs-6"></i>
                    @if($unreadNotifications)
                        <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle">
                            <span class="visually-hidden">New alerts</span>
                        </span>
                    @endif
                </a>

                {{-- Logout --}}
                <form method="POST" action="{{ route('logout') }}" class="d-inline m-0">
                    @csrf
                    <button class="btn btn-sm btn-light border rounded-3 px-3 py-1.5 text-secondary d-inline-flex align-items-center gap-1.5 small fw-semibold" type="submit">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Log out</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
