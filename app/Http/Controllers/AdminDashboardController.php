<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\Review;
use App\Models\ServiceProvider;
use App\Models\TourGuide;
use App\Models\User;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __invoke(): View
    {
        $administrator = request()->user();
        $adminNotifications = $administrator->notifications()->latest('sent_date')->limit(6)->get();
        $adminUnreadNotifications = $administrator->notifications()->where('read_status', false)->count();

        $activeProviders = ServiceProvider::where('verification_status', 'verified')
            ->where('status', 'approved')
            ->whereHas('user', fn ($query) => $query->where('is_active', true))
            ->count();
        $pendingProviderActions = ServiceProvider::where('verification_status', 'verified')
            ->where('status', 'pending')
            ->count();

        return view('admin.dashboard', [
            'totalUsers' => User::count(),
            'activeUsers' => User::where('is_active', true)->count(),
            'pendingProviders' => ServiceProvider::where('verification_status', 'verified')->where('status', 'pending')->count(),
            'approvedProviders' => ServiceProvider::where('verification_status', 'verified')
                ->where('status', 'approved')
                ->whereHas('user', fn ($query) => $query->where('is_active', true))
                ->count(),
            'activeProviders' => $activeProviders,
            'suspendedProviders' => ServiceProvider::where('status', 'suspended')->count(),
            'rejectedProviders' => ServiceProvider::where('status', 'rejected')->count(),
            'verifiedGuides' => TourGuide::where('verification_status', 'verified')->count(),
            'pendingGuides' => TourGuide::where('verification_status', 'pending')->count(),
            'bookings' => Booking::count(),
            'reviewCount' => Review::count(),
            'auditEntries' => AuditLog::count(),
            'recentAudit' => AuditLog::with('actor')->latest()->limit(8)->get(),
            'pendingActions' => $pendingProviderActions,
            'pendingProviderActions' => $pendingProviderActions,
            'adminNotifications' => $adminNotifications,
            'adminUnreadNotifications' => $adminUnreadNotifications,
        ]);
    }
}
