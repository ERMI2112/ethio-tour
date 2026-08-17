<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
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
            'pendingProviders' => ServiceProvider::where('verification_status', 'pending')->count(),
            'approvedProviders' => ServiceProvider::where('verification_status', 'verified')->where('status', 'approved')->count(),
            'museumCount' => MuseumInformation::count(),
            'recentDecisions' => AuditLog::with('actor')->whereIn('action', ['guide_verification_decided', 'provider_verification_decided'])->latest()->limit(6)->get(),
        ]);
    }
}
