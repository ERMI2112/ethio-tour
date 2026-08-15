<?php

namespace App\Http\Controllers;

use App\Models\MuseumInformation;
use App\Models\ServiceProvider;
use App\Models\TourGuide;
use Illuminate\View\View;

class BureauDashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('bureau.dashboard', [
            'pendingGuides' => TourGuide::where('verification_status', 'pending')->count(),
            'verifiedGuides' => TourGuide::where('verification_status', 'verified')->count(),
            'pendingProviders' => ServiceProvider::where('status', 'pending')->count(),
            'approvedProviders' => ServiceProvider::where('status', 'approved')->count(),
            'museumCount' => MuseumInformation::count(),
        ]);
    }
}
