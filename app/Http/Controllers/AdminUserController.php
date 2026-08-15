<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('q')->trim()->value();
        $role = $request->string('role')->trim()->value();
        $active = $request->input('active');
        $users = User::query()->when($search, fn ($q) => $q->where('email', 'like', "%{$search}%"))->when($role, fn ($q) => $q->where('role', $role))->when(in_array($active, ['0', '1'], true), fn ($q) => $q->where('is_active', (bool) $active))->orderBy('email')->paginate(15)->withQueryString();

        return view('admin.users.index', compact('users', 'search', 'role', 'active'));
    }

    public function toggle(Request $request, User $user, AuditService $audit): RedirectResponse
    {
        abort_if((int) $request->user()->user_id === (int) $user->user_id, 422, 'You cannot deactivate your own administrator account.');
        $user->forceFill(['is_active' => ! $user->is_active])->save();
        $audit->record($request->user(), $user->is_active ? 'user_activated' : 'user_deactivated', User::class, $user->user_id);

        return back()->with('success', 'User account state updated.');
    }
}
