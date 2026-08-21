@extends('layouts.app')
@section('title', 'Account')
@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4 p-md-5">
                        <span class="badge text-bg-success mb-3">Signed in</span>
                        <h1 class="h3 mb-2">{{ $workspaceLabel }}</h1>
                        <p class="text-secondary mb-4">Your account role is <strong>{{ str_replace('_', ' ', $user->role) }}</strong>. Open the dedicated workspace for operations that belong to this role. Public discovery stays separate from internal tools.</p>
                        @if ($workspaceRoute)
                            <a class="btn btn-success px-4" href="{{ route($workspaceRoute) }}">Continue to workspace</a>
                        @endif
                        @if ($user->role === 'tourist')
                            <a class="btn btn-outline-secondary px-4" href="{{ route('home') }}">View public site</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
