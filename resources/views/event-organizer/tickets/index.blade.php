@extends('layouts.app')

@section('title', 'Event Tickets')

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb"><ol class="breadcrumb small"><li class="breadcrumb-item"><a href="{{ route('event-organizer.dashboard') }}">Event Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('event-organizer.events.show', $event) }}">{{ $event->event_name }}</a></li><li class="breadcrumb-item active">Tickets</li></ol></nav>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4"><div><p class="text-muted small text-uppercase mb-1">Event Organizer Portal</p><h1 class="h3 mb-0">Ticket management</h1></div><a class="btn btn-outline-secondary" href="{{ route('event-organizer.events.show', $event) }}">Back to event</a></div>
    @include('layouts.partials.flash-messages')
    <div class="card border-0 shadow-sm my-4"><div class="card-body">
        <h2 class="h5">Add ticket type</h2>
        <form method="POST" action="{{ route('event-organizer.events.tickets.store', $event) }}">
            @csrf
            <div class="row g-2"><div class="col-md-4"><label class="form-label" for="ticket-name">Name</label><input id="ticket-name" name="name" class="form-control" placeholder="General admission" required></div><div class="col-md-3"><label class="form-label" for="ticket-price">Price</label><input id="ticket-price" name="price" type="number" min="0" step="0.01" class="form-control" required></div><div class="col-md-3"><label class="form-label" for="ticket-quantity">Quantity</label><input id="ticket-quantity" name="quantity" type="number" min="1" class="form-control" required></div><div class="col-md-2"><label class="form-label" for="ticket-status">Status</label><select id="ticket-status" name="status" class="form-select"><option value="active">Active</option><option value="inactive">Inactive</option></select></div></div>
            <button class="btn btn-primary mt-3">Add ticket type</button>
        </form>
    </div></div>
    <div class="table-responsive"><table class="table bg-white shadow-sm align-middle"><thead><tr><th colspan="5">Ticket type</th><th>Remaining</th><th>Actions</th></tr></thead><tbody>
    @forelse($tickets as $ticket)
        <tr><td colspan="5"><form method="POST" action="{{ route('event-organizer.events.tickets.update', [$event, $ticket]) }}" class="row g-2 align-items-center"><input type="hidden" name="_token" value="{{ csrf_token() }}">@method('PUT')<div class="col-md-4"><label class="visually-hidden" for="ticket-{{ $ticket->ticket_type_id }}-name">Name</label><input id="ticket-{{ $ticket->ticket_type_id }}-name" name="name" class="form-control form-control-sm" value="{{ $ticket->name }}" required></div><div class="col-md-2"><label class="visually-hidden" for="ticket-{{ $ticket->ticket_type_id }}-price">Price</label><input id="ticket-{{ $ticket->ticket_type_id }}-price" name="price" type="number" min="0" step="0.01" class="form-control form-control-sm" value="{{ $ticket->price }}" required></div><div class="col-md-2"><label class="visually-hidden" for="ticket-{{ $ticket->ticket_type_id }}-quantity">Quantity</label><input id="ticket-{{ $ticket->ticket_type_id }}-quantity" name="quantity" type="number" min="1" class="form-control form-control-sm" value="{{ $ticket->quantity }}" required></div><div class="col-md-2"><label class="visually-hidden" for="ticket-{{ $ticket->ticket_type_id }}-status">Status</label><select id="ticket-{{ $ticket->ticket_type_id }}-status" name="status" class="form-select form-select-sm"><option value="active" @selected($ticket->status === 'active')>Active</option><option value="inactive" @selected($ticket->status === 'inactive')>Inactive</option></select></div><div class="col-md-2"><button class="btn btn-sm btn-outline-primary" type="submit">Save changes</button></div></form></td><td>{{ app(\App\Services\EventInventoryService::class)->availableQuantity($ticket) }}</td><td><span class="small text-muted">Use status to deactivate</span></td></tr>
    @empty
        <tr><td colspan="7" class="text-center text-muted py-4">No ticket types yet. Add the first ticket type above.</td></tr>
    @endforelse
    </tbody></table></div>
</div>
@endsection
