<header class="workspace-header border-bottom" aria-label="{{ $workspaceLabel }} header">
    <div class="container-fluid px-3 px-lg-4 py-2">
        <div class="d-flex align-items-center gap-3">
            @if (!isset($workspaceUsesLegacyGuideSidebar) || ! $workspaceUsesLegacyGuideSidebar)
                <button class="btn btn-outline-secondary d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#workspace-sidebar" aria-controls="workspace-sidebar" aria-label="Open workspace navigation">
                    <span aria-hidden="true">☰</span>
                </button>
            @endif
            <a class="navbar-brand mb-0" href="{{ route($workspaceDashboardRoute) }}" aria-label="{{ $workspaceLabel }} home">Ethio Tour</a>
            <span class="workspace-header-divider d-none d-md-inline" aria-hidden="true"></span>
            <span class="workspace-header-label d-none d-md-inline">{{ $workspaceLabel }}</span>
            <div class="ms-auto d-flex align-items-center gap-2">
                @if ($workspaceRole === 'tourist')
                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('home') }}">View Public Site</a>
                @endif
                <button class="workspace-theme-toggle btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-2" type="button" data-theme-toggle aria-pressed="false" aria-label="Switch to dark mode" title="Switch to dark mode">
                    <svg data-theme-icon="light" class="d-none" aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"></circle><path d="M12 2v2"></path><path d="M12 20v2"></path><path d="m4.93 4.93 1.41 1.41"></path><path d="m17.66 17.66 1.41 1.41"></path><path d="M2 12h2"></path><path d="M20 12h2"></path><path d="m6.34 17.66-1.41 1.41"></path><path d="m19.07 4.93-1.41 1.41"></path></svg>
                    <svg data-theme-icon="dark" aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
                    <span class="d-none d-md-inline" data-theme-label>Dark mode</span>
                </button>
                @php($unreadNotifications = auth()->user()->notifications()->where('read_status', false)->count())
                <a class="btn btn-sm btn-outline-primary" href="{{ route('notifications.index') }}" aria-label="Notifications{{ $unreadNotifications ? ', '.$unreadNotifications.' unread' : '' }}">
                    <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg><span class="d-none d-sm-inline"> Notifications</span>@if($unreadNotifications) <span class="badge text-bg-primary">{{ $unreadNotifications }}</span>@endif
                </a>
                <a class="btn btn-sm btn-outline-secondary d-none d-sm-inline" href="{{ route('account') }}">Account</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-sm btn-outline-danger" type="submit">Log out</button>
                </form>
            </div>
        </div>
    </div>
</header>
