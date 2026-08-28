<?php

use App\Http\Middleware\EnsureEventOrganizer;
use App\Http\Middleware\EnsureHotelProvider;
use App\Http\Middleware\EnsureRestaurantProvider;
use App\Http\Middleware\EnsureTransportationProvider;
use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule): void {
        // Complete confirmed bookings whose service window has ended.
        $schedule->command('bookings:complete')->hourly()->withoutOverlapping();

        // Remove stale UAT-prefixed records left by prior seeder runs.
        $schedule->command('cleanup:stale-uat')->daily()->withoutOverlapping();
    })
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: ['payments/chapa/webhook']);
        $middleware->web(append: [SecurityHeaders::class]);
        $middleware->alias([
            'active' => EnsureUserIsActive::class,
            'role' => EnsureUserHasRole::class,
            'hotel-provider' => EnsureHotelProvider::class,
            'restaurant-provider' => EnsureRestaurantProvider::class,
            'transportation-provider' => EnsureTransportationProvider::class,
            'event-organizer' => EnsureEventOrganizer::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
