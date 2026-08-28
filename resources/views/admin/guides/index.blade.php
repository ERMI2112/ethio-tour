@extends('layouts.app')

@section('title', 'Final Guide Approval')

@section('content')
<div class="container py-4 py-lg-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <p class="text-uppercase text-muted small fw-semibold mb-1">Administrator governance</p>
            <h1 class="h3 mb-1">Final guide approval</h1>
            <p class="text-muted mb-0">Review guides after Tourism Bureau verification and document approval.</p>
        </div>
        <a class="btn btn-outline-secondary" href="{{ route('admin.dashboard') }}">Back to dashboard</a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light"><tr><th>Guide</th><th>License</th><th>Bureau status</th><th>Final status</th><th></th></tr></thead>
                <tbody>
                @forelse($guides as $guide)
                    <tr>
                        <td><strong>{{ $guide->full_name ?: $guide->user?->email }}</strong><small class="d-block text-muted">{{ $guide->user?->email }}</small></td>
                        <td class="font-monospace">{{ $guide->license_number ?: 'Not provided' }}</td>
                        <td><x-ui.status-badge :status="$guide->verification_status" /></td>
                        <td><x-ui.status-badge :status="$guide->admin_approval_status" /></td>
                        <td><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.guides.show', $guide) }}">Review</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-5">No Bureau-verified guides are waiting for final approval.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">{{ $guides->links() }}</div>
    </div>
</div>
@endsection
