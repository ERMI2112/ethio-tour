@extends('layouts.app')
@section('title','Edit Vehicle')
@section('content')<div class="container py-4"><div class="card border-0 shadow-sm"><div class="card-body p-4"><h1 class="h3 mb-4">Edit vehicle</h1><form method="POST" action="{{ route('transportation.vehicles.update',$vehicle) }}">@method('PUT')@include('transportation.vehicles._form')<button class="btn btn-primary mt-4">Save changes</button></form></div></div></div>@endsection
