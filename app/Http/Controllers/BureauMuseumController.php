<?php

namespace App\Http\Controllers;

use App\Http\Requests\MuseumInformationRequest;
use App\Models\MuseumInformation;
use App\Models\TourismBureauOfficer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BureauMuseumController extends Controller
{
    public function index(Request $request): View
    {
        $officer = $this->officerOrAbort($request);
        $museums = $officer->museumInformation()->orderBy('museum_name')->get();

        return view('bureau.museums.index', compact('museums'));
    }

    public function create(Request $request): View
    {
        $this->officerOrAbort($request);

        return view('bureau.museums.create');
    }

    public function store(MuseumInformationRequest $request): RedirectResponse
    {
        $this->officerOrAbort($request)->museumInformation()->create($request->validated());

        return to_route('bureau.museums.index')->with('success', 'Museum information published.');
    }

    public function edit(Request $request, MuseumInformation $museumInformation): View
    {
        $this->ensureOwned($request, $museumInformation);

        return view('bureau.museums.edit', ['museum' => $museumInformation]);
    }

    public function update(MuseumInformationRequest $request, MuseumInformation $museumInformation): RedirectResponse
    {
        $this->ensureOwned($request, $museumInformation);
        $museumInformation->update($request->validated());

        return to_route('bureau.museums.index')->with('success', 'Museum information updated.');
    }

    public function destroy(Request $request, MuseumInformation $museumInformation): RedirectResponse
    {
        $this->ensureOwned($request, $museumInformation);
        $museumInformation->delete();

        return to_route('bureau.museums.index')->with('success', 'Museum information removed from publication.');
    }

    private function ensureOwned(Request $request, MuseumInformation $museum): void
    {
        $officer = $this->officerOrAbort($request);
        abort_unless((int) $museum->officer_id === (int) $officer->officer_id, 403);
    }

    private function officerOrAbort(Request $request): TourismBureauOfficer
    {
        return $request->user()->tourismBureauOfficer ?? abort(403);
    }
}
