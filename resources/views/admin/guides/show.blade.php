@extends('layouts.app')

@section('title', 'Review Guide Approval')

@section('content')
<div class="container py-4 py-lg-5">
    <a class="link-secondary" href="{{ route('admin.guides.index') }}">&larr; Back to guide queue</a>
    @include('layouts.partials.flash-messages')
    <div class="row g-4 mt-1">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm mb-4"><div class="card-body p-4">
                <p class="text-uppercase text-muted small fw-semibold mb-1">Administrator final review</p>
                <h1 class="h3 mb-1">{{ $guide->full_name ?: $guide->user?->email }}</h1>
                <p class="text-muted">{{ $guide->user?->email }}</p>
                <dl class="row mb-0"><dt class="col-sm-4">License</dt><dd class="col-sm-8">{{ $guide->license_number ?: 'Not provided' }}</dd><dt class="col-sm-4">Bureau verification</dt><dd class="col-sm-8"><x-ui.status-badge :status="$guide->verification_status" /></dd><dt class="col-sm-4">Final approval</dt><dd class="col-sm-8"><x-ui.status-badge :status="$guide->admin_approval_status" /></dd><dt class="col-sm-4">Bureau notes</dt><dd class="col-sm-8">{{ $guide->verification_notes ?: 'None recorded' }}</dd></dl>
            </div></div>

            <div class="card border-0 shadow-sm"><div class="card-header bg-white"><h2 class="h5 mb-0">Verification documents</h2></div><div class="list-group list-group-flush">
                @forelse($guide->verificationDocuments as $document)
                    <div class="list-group-item d-flex justify-content-between align-items-center gap-3"><div><strong>{{ ucfirst(str_replace('_', ' ', $document->document_type)) }}</strong><small class="d-block text-muted">{{ $document->original_name }} · {{ number_format($document->size_bytes / 1024, 1) }} KB</small></div><div class="d-flex align-items-center gap-2"><x-ui.status-badge :status="$document->status" /><a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.guides.documents.download', [$guide, $document]) }}">Download</a></div></div>
                @empty
                    <div class="list-group-item text-muted">No verification documents uploaded.</div>
                @endforelse
            </div></div>
        </div>
        <div class="col-lg-5">
            @if($guide->verification_status === 'verified' && $guide->admin_approval_status === 'pending')
                <div class="card border-0 shadow-sm"><div class="card-header bg-white"><h2 class="h5 mb-0">Final decision</h2></div><div class="card-body p-4">
                    <p class="small text-muted">Approval requires Bureau verification plus approved license and identity documents.</p>
                    <form method="POST" action="{{ route('admin.guides.decide', $guide) }}">@csrf @method('PATCH')<label class="form-label" for="approval_notes">Decision notes</label><textarea class="form-control mb-3" id="approval_notes" name="approval_notes" rows="4">{{ old('approval_notes') }}</textarea><div class="d-flex gap-2"><button class="btn btn-success" name="decision" value="approve">Approve guide</button><button class="btn btn-outline-danger" name="decision" value="reject">Reject guide</button></div></form>
                </div></div>
            @else
                <div class="alert alert-secondary">This guide is not currently awaiting final approval.</div>
            @endif
        </div>
    </div>
</div>
@endsection
