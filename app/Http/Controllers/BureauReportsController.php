<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\ServiceProvider;
use App\Models\TourGuide;
use Illuminate\View\View;

class BureauReportsController extends Controller
{
    public function index(): View
    {
        return view('bureau.reports.index', [
            'guideStates' => TourGuide::selectRaw('verification_status, count(*) as total')->groupBy('verification_status')->pluck('total', 'verification_status'),
            'providerStates' => ServiceProvider::selectRaw('verification_status, count(*) as total')->groupBy('verification_status')->pluck('total', 'verification_status'),
            'recentDecisions' => AuditLog::with('actor')->whereIn('action', ['guide_verification_decision', 'provider_verification_decision'])->latest()->limit(15)->get(),
        ]);
    }
}
