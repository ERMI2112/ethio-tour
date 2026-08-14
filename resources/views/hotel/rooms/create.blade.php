@extends('layouts.app')

@section('title', 'Add Physical Room')

@section('content')
<div class="container py-5"><div class="row justify-content-center"><div class="col-lg-8"><a class="small text-decoration-none" href="{{ route('hotel.rooms.index') }}">&larr; Physical rooms</a><div class="card border-0 shadow-sm mt-3"><div class="card-body p-4"><h1 class="h3 mb-1">Add physical room</h1><p class="text-muted mb-4">Assign an inventory number to an existing room type.</p>@include('hotel.rooms._form', ['formAction' => route('hotel.rooms.store'), 'submitLabel' => 'Add room'])</div></div></div></div></div>
@endsection
