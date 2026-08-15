@extends('layouts.app')
@section('title','Event Organizer Profile')
@section('content')<div class="container py-4"><div class="card border-0 shadow-sm"><div class="card-body p-4"><h1 class="h3">Event organizer profile</h1><dl class="row mt-4"><dt class="col-sm-3">Business name</dt><dd class="col-sm-9">{{ $provider->business_name }}</dd><dt class="col-sm-3">Status</dt><dd class="col-sm-9">{{ $provider->status }}</dd></dl></div></div></div>@endsection
