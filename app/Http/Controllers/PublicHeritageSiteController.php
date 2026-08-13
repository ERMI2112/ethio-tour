<?php

namespace App\Http\Controllers;

use App\Models\HeritageSite;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicHeritageSiteController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('q')->trim()->value();

        $heritageSites = HeritageSite::query()
            ->with('destination')
            ->when($search, fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('heritage_type', 'like', "%{$search}%")
                    ->orWhereHas('destination', fn ($query) => $query->where('name', 'like', "%{$search}%"));
            }))
            ->orderBy('heritage_type')
            ->get();

        return view('public.heritage-sites.index', compact('heritageSites', 'search'));
    }

    public function show(HeritageSite $heritageSite): View
    {
        $heritageSite->load('destination');

        return view('public.heritage-sites.show', compact('heritageSite'));
    }
}
