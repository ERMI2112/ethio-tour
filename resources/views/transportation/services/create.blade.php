@extends('layouts.app')
@section('title','Add Transportation Service')
@section('content')<div class="container py-4"><div class="card border-0 shadow-sm"><div class="card-body p-4"><h1 class="h3 mb-4">Add transportation service</h1>
@if($destinations->isEmpty() || $categories->isEmpty())
<div class="alert alert-warning" role="alert"><strong>Reference data is not available yet.</strong><br>A destination and category must be published by the Tourism Bureau before a transportation service can be created.</div>
@else
<form method="POST" action="{{ route('transportation.services.store') }}">
@include('transportation.services._form')
<button class="btn btn-primary mt-4">Create service</button></form>
@endif</div></div></div>@endsection
