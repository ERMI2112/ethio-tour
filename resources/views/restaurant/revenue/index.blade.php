@extends('layouts.app')

@section('title', 'Dining Revenue & Financial Ledger · ' . $provider->business_name)

@section('content')
<div class="container-fluid py-4 py-lg-5 px-3 px-lg-5">
    {{-- Breadcrumbs & Header --}}
    <nav class="mb-3" aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="{{ route('restaurant.dashboard') }}" class="text-success text-decoration-none fw-semibold">Restaurant Dashboard</a></li>
            <li class="breadcrumb-item active text-muted" aria-current="page">Revenue</li>
        </ol>
    </nav>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-3 border-bottom">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-0.5" style="font-size: 0.72rem; font-weight: 700;">
                    💳 Financial Ledger
                </span>
            </div>
            <h1 class="h3 fw-bold mb-0 text-dark" style="font-family: var(--font-display);">Dining Revenue</h1>
            <p class="text-secondary mb-0 small">
                Gross sales, escrow clearances, and completed reservations for {{ $provider->business_name }}
            </p>
        </div>

        <div class="d-flex align-items-center gap-3">
            <div class="p-2.5 px-3.5 bg-white border rounded-4 shadow-sm text-center">
                <div class="small text-muted fw-bold text-uppercase" style="font-size: 0.68rem;">Total Cleared Revenue</div>
                <div class="h4 fw-bold text-success mb-0 font-monospace">{{ number_format($totalRevenue, 2) }} ETB</div>
            </div>
            <div class="p-2.5 px-3.5 bg-white border rounded-4 shadow-sm text-center">
                <div class="small text-muted fw-bold text-uppercase" style="font-size: 0.68rem;">Completed Orders</div>
                <div class="h4 fw-bold text-dark mb-0 font-monospace">{{ $completedCount }}</div>
            </div>
        </div>
    </div>

    {{-- Financial Ledger Table --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <div class="card-header bg-white p-3.5 border-bottom d-flex justify-content-between align-items-center">
            <h2 class="h6 fw-bold mb-0 text-dark" style="font-family: var(--font-display);">
                <i class="bi bi-receipt-cutoff text-success me-1.5"></i> Cleared Dining Transactions
            </h2>
            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 font-monospace small">
                Direct Escrow Cleared
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-uppercase small text-muted" style="font-size: 0.72rem; letter-spacing: 0.05em;">
                    <tr>
                        <th class="ps-4 py-3">Booking Reference</th>
                        <th class="py-3">Diner Guest</th>
                        <th class="py-3">Menu Offering</th>
                        <th class="py-3">Settlement Date</th>
                        <th class="py-3">Status</th>
                        <th class="pe-4 py-3 text-end">Gross Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookings as $booking)
                        <tr>
                            <td class="ps-4 py-3 font-monospace fw-bold text-dark">
                                #BK-{{ sprintf('%05d', $booking->booking_id) }}
                            </td>
                            <td class="py-3">
                                <div class="fw-semibold text-dark">{{ $booking->tourist->full_name ?? 'Guest Diner' }}</div>
                                <div class="small text-muted">{{ $booking->tourist?->nationality ?? 'Traveler' }}</div>
                            </td>
                            <td class="py-3">
                                <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1">
                                    {{ $booking->tourismService->service_name ?? 'Dining Reservation' }}
                                </span>
                            </td>
                            <td class="py-3 font-monospace small text-muted">
                                {{ $booking->booking_date?->format('Y-m-d H:i') }}
                            </td>
                            <td class="py-3">
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1">
                                    ● {{ ucfirst($booking->status) }}
                                </span>
                            </td>
                            <td class="pe-4 py-3 text-end font-monospace fw-bold text-success fs-6">
                                ${{ number_format((float) $booking->total_amount, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <div class="fs-1 mb-2">💳</div>
                                <div class="fw-bold fs-6">No completed transactions recorded yet.</div>
                                <div class="small">Completed reservations will appear in this ledger.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($bookings->hasPages())
            <div class="p-3 border-top d-flex justify-content-end">
                {{ $bookings->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
