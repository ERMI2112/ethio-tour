@if ($booking->payment?->status === 'success' || $booking->status === 'confirmed' && $booking->payment?->status === 'success')
    <div class="alert alert-success small mb-0"><strong>Payment confirmed.</strong> This booking is secured.</div>
@elseif (in_array($booking->status, ['accepted', 'payment_pending'], true))
    @if ($booking->total_amount === null || (float) $booking->total_amount <= 0)
        <div class="alert alert-warning small mb-0">Payment is not available because this booking has no payable amount yet.</div>
    @else
        <div class="border rounded p-3">
            @if ($booking->payment?->status === 'pending')
                <div class="alert alert-info small mb-3"><strong>Payment attempt in progress.</strong> Click below to continue your payment with Chapa.</div>
            @endif
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-muted">Amount to pay</span>
                <strong>{{ number_format((float) $booking->total_amount, 2) }} {{ $booking->currency }}</strong>
            </div>
            <form method="POST" action="{{ route('payments.initialize', $booking) }}">
                @csrf
                <button type="submit" class="btn btn-success w-100">
                    {{ $booking->status === 'payment_pending' ? 'Continue Payment' : 'Pay Now' }}
                </button>
            </form>
            <p class="small text-muted mt-2 mb-0">You will continue to Chapa's secure test checkout.</p>
        </div>
    @endif
@elseif ($booking->status === 'pending')
    <div class="alert alert-warning small mb-0"><strong>Awaiting provider decision.</strong> Payment will become available after this request is accepted.</div>
@elseif ($booking->status === 'rejected')
    <div class="alert alert-danger small mb-0"><strong>Booking request rejected.</strong> No payment is due for this request.</div>
@elseif ($booking->status === 'cancelled')
    <div class="alert alert-secondary small mb-0"><strong>Booking cancelled.</strong> No payment is due for this booking.</div>
@elseif ($booking->status === 'completed')
    <div class="alert alert-success small mb-0"><strong>Experience completed.</strong> Thank you for travelling with Ethio Tour.</div>
@endif
