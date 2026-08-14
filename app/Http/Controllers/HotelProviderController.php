<?php

namespace App\Http\Controllers;

use App\Http\Requests\HotelProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HotelProviderController extends Controller
{
    public function show(Request $request): View
    {
        $provider = $request->user()->serviceProvider;
        $provider->loadCount('tourismServices');

        return view('hotel.profile.show', compact('provider'));
    }

    public function edit(Request $request): View
    {
        return view('hotel.profile.edit', ['provider' => $request->user()->serviceProvider]);
    }

    public function update(HotelProfileRequest $request): RedirectResponse
    {
        $request->user()->serviceProvider->update($request->validated());

        return to_route('hotel.profile')->with('success', 'Hotel provider profile updated.');
    }
}
