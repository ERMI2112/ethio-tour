<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\ServiceProvider;
use App\Models\TourGuide;
use App\Models\User;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'totalUsers' => User::count(),
            'activeUsers' => User::where('is_active', true)->count(),
            'pendingProviders' => ServiceProvider::where('verification_status', 'verified')->where('status', 'pending')->count(),
            'approvedProviders' => ServiceProvider::where('status', 'approved')->count(),
            'suspendedProviders' => ServiceProvider::where('status', 'suspended')->count(),
            'rejectedProviders' => ServiceProvider::where('status', 'rejected')->count(),
            'verifiedGuides' => TourGuide::where('verification_status', 'verified')->count(),
            'pendingGuides' => TourGuide::where('verification_status', 'pending')->count(),
            'bookings' => Booking::count(),
            'auditEntries' => AuditLog::count(),
        ]);
    }
}
