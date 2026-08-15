<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AdminAuditController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminProviderController;
use App\Http\Controllers\AdminSubscriptionController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\BureauDashboardController;
use App\Http\Controllers\BureauGuideVerificationController;
use App\Http\Controllers\BureauMuseumController;
use App\Http\Controllers\BureauProviderVerificationController;
use App\Http\Controllers\CulturalEventController;
use App\Http\Controllers\EventOrganizerController;
use App\Http\Controllers\EventReservationController;
use App\Http\Controllers\EventTicketController;
use App\Http\Controllers\EventTouristBookingController;
use App\Http\Controllers\HotelProviderController;
use App\Http\Controllers\HotelProviderReservationController;
use App\Http\Controllers\HotelReservationController;
use App\Http\Controllers\HotelRoomController;
use App\Http\Controllers\HotelServiceController;
use App\Http\Controllers\ProviderOnboardingController;
use App\Http\Controllers\PublicCategoryController;
use App\Http\Controllers\PublicCulturalEventController;
use App\Http\Controllers\PublicDestinationController;
use App\Http\Controllers\PublicHeritageSiteController;
use App\Http\Controllers\PublicMuseumController;
use App\Http\Controllers\PublicTourGuideController;
use App\Http\Controllers\PublicTourismServiceController;
use App\Http\Controllers\PublicTransportationController;
use App\Http\Controllers\RestaurantProviderController;
use App\Http\Controllers\RestaurantReservationController;
use App\Http\Controllers\RestaurantServiceController;
use App\Http\Controllers\RestaurantTableController;
use App\Http\Controllers\RestaurantTouristReservationController;
use App\Http\Controllers\TourGuideBookingController;
use App\Http\Controllers\TourGuideBookingRequestController;
use App\Http\Controllers\TourGuidePortalController;
use App\Http\Controllers\TouristReservationController;
use App\Http\Controllers\TransportationProviderController;
use App\Http\Controllers\TransportationReservationController;
use App\Http\Controllers\TransportationServiceController;
use App\Http\Controllers\TransportationTouristReservationController;
use App\Http\Controllers\TransportationVehicleController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/destinations', [PublicDestinationController::class, 'index'])->name('destinations.index');
Route::get('/destinations/{destination}', [PublicDestinationController::class, 'show'])->name('destinations.show');
Route::get('/heritage-sites', [PublicHeritageSiteController::class, 'index'])->name('heritage-sites.index');
Route::get('/heritage-sites/{heritageSite}', [PublicHeritageSiteController::class, 'show'])->name('heritage-sites.show');
Route::get('/tourism-services', [PublicTourismServiceController::class, 'index'])->name('tourism-services.index');
Route::get('/tourism-services/{tourismService}', [PublicTourismServiceController::class, 'show'])->name('tourism-services.show');
Route::get('/museums', [PublicMuseumController::class, 'index'])->name('museums.index');
Route::get('/museums/{museumInformation}', [PublicMuseumController::class, 'show'])->name('museums.show');
Route::get('/transportation', [PublicTransportationController::class, 'index'])->name('transportation.index');
Route::get('/transportation/{tourismService}', [PublicTransportationController::class, 'show'])->name('transportation.show');
Route::get('/events', [PublicCulturalEventController::class, 'index'])->name('events.index');
Route::get('/events/{culturalEvent}', [PublicCulturalEventController::class, 'show'])->name('events.show');
Route::post('/transportation/{tourismService}/availability', [TransportationTouristReservationController::class, 'checkAvailability'])->name('transportation.availability');
Route::post('/restaurants/{tourismService}/availability', [RestaurantTouristReservationController::class, 'checkAvailability'])->name('restaurants.availability');
Route::post('/tourism-services/{tourismService}/check-availability', [HotelReservationController::class, 'checkAvailability'])->name('tourism-services.check-availability');
Route::get('/categories', [PublicCategoryController::class, 'index'])->name('categories.index');
Route::get('/tour-guides', [PublicTourGuideController::class, 'index'])->name('tour-guides.index');
Route::get('/tour-guides/{guide}', [PublicTourGuideController::class, 'show'])->name('tour-guides.show');

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])->name('register.store');
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:5,1')->name('login.store');
    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->middleware('throttle:5,1')->name('password.email');
    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.update');
});

Route::middleware(['auth', 'active'])->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/account', AccountController::class)->name('account');
    Route::get('/confirm-password', [ConfirmablePasswordController::class, 'create'])->name('password.confirm');
    Route::post('/confirm-password', [ConfirmablePasswordController::class, 'store'])->name('password.confirm.store');

    Route::view('/access-check/tourist', 'account.access-check')->middleware('role:tourist')->defaults('role', 'Tourist');
    Route::view('/access-check/tour-guide', 'account.access-check')->middleware('role:tour_guide')->defaults('role', 'Tour Guide');
    Route::view('/access-check/service-provider', 'account.access-check')->middleware('role:service_provider')->defaults('role', 'Service Provider');
    Route::view('/access-check/bureau', 'account.access-check')->middleware('role:tourism_bureau_officer')->defaults('role', 'Tourism Bureau Officer');
    Route::view('/access-check/administrator', 'account.access-check')->middleware('role:administrator')->defaults('role', 'Administrator');

    Route::prefix('admin')->name('admin.')->middleware('role:administrator')->group(function () {
        Route::get('/', AdminDashboardController::class)->name('dashboard');
        Route::get('/providers', [AdminProviderController::class, 'index'])->name('providers.index');
        Route::get('/providers/{serviceProvider}', [AdminProviderController::class, 'show'])->name('providers.show');
        Route::patch('/providers/{serviceProvider}/status', [AdminProviderController::class, 'updateStatus'])->name('providers.status');
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::patch('/users/{user}/toggle', [AdminUserController::class, 'toggle'])->name('users.toggle');
        Route::get('/subscriptions', [AdminSubscriptionController::class, 'index'])->name('subscriptions.index');
        Route::post('/subscriptions', [AdminSubscriptionController::class, 'store'])->name('subscriptions.store');
        Route::put('/subscriptions/{subscriptionPlan}', [AdminSubscriptionController::class, 'update'])->name('subscriptions.update');
        Route::get('/audit', [AdminAuditController::class, 'index'])->name('audit.index');
    });

    Route::prefix('tour-guide')->name('tour-guide.')->middleware('role:tour_guide')->group(function () {
        Route::get('/', [TourGuidePortalController::class, 'dashboard'])->name('dashboard');
        Route::get('/profile', [TourGuidePortalController::class, 'showProfile'])->name('profile');
        Route::get('/profile/edit', [TourGuidePortalController::class, 'editProfile'])->name('profile.edit');
        Route::put('/profile', [TourGuidePortalController::class, 'updateProfile'])->name('profile.update');
        Route::get('/availability', [TourGuideBookingRequestController::class, 'availability'])->name('availability');
        Route::get('/requests', [TourGuideBookingRequestController::class, 'index'])->name('requests.index');
        Route::get('/requests/{booking}', [TourGuideBookingRequestController::class, 'show'])->name('requests.show');
        Route::patch('/requests/{booking}/accept', [TourGuideBookingRequestController::class, 'accept'])->name('requests.accept');
        Route::patch('/requests/{booking}/reject', [TourGuideBookingRequestController::class, 'reject'])->name('requests.reject');
    });

    Route::middleware('role:tourist')->prefix('tourist')->name('tourist.')->group(function () {
        Route::post('/services/{tourismService}/reservations', [HotelReservationController::class, 'store'])->name('reservations.store');
        Route::get('/reservations', [TouristReservationController::class, 'index'])->name('reservations.index');
        Route::get('/reservations/{booking}', [TouristReservationController::class, 'show'])->name('reservations.show');
        Route::patch('/reservations/{booking}/cancel', [TouristReservationController::class, 'cancel'])->name('reservations.cancel');
        Route::post('/services/{tourismService}/restaurant-reservations', [RestaurantTouristReservationController::class, 'store'])->name('restaurant-reservations.store');
        Route::post('/services/{tourismService}/transportation-reservations', [TransportationTouristReservationController::class, 'store'])->name('transportation-reservations.store');
        Route::post('/events/{culturalEvent}/reservations', [EventTouristBookingController::class, 'store'])->name('event-reservations.store');
    });

    Route::middleware('role:service_provider')->prefix('provider')->name('provider.')->group(function () {
        Route::get('/status', [ProviderOnboardingController::class, 'show'])->name('status');
        Route::get('/profile', [ProviderOnboardingController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [ProviderOnboardingController::class, 'update'])->name('profile.update');
    });

    Route::middleware('role:tourist')->prefix('tour-guides/{guide}')->name('tour-guides.')->group(function () {
        Route::get('/book', [TourGuideBookingController::class, 'create'])->name('book');
        Route::post('/book', [TourGuideBookingController::class, 'store'])->name('book.store');
    });

    Route::prefix('hotel')->name('hotel.')->middleware(['role:service_provider', 'hotel-provider'])->group(function () {
        Route::get('/', [HotelProviderController::class, 'dashboard'])->name('dashboard');
        Route::get('/profile', [HotelProviderController::class, 'show'])->name('profile');
        Route::get('/profile/edit', [HotelProviderController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [HotelProviderController::class, 'update'])->name('profile.update');

        Route::resource('services', HotelServiceController::class)->except(['show'])->parameters(['services' => 'tourismService']);
        Route::resource('rooms', HotelRoomController::class)->except(['show'])->parameters(['rooms' => 'hotelRoom']);

        Route::get('/reservations', [HotelProviderReservationController::class, 'index'])->name('reservations.index');
        Route::get('/reservations/{booking}', [HotelProviderReservationController::class, 'show'])->name('reservations.show');
        Route::patch('/reservations/{booking}/accept', [HotelProviderReservationController::class, 'accept'])->name('reservations.accept');
        Route::patch('/reservations/{booking}/reject', [HotelProviderReservationController::class, 'reject'])->name('reservations.reject');
    });

    Route::prefix('restaurant')->name('restaurant.')->middleware(['role:service_provider', 'restaurant-provider'])->group(function () {
        Route::get('/', [RestaurantProviderController::class, 'dashboard'])->name('dashboard');
        Route::get('/profile', [RestaurantProviderController::class, 'show'])->name('profile');
        Route::get('/profile/edit', [RestaurantProviderController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [RestaurantProviderController::class, 'update'])->name('profile.update');
        Route::resource('services', RestaurantServiceController::class)->except(['show'])->parameters(['services' => 'tourismService']);
        Route::resource('tables', RestaurantTableController::class)->except(['show'])->parameters(['tables' => 'restaurantTable']);
        Route::get('/reservations', [RestaurantReservationController::class, 'index'])->name('reservations.index');
        Route::get('/reservations/{booking}', [RestaurantReservationController::class, 'show'])->name('reservations.show');
        Route::patch('/reservations/{booking}/accept', [RestaurantReservationController::class, 'accept'])->name('reservations.accept');
        Route::patch('/reservations/{booking}/reject', [RestaurantReservationController::class, 'reject'])->name('reservations.reject');
    });

    Route::prefix('transportation-portal')->name('transportation.')->middleware(['role:service_provider', 'transportation-provider'])->group(function () {
        Route::get('/', [TransportationProviderController::class, 'dashboard'])->name('dashboard');
        Route::get('/profile', [TransportationProviderController::class, 'show'])->name('profile');
        Route::get('/profile/edit', [TransportationProviderController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [TransportationProviderController::class, 'update'])->name('profile.update');
        Route::resource('services', TransportationServiceController::class)->except(['show'])->parameters(['services' => 'tourismService']);
        Route::resource('vehicles', TransportationVehicleController::class)->except(['show'])->parameters(['vehicles' => 'transportationVehicle']);
        Route::get('/reservations', [TransportationReservationController::class, 'index'])->name('reservations.index');
        Route::get('/reservations/{booking}', [TransportationReservationController::class, 'show'])->name('reservations.show');
        Route::patch('/reservations/{booking}/accept', [TransportationReservationController::class, 'accept'])->name('reservations.accept');
        Route::patch('/reservations/{booking}/reject', [TransportationReservationController::class, 'reject'])->name('reservations.reject');
    });

    Route::prefix('event-organizer')->name('event-organizer.')->middleware(['role:service_provider', 'event-organizer'])->group(function () {
        Route::get('/', [EventOrganizerController::class, 'dashboard'])->name('dashboard');
        Route::get('/profile', [EventOrganizerController::class, 'profile'])->name('profile');
        Route::resource('events', CulturalEventController::class)->parameters(['events' => 'culturalEvent']);
        Route::get('/events/{culturalEvent}/tickets', [EventTicketController::class, 'index'])->name('events.tickets');
        Route::post('/events/{culturalEvent}/tickets', [EventTicketController::class, 'store'])->name('events.tickets.store');
        Route::put('/events/{culturalEvent}/tickets/{eventTicketType}', [EventTicketController::class, 'update'])->name('events.tickets.update');
        Route::delete('/events/{culturalEvent}/tickets/{eventTicketType}', [EventTicketController::class, 'destroy'])->name('events.tickets.destroy');
        Route::get('/events-bookings', [EventReservationController::class, 'index'])->name('events.bookings');
    });

    Route::prefix('bureau')->name('bureau.')->middleware('role:tourism_bureau_officer')->group(function () {
        Route::get('/', BureauDashboardController::class)->name('dashboard');
        Route::get('/guides', [BureauGuideVerificationController::class, 'index'])->name('guides.index');
        Route::get('/guides/{tourGuide}', [BureauGuideVerificationController::class, 'show'])->name('guides.show');
        Route::patch('/guides/{tourGuide}/decision', [BureauGuideVerificationController::class, 'decide'])->name('guides.decide');
        Route::get('/providers', [BureauProviderVerificationController::class, 'index'])->name('providers.index');
        Route::get('/providers/{serviceProvider}', [BureauProviderVerificationController::class, 'show'])->name('providers.show');
        Route::patch('/providers/{serviceProvider}/decision', [BureauProviderVerificationController::class, 'decide'])->name('providers.decide');
        Route::resource('museums', BureauMuseumController::class)->except(['show'])->parameters(['museums' => 'museumInformation']);
    });
});
