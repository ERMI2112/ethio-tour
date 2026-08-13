@extends('layouts.app')
@section('title', 'Account')
@section('content')
    <div class="container py-5"><div class="row justify-content-center"><div class="col-lg-8"><div class="card border-0 shadow-sm"><div class="card-body p-4 p-md-5"><span class="badge text-bg-secondary mb-3">Authentication foundation</span><h1 class="h3">Signed in successfully</h1><p class="mb-0">Your account role is <strong>{{ $user->role }}</strong>. Business portal features will be added in later phases.</p></div></div></div></div></div>
@endsection
