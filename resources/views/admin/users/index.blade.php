@extends('layouts.app')
@section('title', 'User Management')
@section('content')
<div class="container py-4"><h1 class="h3">User Management</h1><form class="mb-3"><input class="form-control" name="q" value="{{ $search }}" placeholder="Search email"><button class="btn btn-outline-primary mt-2">Search</button></form><div class="table-responsive"><table class="table"><thead><tr><th>Email</th><th>Role</th><th>Active</th><th></th></tr></thead><tbody>@foreach($users as $user)<tr><td>{{ $user->email }}</td><td>{{ $user->role }}</td><td>{{ $user->is_active ? 'Yes' : 'No' }}</td><td>@if($user->user_id !== auth()->id())<form method="POST" action="{{ route('admin.users.toggle',$user) }}">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-secondary">{{ $user->is_active ? 'Deactivate' : 'Activate' }}</button></form>@else<span class="text-muted">Current account</span>@endif</td></tr>@endforeach</tbody></table></div></div>
@endsection
