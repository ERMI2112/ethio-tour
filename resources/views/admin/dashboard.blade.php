@extends('layouts.app')
@section('title', 'Administrator Dashboard')
@section('content')
<div class="container py-4"><h1 class="h3 mb-4">Administrator Dashboard</h1><div class="row g-3">@foreach([['Total users',$totalUsers],['Active users',$activeUsers],['Pending activation',$pendingProviders],['Approved providers',$approvedProviders],['Suspended providers',$suspendedProviders],['Rejected providers',$rejectedProviders],['Verified guides',$verifiedGuides],['Pending guides',$pendingGuides],['Bookings',$bookings],['Audit entries',$auditEntries]] as [$label,$value])<div class="col-6 col-lg-3"><div class="card shadow-sm h-100"><div class="card-body"><div class="text-muted small">{{ $label }}</div><div class="display-6">{{ $value }}</div></div></div></div>@endforeach</div></div>
@endsection
