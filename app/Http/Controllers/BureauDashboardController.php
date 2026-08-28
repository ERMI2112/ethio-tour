<?php

namespace App\Http\Controllers;

use App\Models\Attraction;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\MuseumInformation;
use App\Models\Payment;
use App\Models\ServiceProvider;
use App\Models\TourGuide;
use App\Models\Tourist;
use App\Models\VerificationDocument;
use Illuminate\View\View;

class BureauDashboardController extends Controller
{
    public function __invoke(): View
    {
        $monthStart = now()->copy()->subMonths(5)->startOfMonth();
        $bookingActivity = Booking::where('booking_date', '>=', $monthStart)
            ->get(['booking_date'])
            ->groupBy(fn (Booking $booking): string => $booking->booking_date->format('Y-m'))
            ->map->count();
        $bookingActivityMonths = collect(range(5, 0))->map(function (int $monthsAgo) use ($bookingActivity): array {
            $month = now()->copy()->subMonths($monthsAgo);
            $key = $month->format('Y-m');

            return ['label' => $month->format('M Y'), 'total' => (int) ($bookingActivity[$key] ?? 0)];
        })->all();

        return view('bureau.dashboard', [
            'activeTourists' => Tourist::whereHas('user', fn ($query) => $query->where('is_active', true))->count(),
            'certifiedProviders' => ServiceProvider::where('verification_status', 'verified')->count(),
            'pendingDocuments' => VerificationDocument::where('status', 'pending')->count(),
            'activeEscrowVolume' => (float) Payment::where('status', 'pending')->sum('amount'),
            'pendingGuides' => TourGuide::where('verification_status', 'pending')->count(),
            'pendingFinalGuides' => TourGuide::where('verification_status', 'verified')->where('admin_approval_status', 'pending')->count(),
            'verifiedGuides' => TourGuide::where('verification_status', 'verified')->count(),
            'pendingProviders' => ServiceProvider::where('verification_status', 'pending')->count(),
            'approvedProviders' => ServiceProvider::where('verification_status', 'verified')->where('status', 'approved')->count(),
            'suspendedProviders' => ServiceProvider::where('status', 'suspended')->count(),
            'bookingActivityMonths' => $bookingActivityMonths,
            'museumCount' => MuseumInformation::count(),
            'attractionCount' => Attraction::count(),
            'recentDecisions' => AuditLog::with('actor')->whereIn('action', ['guide_verification_decided', 'provider_verification_decided'])->latest()->limit(6)->get(),
        ]);
    }
}
