<?php

use App\Services\BookingCompletionService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('bookings:complete', function (BookingCompletionService $completion): void {
    $count = $completion->completeDueBookings();
    $this->info("Completed {$count} booking(s).");
})->purpose('Complete confirmed bookings whose service has ended');
