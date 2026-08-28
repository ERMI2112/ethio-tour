<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\ProviderSubscription;
use App\Models\Review;
use App\Models\ServiceProvider;
use App\Models\SubscriptionPlan;
use App\Models\TourGuide;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminReportsController extends Controller
{
    public function index(Request $request): View
    {
        $from = $request->date('from')?->startOfDay();
        $to = $request->date('to')?->endOfDay();
        $status = $request->string('status')->trim()->value();
        $providerType = $request->string('provider_type')->trim()->value();

        $bookings = Booking::query()->when($from, fn ($query) => $query->where('booking_date', '>=', $from))->when($to, fn ($query) => $query->where('booking_date', '<=', $to))->when(in_array($status, ['pending', 'accepted', 'rejected', 'payment_pending', 'confirmed', 'cancelled', 'completed'], true), fn ($query) => $query->where('status', $status));
        $domainBookings = Booking::query()->whereNotNull('service_id')->when($providerType, fn ($query) => $query->whereHas('tourismService.serviceProvider', fn ($provider) => $provider->where('provider_type', $providerType)))->when($from, fn ($query) => $query->where('booking_date', '>=', $from))->when($to, fn ($query) => $query->where('booking_date', '<=', $to));
        $roleBreakdown = User::query()->selectRaw('role, count(*) as total')->groupBy('role')->pluck('total', 'role');
        $statusBreakdown = (clone $bookings)->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $providerBreakdown = ServiceProvider::query()->selectRaw('provider_type, count(*) as total')->groupBy('provider_type')->pluck('total', 'provider_type');
        $reviewQuery = Review::query()->when($from, fn ($query) => $query->where('review_date', '>=', $from->toDateString()))->when($to, fn ($query) => $query->where('review_date', '<=', $to->toDateString()));

        // Platform monetization: commission actually captured at payment confirmation.
        $successfulPayments = Payment::query()->where('status', 'success')
            ->when($from, fn ($query) => $query->where('created_at', '>=', $from))
            ->when($to, fn ($query) => $query->where('created_at', '<=', $to));

        return view('admin.reports.index', [
            'from' => $from?->toDateString(), 'to' => $to?->toDateString(), 'status' => $status, 'providerType' => $providerType,
            'totalUsers' => User::count(), 'activeUsers' => User::where('is_active', true)->count(), 'roleBreakdown' => $roleBreakdown,
            'providerBreakdown' => $providerBreakdown, 'providerStatuses' => ServiceProvider::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
            'planTotal' => SubscriptionPlan::count(), 'activePlans' => SubscriptionPlan::where('active', true)->count(), 'inactivePlans' => SubscriptionPlan::where('active', false)->count(), 'activeSubscriptions' => ProviderSubscription::where('status', 'active')->count(),
            'totalGuides' => TourGuide::count(), 'verifiedGuides' => TourGuide::where('verification_status', 'verified')->count(), 'pendingGuides' => TourGuide::where('verification_status', 'pending')->count(),
            'bookingTotal' => (clone $bookings)->count(), 'statusBreakdown' => $statusBreakdown, 'domainBookings' => ['hotel' => (clone $domainBookings)->whereHas('tourismService.serviceProvider', fn ($q) => $q->where('provider_type', 'hotel'))->count(), 'restaurant' => (clone $domainBookings)->whereHas('tourismService.serviceProvider', fn ($q) => $q->where('provider_type', 'restaurant'))->count(), 'transportation' => (clone $domainBookings)->whereHas('tourismService.serviceProvider', fn ($q) => $q->where('provider_type', 'transportation_car_rental'))->count(), 'event' => (clone $domainBookings)->whereHas('tourismService.serviceProvider', fn ($q) => $q->where('provider_type', 'event_organizer'))->count(), 'guide' => (clone $bookings)->whereNotNull('guide_id')->count()],
            'reviewCount' => (clone $reviewQuery)->count(), 'reviewAverage' => (clone $reviewQuery)->avg('rating'), 'ratingDistribution' => (clone $reviewQuery)->selectRaw('rating, count(*) as total')->groupBy('rating')->orderBy('rating')->pluck('total', 'rating'),
            'recentAudit' => AuditLog::with('actor')->latest()->limit(10)->get(), 'recentReviews' => Review::with('tourist')->latest('review_date')->limit(8)->get(),
            'paymentVolume' => (clone $successfulPayments)->sum('amount'),
            'commissionTotal' => (clone $successfulPayments)->sum('commission_amount'),
            'providerNetTotal' => (clone $successfulPayments)->sum('provider_net_amount'),
            'commissionedPayments' => (clone $successfulPayments)->whereNotNull('commission_amount')->count(),
            'commissionByRate' => (clone $successfulPayments)->whereNotNull('commission_rate')->selectRaw('commission_rate, count(*) as payments, sum(commission_amount) as earned')->groupBy('commission_rate')->orderBy('commission_rate')->get(),
        ]);
    }
}
