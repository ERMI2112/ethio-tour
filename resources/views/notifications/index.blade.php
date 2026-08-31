@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="container py-4 py-lg-5">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.72rem; letter-spacing: 0.04em;">
                    <i class="bi bi-bell-fill me-1"></i> CENTRAL ALERT CENTER
                </span>
                @php($unreadTotal = $notifications->where('read_status', false)->count())
                @if ($unreadTotal > 0)
                    <span class="badge bg-danger text-white rounded-pill px-2 py-0.5" style="font-size: 0.7rem;">
                        {{ $unreadTotal }} unread
                    </span>
                @endif
            </div>
            <h1 class="h2 mb-1 fw-bold text-dark" style="font-family: var(--font-display);">Notifications</h1>
            <p class="text-muted mb-0">Actionable updates from your real bookings, payments, and platform workflows.</p>
        </div>
        @if ($notifications->where('read_status', false)->isNotEmpty())
            <form method="POST" action="{{ route('notifications.read-all') }}">
                @csrf
                @method('PATCH')
                <button class="btn btn-outline-secondary btn-sm rounded-3 px-3 py-2 fw-semibold d-inline-flex align-items-center gap-1.5" type="submit">
                    <i class="bi bi-check2-all"></i> Mark all as read
                </button>
            </form>
        @endif
    </div>

    @if ($notifications->isEmpty())
        <x-ui.empty-state icon="bi-bell" title="No notifications yet" message="Updates about your bookings, applications, and verification decisions will appear here." />
    @else
        <div class="d-flex flex-column gap-3 notification-list">
            @foreach ($notifications as $notification)
                <div class="card border-0 shadow-sm rounded-4 position-relative overflow-hidden transition-hover {{ $notification->read_status ? 'bg-white' : 'border-start border-4 border-primary bg-primary-subtle bg-opacity-25' }}">
                    <div class="card-body p-3.5 p-lg-4">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                            <div class="d-flex align-items-start gap-3 flex-grow-1">
                                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 {{ $notification->read_status ? 'bg-light text-muted border' : 'bg-primary text-white shadow-sm' }}" style="width: 44px; height: 44px; font-size: 1.15rem;">
                                    <i class="bi {{ $notification->category_icon }}"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                        <span class="badge rounded-pill px-2.5 py-1 {{ $notification->category_badge }}" style="font-size: 0.72rem;">
                                            {{ ucwords(str_replace('_', ' ', $notification->type)) }}
                                        </span>
                                        @unless($notification->read_status)
                                            <span class="badge bg-primary text-white rounded-pill px-2 py-0.5" style="font-size: 0.68rem;">New</span>
                                        @endunless
                                        <span class="text-muted small d-inline-flex align-items-center gap-1 ms-auto ms-sm-0">
                                            <i class="bi bi-clock"></i> {{ $notification->sent_date?->diffForHumans() }}
                                        </span>
                                    </div>
                                    <h2 class="h6 fw-bold mb-1 text-dark">{{ $notification->title }}</h2>
                                    <p class="text-secondary small mb-0">{{ $notification->message }}</p>
                                </div>
                            </div>

                            <div class="d-flex align-items-center gap-2 w-100 w-md-auto justify-content-end pt-2 pt-md-0 border-top border-md-top-0">
                                @if (! $notification->read_status)
                                    <form method="POST" action="{{ route('notifications.read', $notification) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-sm btn-light border rounded-3 px-2.5 py-1.5 text-muted fw-semibold" type="submit" title="Mark as read">
                                            <i class="bi bi-check2"></i> <span class="d-none d-sm-inline">Mark read</span>
                                        </button>
                                    </form>
                                @endif
                                <a class="btn btn-sm btn-primary rounded-3 px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-sm" href="{{ route('notifications.navigate', $notification) }}">
                                    <span>{{ $notification->action_label }}</span>
                                    <i class="bi bi-arrow-right small"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-4">{{ $notifications->links() }}</div>
    @endif
</div>
@endsection
