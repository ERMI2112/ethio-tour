@extends('layouts.app')
@section('title','Edit Event')
@section('content')<div class="container py-4"><div class="card border-0 shadow-sm"><div class="card-body p-4"><h1 class="h3 mb-4">Edit event</h1><form method="POST" action="{{ route('event-organizer.events.update',$event) }}">@method('PUT')@include('event-organizer.events._form')<button class="btn btn-primary mt-4">Save changes</button></form></div></div></div>@endsection
