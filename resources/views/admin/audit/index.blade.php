@extends('layouts.app')
@section('title', 'Audit Log')
@section('content')
<div class="container py-4"><h1 class="h3">Audit Log</h1><div class="table-responsive"><table class="table"><thead><tr><th>When</th><th>Actor</th><th>Action</th><th>Subject</th></tr></thead><tbody>@forelse($entries as $entry)<tr><td>{{ $entry->created_at }}</td><td>{{ $entry->actor?->email }}</td><td>{{ $entry->action }}</td><td>{{ $entry->subject_type }} #{{ $entry->subject_id }}</td></tr>@empty<tr><td colspan="4">No audit activity recorded.</td></tr>@endforelse</tbody></table></div>{{ $entries->links() }}</div>
@endsection
