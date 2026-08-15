@extends('layouts.app')
@section('title','Add Vehicle')
@section('content')<div class="container py-4"><div class="card border-0 shadow-sm"><div class="card-body p-4"><h1 class="h3 mb-4">Add physical vehicle</h1><form method="POST" action="{{ route('transportation.vehicles.store') }}">@include('transportation.vehicles._form')<button class="btn btn-primary mt-4">Add vehicle</button></form></div></div></div>@endsection
