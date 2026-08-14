<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\HotelProviderController;
use App\Http\Controllers\HotelProviderReservationController;
use App\Http\Controllers\HotelReservationController;
use App\Http\Controllers\HotelRoomController;
use App\Http\Controllers\HotelServiceController;
use App\Http\Controllers\PublicCategoryController;
use App\Http\Controllers\PublicDestinationController;
use App\Http\Controllers\PublicHeritageSiteController;
use App\Http\Controllers\PublicTourismServiceController;
use App\Http\Controllers\TouristReservationController;
use App\Http\Controllers\TourGuidePortalController;
use App\Http\Controllers\TourGuideBookingRequestController;
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
Route::post('/tourism-services/{tourismService}/check-availability', [HotelReservationController::class, 'checkAvailability'])->name('tourism-services.check-availability');
Route::get('/categories', [PublicCategoryController::class, 'index'])->name('categories.index');

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
});
