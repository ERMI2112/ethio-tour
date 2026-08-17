@extends('layouts.app')
@section('title','Create Event')
@section('content')<div class="container py-4"><div class="card border-0 shadow-sm"><div class="card-body p-4"><h1 class="h3 mb-4">Create cultural event</h1>
@if($destinations->isEmpty() || $categories->isEmpty())
<div class="alert alert-warning" role="alert"><strong>Reference data is not available yet.</strong><br>At least one destination and category must be published by the Tourism Bureau before an event can be created.</div>
@else
<form method="POST" action="{{ route('event-organizer.events.store') }}">
@include('event-organizer.events._form')
<button class="btn btn-primary mt-4">Create event</button></form>
@endif</div></div></div>@endsection
