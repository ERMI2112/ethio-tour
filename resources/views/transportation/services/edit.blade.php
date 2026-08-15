@extends('layouts.app')
@section('title','Edit Transportation Service')
@section('content')<div class="container py-4"><div class="card border-0 shadow-sm"><div class="card-body p-4"><h1 class="h3 mb-4">Edit transportation service</h1><form method="POST" action="{{ route('transportation.services.update',$service) }}">@method('PUT')@include('transportation.services._form')<button class="btn btn-primary mt-4">Save changes</button></form></div></div></div>@endsection
