@extends('layouts.app')

@section('title', 'Add Museum')

@section('content')
<div class="container py-4">
    <a class="link-secondary d-inline-block mb-3" href="{{ route('bureau.museums.index') }}">&larr; Back to museum information</a>
    <div class="card border-0 shadow-sm"><div class="card-body p-4">
        <h1 class="h3 mb-4">Add museum information</h1>
        <form method="POST" action="{{ route('bureau.museums.store') }}">
            @include('bureau.museums._form')
            <div class="mt-4"><button class="btn btn-primary" type="submit">Publish museum</button> <a class="btn btn-link" href="{{ route('bureau.museums.index') }}">Cancel</a></div>
        </form>
    </div></div>
</div>
@endsection
