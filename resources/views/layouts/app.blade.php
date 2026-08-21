<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name')) | {{ config('app.name') }}</title>
    @yield('meta')
    <script>
        (function() {
            var theme = localStorage.getItem('ethio_tour_theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', theme);
        })();
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400;1,600&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    @vite(['resources/css/app.css', 'resources/scss/app.scss', 'resources/js/app.js'])
</head>
@php
    $workspaceRole = null;
    $workspaceLabel = null;
    $workspaceDashboardRoute = null;
    $workspaceUsesLegacyGuideSidebar = false;

    if (auth()->check()) {
        $userRole = auth()->user()->role;
        $workspaceRoute = fn (array $patterns): bool => request()->routeIs(...$patterns);

        if ($userRole === 'administrator' && $workspaceRoute(['admin.*', 'notifications.*', 'account', 'password.confirm'])) {
            $workspaceRole = 'administrator';
            $workspaceLabel = 'Admin Dashboard';
            $workspaceDashboardRoute = 'admin.dashboard';
        } elseif ($userRole === 'tourism_bureau_officer' && $workspaceRoute(['bureau.*', 'notifications.*', 'account', 'password.confirm'])) {
            $workspaceRole = 'bureau';
            $workspaceLabel = 'Bureau Dashboard';
            $workspaceDashboardRoute = 'bureau.dashboard';
        } elseif ($userRole === 'tour_guide' && $workspaceRoute(['tour-guide.*', 'notifications.*', 'account', 'password.confirm'])) {
            $workspaceRole = 'tour_guide';
            $workspaceLabel = 'Tour Guide Portal';
            $workspaceDashboardRoute = 'tour-guide.dashboard';
            $workspaceUsesLegacyGuideSidebar = request()->routeIs('tour-guide.*');
        } elseif ($userRole === 'service_provider' && $workspaceRoute(['provider.*', 'hotel.*', 'restaurant.*', 'transportation.*', 'event-organizer.*', 'notifications.*', 'account', 'password.confirm'])) {
            $workspaceRole = 'service_provider';
            $workspaceLabel = auth()->user()->serviceProvider ? 'Provider Workspace' : 'Application Status';
            $workspaceDashboardRoute = match (auth()->user()->serviceProvider?->provider_type) {
                'hotel' => 'hotel.dashboard',
                'restaurant' => 'restaurant.dashboard',
                'transportation_car_rental' => 'transportation.dashboard',
                'event_organizer' => 'event-organizer.dashboard',
                default => 'provider.status',
            };
        } elseif ($userRole === 'tourist' && $workspaceRoute(['tourist.*', 'smart-trip.*', 'notifications.*', 'account', 'password.confirm'])) {
            $workspaceRole = 'tourist';
            $workspaceLabel = 'Traveler Workspace';
            $workspaceDashboardRoute = 'tourist.dashboard';
        }
    }
@endphp

<body class="d-flex flex-column {{ $workspaceRole ? 'workspace-body' : '' }}">
    @if ($workspaceRole)
        @include('layouts.partials.workspace-header', ['workspaceLabel' => $workspaceLabel, 'workspaceRole' => $workspaceRole, 'workspaceDashboardRoute' => $workspaceDashboardRoute, 'workspaceUsesLegacyGuideSidebar' => $workspaceUsesLegacyGuideSidebar])
        <div class="workspace-layout flex-grow-1">
            @unless ($workspaceUsesLegacyGuideSidebar)
                @include('layouts.partials.workspace-sidebar', ['workspaceRole' => $workspaceRole])
            @endunless
            <main class="workspace-content flex-grow-1">
                @include('layouts.partials.flash-messages')
                @unless (request()->routeIs($workspaceDashboardRoute))
                    <div class="workspace-breadcrumb px-3 px-lg-4 pt-3">
                        <nav aria-label="Workspace breadcrumb">
                            <ol class="breadcrumb small mb-0">
                                <li class="breadcrumb-item"><a href="{{ route($workspaceDashboardRoute) }}">{{ $workspaceLabel }}</a></li>
                                <li class="breadcrumb-item active" aria-current="page">@yield('title')</li>
                            </ol>
                        </nav>
                    </div>
                @endunless
                @yield('content')
            </main>
        </div>
    @else
        @include('layouts.partials.navigation')
        <main class="flex-grow-1">
            @include('layouts.partials.flash-messages')
            @if (request()->routeIs('destinations.*'))
                <div class="container pt-4"><x-ui.public-breadcrumbs :group="null" section="Destinations" section-route="{{ route('destinations.index') }}" :current="request()->routeIs('destinations.show') ? ($destination->name ?? null) : null" /></div>
            @elseif (request()->routeIs('heritage-sites.*'))
                <div class="container pt-4"><x-ui.public-breadcrumbs group="Things to Do" section="Culture &amp; Heritage" section-route="{{ route('heritage-sites.index') }}" :current="request()->routeIs('heritage-sites.show') ? ($heritageSite->heritage_type ?? null) : null" /></div>
            @elseif (request()->routeIs('museums.*'))
                <div class="container pt-4"><x-ui.public-breadcrumbs group="Plan Your Trip" section="Museums" section-route="{{ route('museums.index') }}" :current="request()->routeIs('museums.show') ? ($museum->museum_name ?? null) : null" /></div>
            @elseif (request()->routeIs('tour-guides.*'))
                <div class="container pt-4"><x-ui.public-breadcrumbs group="Things to Do" section="Tour Guides" section-route="{{ route('tour-guides.index') }}" :current="request()->routeIs('tour-guides.show') ? 'Tour Guide Profile' : null" /></div>
            @elseif (request()->routeIs('tourism-services.*'))
                @php
                    $tourismBreadcrumbSection = match (request()->routeIs('tourism-services.show') ? $tourismService->serviceProvider?->provider_type : request('provider_type')) {
                        'hotel' => 'Hotels',
                        'restaurant' => 'Restaurants',
                        default => 'Tourism Services',
                    };
                @endphp
                <div class="container pt-4"><x-ui.public-breadcrumbs group="Plan Your Trip" :section="$tourismBreadcrumbSection" section-route="{{ route('tourism-services.index', request()->only(['provider_type', 'category', 'destination', 'q'])) }}" :current="request()->routeIs('tourism-services.show') ? ($tourismService->service_name ?? null) : null" /></div>
            @elseif (request()->routeIs('transportation.index', 'transportation.show'))
                <div class="container pt-4"><x-ui.public-breadcrumbs group="Plan Your Trip" section="Transportation &amp; Car Rental" section-route="{{ route('transportation.index') }}" :current="request()->routeIs('transportation.show') ? ($tourismService->service_name ?? null) : null" /></div>
            @elseif (request()->routeIs('events.*'))
                <div class="container pt-4"><x-ui.public-breadcrumbs group="Events" section="Cultural Events" section-route="{{ route('events.index') }}" :current="request()->routeIs('events.show') ? ($culturalEvent->event_name ?? null) : null" /></div>
            @elseif (request()->routeIs('map'))
                <div class="container pt-4"><x-ui.public-breadcrumbs group="Plan Your Trip" section="Map" section-route="{{ route('map') }}" /></div>
            @elseif (request()->routeIs('smart-trip.*'))
                <div class="container pt-4"><x-ui.public-breadcrumbs :group="null" section="Smart Trip" section-route="{{ route('smart-trip.index') }}" :current="request()->routeIs('smart-trip.show') || request()->routeIs('smart-trip.edit') || request()->routeIs('smart-trip.ai.*') ? ($trip->title ?? null) : null" /></div>
            @elseif (request()->routeIs('search'))
                <div class="container pt-4"><x-ui.public-breadcrumbs :group="null" section="Search" section-route="{{ route('search') }}" /></div>
            @endif
            @yield('content')
        </main>
    @endif

    @include('layouts.partials.footer')
    @stack('scripts')
</body>
</html>
