@extends('layouts.app')
@section('title', 'Categories')
@section('content')
    <div class="container py-5"><div class="mb-4"><h1 class="h2 mb-1">Tourism Service Categories</h1><p class="text-muted mb-0">Browse available service types.</p></div>@if($categories->isEmpty())<x-ui.empty-state title="No categories available" message="Service categories will be published as tourism services are added." />@else<div class="row g-3">@foreach($categories as $category)<div class="col-sm-6 col-lg-4"><a class="card h-100 text-decoration-none border-0 shadow-sm" href="{{ route('tourism-services.index', ['category' => $category->category_id]) }}"><div class="card-body"><h2 class="h5 text-body">{{ $category->category_name }}</h2><p class="text-muted small mb-0">{{ $category->tourism_services_count }} {{ \Illuminate\Support\Str::plural('service', $category->tourism_services_count) }}</p></div></a></div>@endforeach</div>@endif</div>
@endsection
