<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name')) | {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="d-flex flex-column">
    @include('layouts.partials.navigation')

    <main class="flex-grow-1">
        @include('layouts.partials.flash-messages')
        @if (request()->routeIs('destinations.*'))
            <div class="container pt-4"><x-ui.public-breadcrumbs group="Explore Ethiopia" section="Destinations" section-route="{{ route('destinations.index') }}" :current="request()->routeIs('destinations.show') ? ($destination->name ?? null) : null" /></div>
        @elseif (request()->routeIs('heritage-sites.*'))
            <div class="container pt-4"><x-ui.public-breadcrumbs group="Explore Ethiopia" section="Heritage Sites" section-route="{{ route('heritage-sites.index') }}" :current="request()->routeIs('heritage-sites.show') ? ($heritageSite->heritage_type ?? null) : null" /></div>
        @elseif (request()->routeIs('museums.*'))
            <div class="container pt-4"><x-ui.public-breadcrumbs group="Explore Ethiopia" section="Museums" section-route="{{ route('museums.index') }}" :current="request()->routeIs('museums.show') ? ($museum->museum_name ?? null) : null" /></div>
        @elseif (request()->routeIs('tour-guides.*'))
            <div class="container pt-4"><x-ui.public-breadcrumbs group="Things to Do" section="Tour Guides" section-route="{{ route('tour-guides.index') }}" :current="request()->routeIs('tour-guides.show') ? 'Tour Guide Profile' : null" /></div>
        @elseif (request()->routeIs('tourism-services.*'))
            <div class="container pt-4"><x-ui.public-breadcrumbs group="Stay &amp; Eat" section="Tourism Services" section-route="{{ route('tourism-services.index') }}" :current="request()->routeIs('tourism-services.show') ? ($tourismService->service_name ?? null) : null" /></div>
        @elseif (request()->routeIs('transportation.index', 'transportation.show'))
            <div class="container pt-4"><x-ui.public-breadcrumbs group="Travel &amp; Transport" section="Transportation" section-route="{{ route('transportation.index') }}" :current="request()->routeIs('transportation.show') ? ($tourismService->service_name ?? null) : null" /></div>
        @elseif (request()->routeIs('events.*'))
            <div class="container pt-4"><x-ui.public-breadcrumbs group="Events" section="Events &amp; Festivals" section-route="{{ route('events.index') }}" :current="request()->routeIs('events.show') ? ($culturalEvent->event_name ?? null) : null" /></div>
        @elseif (request()->routeIs('map'))
            <div class="container pt-4"><x-ui.public-breadcrumbs group="Explore Ethiopia" section="Map" section-route="{{ route('map') }}" /></div>
        @elseif (request()->routeIs('smart-trip.*'))
            <div class="container pt-4"><x-ui.public-breadcrumbs group="Plan Your Trip" section="Smart Trip" section-route="{{ route('smart-trip.index') }}" :current="request()->routeIs('smart-trip.show') || request()->routeIs('smart-trip.edit') || request()->routeIs('smart-trip.ai.*') ? ($trip->title ?? null) : null" /></div>
        @endif
        @yield('content')
    </main>

    @include('layouts.partials.footer')
    @stack('scripts')
</body>
</html>
