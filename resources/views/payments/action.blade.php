@if ($booking->payment?->status === 'success' || $booking->status === 'confirmed' && $booking->payment?->status === 'success')
    <div class="alert alert-success small mb-0"><strong>Payment confirmed.</strong> This booking is secured.</div>
@elseif (in_array($booking->status, ['accepted', 'payment_pending'], true))
    @if ($booking->payment?->status === 'pending')
        <div class="alert alert-info small mb-0"><strong>Payment attempt in progress.</strong> Return to the Chapa checkout window to complete it.</div>
    @elseif ($booking->total_amount === null || (float) $booking->total_amount <= 0)
        <div class="alert alert-warning small mb-0">Payment is not available because this booking has no payable amount yet.</div>
    @else
        <div class="border rounded p-3">
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
@endif
