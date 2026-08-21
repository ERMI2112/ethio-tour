<?php

namespace App\Http\Controllers;

use App\Support\WorkspaceHome;
use Illuminate\Http\RedirectResponse;

class PublicPortalController extends Controller
{
    public function __invoke(): RedirectResponse
    {
        if (auth()->check() && auth()->user()->role !== 'tourist') {
            return to_route(WorkspaceHome::routeNameFor(auth()->user()));
        }

        return to_route('home');
    }
}
