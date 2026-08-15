@extends('layouts.app')
@section('title', 'Provider Profile')
@section('content')
<div class="container py-4"><h1 class="h3">Provider Profile</h1><form method="POST" action="{{ route('provider.profile.update') }}" class="card shadow-sm"><div class="card-body">@csrf @method('PUT')<div class="mb-3"><label class="form-label" for="business_name">Business name</label><input class="form-control @error('business_name') is-invalid @enderror" id="business_name" name="business_name" value="{{ old('business_name', $provider->business_name) }}">@error('business_name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div><button class="btn btn-primary">Save profile</button></div></form></div>
@endsection
