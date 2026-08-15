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
        $users = User::query()->when($search, fn ($q) => $q->where('email', 'like', "%{$search}%"))->orderBy('email')->get();

        return view('admin.users.index', compact('users', 'search'));
    }

    public function toggle(Request $request, User $user, AuditService $audit): RedirectResponse
    {
        abort_if((int) $request->user()->user_id === (int) $user->user_id, 422, 'You cannot deactivate your own administrator account.');
        $user->forceFill(['is_active' => ! $user->is_active])->save();
        $audit->record($request->user(), $user->is_active ? 'user_activated' : 'user_deactivated', User::class, $user->user_id);

        return back()->with('success', 'User account state updated.');
    }
}
