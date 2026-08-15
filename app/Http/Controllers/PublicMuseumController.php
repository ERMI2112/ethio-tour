<?php

namespace App\Http\Controllers;

use App\Models\MuseumInformation;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicMuseumController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('q')->trim()->value();

        $museums = MuseumInformation::query()
            ->when($search, fn ($query) => $query->where(function ($query) use ($search): void {
                $query->where('museum_name', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            }))
            ->orderBy('museum_name')
            ->get();

        return view('public.museums.index', compact('museums', 'search'));
    }

    public function show(MuseumInformation $museumInformation): View
    {
        return view('public.museums.show', ['museum' => $museumInformation]);
    }
}
