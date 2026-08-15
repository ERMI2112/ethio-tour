@extends('layouts.app')

@section('title', 'Museum Information')

@section('content')
<div class="container py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <p class="text-uppercase small text-primary fw-semibold mb-1">Tourism Bureau</p>
            <h1 class="h2 mb-1">Museum Information</h1>
            <p class="text-muted mb-0">Publish and maintain museum information for tourists.</p>
        </div>
        <a class="btn btn-primary" href="{{ route('bureau.museums.create') }}">Add museum</a>
    </div>

    @if ($museums->isEmpty())
        <x-ui.empty-state title="No museum information yet" message="Create a museum record to publish it on the public museum directory." />
    @else
        <div class="table-responsive">
            <table class="table align-middle bg-white shadow-sm">
                <thead><tr><th>Museum</th><th>Location</th><th>Opening hours</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                    @foreach ($museums as $museum)
                        <tr>
                            <td>{{ $museum->museum_name }}</td>
                            <td>{{ $museum->location }}</td>
                            <td>{{ $museum->opening_hours }}</td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('bureau.museums.edit', $museum) }}">Edit</a>
                                <form class="d-inline" method="POST" action="{{ route('bureau.museums.destroy', $museum) }}" onsubmit="return confirm('Remove this museum from publication?');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Remove</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
