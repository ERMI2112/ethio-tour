@extends('layouts.app')

@section('title', 'Edit Physical Room')

@section('content')
<div class="container py-4 py-lg-5"><div class="row justify-content-center"><div class="col-lg-8">
<nav aria-label="breadcrumb"><ol class="breadcrumb small mb-2"><li class="breadcrumb-item"><a href="{{ route('hotel.dashboard') }}">Hotel Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('hotel.rooms.index') }}">Physical Rooms</a></li><li class="breadcrumb-item active" aria-current="page">Edit</li></ol></nav>
<div class="card border-0 shadow-sm mt-3"><div class="card-body p-4"><h1 class="h3 mb-1">Edit room {{ $room->room_number }}</h1><p class="text-muted mb-4">Change its room type, number, or operational state.</p>@include('hotel.rooms._form', ['formAction' => route('hotel.rooms.update', $room), 'submitLabel' => 'Save changes'])</div></div></div></div></div>
@endsection
