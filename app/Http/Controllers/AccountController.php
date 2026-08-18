<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateTouristProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function __invoke(Request $request): View
    {
        if ($request->user()->role === 'tourist' && $request->user()->tourist) {
            return view('tourist.profile.show', ['tourist' => $request->user()->tourist]);
        }

        return view('account.placeholder', ['user' => $request->user()]);
    }

    public function editTouristProfile(Request $request): View
    {
        abort_unless($request->user()->role === 'tourist' && $request->user()->tourist, 403);

        return view('tourist.profile.edit', ['tourist' => $request->user()->tourist]);
    }

    public function showTouristProfile(Request $request): View
    {
        abort_unless($request->user()->role === 'tourist' && $request->user()->tourist, 403);

        return view('tourist.profile.show', ['tourist' => $request->user()->tourist]);
    }

    public function updateTouristProfile(UpdateTouristProfileRequest $request): RedirectResponse
    {
        $request->user()->tourist->update($request->validated());

        return to_route('tourist.profile')->with('success', 'Your profile was updated.');
    }
}
