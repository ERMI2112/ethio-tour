@extends('layouts.app')

@section('title', 'Security Audit Log · Administrator')

@section('content')
<div class="container-fluid py-4 py-lg-5 px-3 px-lg-5">
    {{-- Breadcrumbs & Header --}}
    <nav class="mb-3" aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-success text-decoration-none fw-semibold">Admin Dashboard</a></li>
            <li class="breadcrumb-item active text-muted" aria-current="page">Security Audit Log</li>
        </ol>
    </nav>

    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-0.5" style="font-size: 0.72rem; font-weight: 700; letter-spacing: 0.05em;">
                    <span class="spinner-grow spinner-grow-sm me-1" style="width: 6px; height: 6px;" role="status"></span>
                    ADMINISTRATOR WORKSPACE
                </span>
            </div>
            <h1 class="display-6 fw-bold mb-1 text-dark" style="font-family: var(--font-display); letter-spacing: -0.03em;">Audit log</h1>
            <p class="text-secondary mb-0" style="font-size: 0.95rem;">
                Trace material platform changes with actor, action, target, and time.
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-light text-dark border px-3 py-2 rounded-pill fw-semibold shadow-sm" style="font-size: 0.82rem;">
                <i class="bi bi-shield-lock-fill text-danger me-1"></i> Immutable Audit Trail
            </span>
        </div>
    </div>

    {{-- Filter Form --}}
    <form class="card border-0 shadow-sm rounded-4 p-3.5 mb-4 bg-white" method="GET">
        <div class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label small fw-bold text-muted" for="audit-search">
                    <i class="bi bi-search me-1"></i> Search actions
                </label>
                <input id="audit-search" class="form-control rounded-3" name="q" value="{{ $search }}" placeholder="approval, suspension, update">
            </div>
            <div class="col-md-5">
                <label class="form-label small fw-bold text-muted" for="audit-action">
                    <i class="bi bi-funnel-fill me-1"></i> Action type
                </label>
                <select id="audit-action" class="form-select rounded-3" name="action">
                    <option value="">All actions</option>
                    @foreach($actions as $option)
                        <option value="{{ $option }}" @selected($action === $option)>{{ str_replace('_',' ',ucfirst($option)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-vn-navy w-100 fw-bold shadow-sm rounded-3 py-2" type="submit">
                    Apply filters
                </button>
            </div>
        </div>
    </form>

    {{-- Audit Log Table --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <div class="card-header bg-white p-3.5 border-bottom d-flex justify-content-between align-items-center">
            <h2 class="h6 fw-bold mb-0 text-dark" style="font-family: var(--font-display);">
                <i class="bi bi-journal-code text-danger me-1.5"></i> Platform Security &amp; Activity Log
            </h2>
            <span class="badge bg-light text-secondary border px-2.5 py-1 rounded-pill small">
                Records: {{ $entries->total() }}
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <caption class="visually-hidden">Administrative audit events</caption>
                <thead class="table-light text-uppercase small text-muted" style="font-size: 0.72rem; letter-spacing: 0.06em;">
                    <tr>
                        <th class="ps-4 py-3">Timestamp</th>
                        <th class="py-3">Actor</th>
                        <th class="py-3">Action</th>
                        <th class="py-3">Target</th>
                        <th class="pe-4 py-3">Details &amp; Metadata</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($entries as $entry)
                        <tr>
                            <td class="ps-4 py-3 text-nowrap font-monospace small text-muted">
                                <i class="bi bi-clock-history me-1"></i>
                                {{ $entry->created_at?->format('Y-m-d H:i:s') }}
                            </td>
                            <td class="py-3">
                                <div class="fw-bold text-dark fs-6">{{ $entry->actor?->email ?? 'System Engine' }}</div>
                                <div class="small text-muted font-monospace">{{ $entry->actor?->role ?? 'automated' }}</div>
                            </td>
                            <td class="py-3">
                                <span class="badge bg-light text-dark border font-monospace rounded-pill px-2.5 py-1" style="font-size: 0.75rem;">
                                    {{ str_replace('_',' ',ucfirst($entry->action)) }}
                                </span>
                            </td>
                            <td class="py-3 text-muted small font-monospace">
                                {{ $entry->subject_type ? class_basename($entry->subject_type).' #'.$entry->subject_id : 'Platform System' }}
                            </td>
                            <td class="pe-4 py-3">
                                @if($entry->metadata)
                                    <details class="rounded-3 p-2 bg-light border">
                                        <summary class="small fw-bold text-primary cursor-pointer">
                                            View recorded payload
                                        </summary>
                                        <div class="small mb-0 mt-2 font-monospace" style="font-size: 0.78rem;">
                                            @foreach($entry->metadata as $key => $value)
                                                <div class="d-flex gap-2 py-0.5">
                                                    <span class="text-muted text-nowrap">{{ str_replace('_',' ',ucfirst($key)) }}:</span>
                                                    <span class="text-dark fw-semibold">{{ is_scalar($value) ? $value : json_encode($value) }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </details>
                                @else
                                    <span class="text-muted small">&mdash;</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-5">
                                <div class="fs-1 mb-2">📜</div>
                                <div class="fw-bold fs-6">No audit activity matches these filters.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($entries->hasPages())
            <div class="p-3 border-top bg-light-subtle d-flex justify-content-end">
                {{ $entries->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
