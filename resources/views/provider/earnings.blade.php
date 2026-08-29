@extends('layouts.app')

@section('title', 'Earnings')

@section('content')
<div class="container-fluid py-4 py-lg-5 px-3 px-lg-5">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-3 border-bottom">
        <div>
            <p class="text-uppercase text-muted small fw-semibold mb-1">Financial ledger</p>
            <h1 class="h3 mb-1">Earnings</h1>
            <p class="text-secondary mb-0">{{ $payable->business_name ?? $payable->user?->email }}</p>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3"><x-ui.stat-card label="Gross earnings" icon="cash" :value="$totals['gross_earnings'].' '.$totals['currency']" hint="All successful payments" /></div>
        <div class="col-6 col-xl-3"><x-ui.stat-card label="Platform commission" icon="percent" :value="$totals['commission_deductions'].' '.$totals['currency']" hint="Deducted per your plan" /></div>
        <div class="col-6 col-xl-3"><x-ui.stat-card label="Net earnings" icon="wallet2" :value="$totals['net_earnings'].' '.$totals['currency']" hint="Gross minus commission" /></div>
    </div>

    <div class="alert alert-info small" role="note">
        These figures are your recorded financial history. Settlement, payout schedules, and refund handling are not part of the platform yet and will be introduced in a later phase.
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white"><h2 class="h5 mb-0">Ledger entries</h2></div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th class="small text-muted text-uppercase">Date</th>
                        <th class="small text-muted text-uppercase">Type</th>
                        <th class="small text-muted text-uppercase">Description</th>
                        <th class="small text-muted text-uppercase text-end">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($entries as $entry)
                        <tr>
                            <td class="small">{{ $entry->created_at?->toDateTimeString() }}</td>
                            <td>
                                @if($entry->entry_type === 'earning')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">Earning</span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">Commission</span>
                                @endif
                            </td>
                            <td class="small text-muted">{{ $entry->description }}</td>
                            <td class="text-end fw-semibold {{ str_starts_with((string) $entry->amount, '-') ? 'text-danger' : '' }}">
                                {{ $entry->amount }} {{ $entry->currency }}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-muted text-center py-4">No financial entries recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($entries->hasPages())
            <div class="card-footer bg-white">{{ $entries->links() }}</div>
        @endif
    </div>
</div>
@endsection
