@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="container py-4 py-lg-5">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div><p class="text-muted small text-uppercase mb-1">Your account</p><h1 class="h2 mb-0">Notifications</h1></div>
        @if ($notifications->where('read_status', false)->isNotEmpty())
            <form method="POST" action="{{ route('notifications.read-all') }}">@csrf @method('PATCH')<button class="btn btn-outline-primary" type="submit">Mark all as read</button></form>
        @endif
    </div>
    @if ($notifications->isEmpty())
        <x-ui.empty-state title="No notifications yet" message="Updates about your bookings, applications, and verification decisions will appear here." />
    @else
        <div class="list-group shadow-sm">
            @foreach ($notifications as $notification)
                <div class="list-group-item list-group-item-action {{ $notification->read_status ? '' : 'border-start border-4 border-primary bg-primary-subtle' }}">
                    <div class="d-flex justify-content-between gap-3">
                        <div><div class="d-flex align-items-center gap-2"><span class="badge text-bg-secondary">{{ str_replace('_', ' ', $notification->type) }}</span><h2 class="h6 mb-0">{{ $notification->title }}</h2></div><p class="mb-1 mt-2">{{ $notification->message }}</p><small class="text-muted">{{ $notification->sent_date?->diffForHumans() }}</small></div>
                        @if (! $notification->read_status)<form method="POST" action="{{ route('notifications.read', $notification) }}">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-secondary" type="submit">Mark read</button></form>@endif
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-3">{{ $notifications->links() }}</div>
    @endif
</div>
@endsection
