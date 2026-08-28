@extends('layouts.app')

@section('title', 'Ethio Tour workspaces')

@section('meta_description', 'Role-scoped Ethio Tour workspaces for travelers, tourism professionals, Bureau officers, and platform administrators.')

@section('content')
<div class="container py-5">
    <div class="row justify-content-between align-items-end g-3 mb-4">
        <div class="col-lg-8">
            <p class="landing-eyebrow mb-1">Operating system</p>
            <h1 class="display-6 fw-bold mb-2">Role-based workspaces for Ethio Tour</h1>
            <p class="lead text-secondary mb-0">Each workspace is role-scoped. Public visitors never see operator tools. Bureau officers verify credentials. Administrators activate accounts after that verification. Travelers book only from publicly operational records.</p>
        </div>
        <div class="col-lg-4 text-lg-end">
            @auth
                @if ($workspaceRoute)
                    <a class="btn btn-success px-4" href="{{ route($workspaceRoute) }}">Open {{ $workspaceLabel }}</a>
                @endif
            @else
                <a class="btn btn-success px-4" href="{{ route('login') }}">Log in to your workspace</a>
                <a class="btn btn-outline-success px-4 mt-2 mt-sm-0" href="{{ route('register') }}">Register</a>
            @endauth
        </div>
    </div>

    <div class="row g-4">
        @foreach ($portals as $portal)
            <div class="col-md-6 col-xl-4">
                <article class="landing-portal-card h-100 shadow-sm">
                    <span class="landing-portal-index">Workspace {{ sprintf('%02d', $loop->iteration) }}</span>
                    <h2 class="h5 mb-1">{{ $portal['name'] }}</h2>
                    <p class="small text-muted mb-2">{{ $portal['audience'] }}</p>
                    <p class="small text-secondary mb-3">{{ $portal['summary'] }}</p>
                    <ul class="small text-secondary mb-3 ps-3">
                        @foreach ($portal['capabilities'] as $capability)
                            <li>{{ $capability }}</li>
                        @endforeach
                    </ul>
                    @if ($portal['entry'] === 'home')
                        <a class="btn btn-sm btn-outline-success" href="{{ route('home') }}">Open public site</a>
                    @else
                        <a class="btn btn-sm btn-outline-success" href="{{ route('login') }}">Sign in to continue</a>
                    @endif
                </article>
            </div>
        @endforeach
    </div>

    <section class="landing-plan-card shadow-sm mt-5" aria-labelledby="governance-heading">
        <p class="landing-eyebrow mb-1">Governance</p>
        <h2 id="governance-heading" class="h3 fw-bold mb-2">How a provider becomes public</h2>
        <ol class="mb-0 text-secondary">
            <li class="mb-2">A hotel, restaurant, transport operator, event organizer, or tour guide registers a public account.</li>
            <li class="mb-2">A tourism bureau officer reviews credentials and records a verification decision. That step never activates the account.</li>
            <li>An administrator reviews Bureau-verified providers and activates the account. Only then do services appear in public discovery and become bookable.</li>
        </ol>
    </section>
</div>
@endsection
