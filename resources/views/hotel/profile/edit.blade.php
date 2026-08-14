@extends('layouts.app')

@section('title', 'Edit Hotel Profile')

@section('content')
<div class="container py-5"><div class="row justify-content-center"><div class="col-lg-7"><a class="small text-decoration-none" href="{{ route('hotel.profile') }}">&larr; Hotel management</a><div class="card border-0 shadow-sm mt-3"><div class="card-body p-4"><h1 class="h3 mb-4">Edit hotel provider profile</h1><form method="POST" action="{{ route('hotel.profile.update') }}">@csrf @method('PUT')<div class="mb-3"><label class="form-label" for="business_name">Business name</label><input class="form-control @error('business_name') is-invalid @enderror" id="business_name" name="business_name" value="{{ old('business_name', $provider->business_name) }}" required>@error('business_name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div><div class="d-flex gap-2"><button class="btn btn-primary" type="submit">Save changes</button><a class="btn btn-outline-secondary" href="{{ route('hotel.profile') }}">Cancel</a></div></form></div></div></div></div></div>
@endsection
