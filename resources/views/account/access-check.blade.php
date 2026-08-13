@extends('layouts.app')
@section('title', 'Access check')
@section('content')
    <div class="container py-5"><div class="alert alert-success mb-0">{{ $role }} authorization check passed.</div></div>
@endsection
