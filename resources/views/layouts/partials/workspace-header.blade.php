<header class="workspace-header border-bottom" aria-label="{{ $workspaceLabel }} header">
    <div class="container-fluid px-3 px-lg-4 py-2">
        <div class="d-flex align-items-center gap-3">
            @if (!isset($workspaceUsesLegacyGuideSidebar) || ! $workspaceUsesLegacyGuideSidebar)
                <button class="btn btn-outline-secondary d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#workspace-sidebar" aria-controls="workspace-sidebar" aria-label="Open workspace navigation">
                    <span aria-hidden="true">☰</span>
                </button>
            @endif
            <a class="navbar-brand mb-0" href="{{ route('home') }}">Ethio Tour</a>
            <span class="workspace-header-divider d-none d-md-inline" aria-hidden="true"></span>
            <span class="workspace-header-label d-none d-md-inline">{{ $workspaceLabel }}</span>
            <div class="ms-auto d-flex align-items-center gap-2">
                <a class="btn btn-sm btn-outline-secondary" href="{{ route('home') }}">View Public Site</a>
                @php($unreadNotifications = auth()->user()->notifications()->where('read_status', false)->count())
                <a class="btn btn-sm btn-outline-primary" href="{{ route('notifications.index') }}" aria-label="Notifications{{ $unreadNotifications ? ', '.$unreadNotifications.' unread' : '' }}">
                    <span aria-hidden="true">🔔</span><span class="d-none d-sm-inline"> Notifications</span>@if($unreadNotifications) <span class="badge text-bg-primary">{{ $unreadNotifications }}</span>@endif
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
