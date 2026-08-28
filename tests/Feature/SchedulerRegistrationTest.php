<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class SchedulerRegistrationTest extends TestCase
{
    private function scheduleListOutput(): string
    {
        // schedule:list renders every registered event with its cron expression.
        $exitCode = Artisan::call('schedule:list');

        $this->assertSame(0, $exitCode);

        return Artisan::output();
    }

    public function test_booking_completion_is_scheduled_recurring(): void
    {
        $output = $this->scheduleListOutput();

        $this->assertStringContainsString('bookings:complete', $output);
        $this->assertStringContainsString('0 * * * *', $output);
    }

    public function test_stale_uat_cleanup_is_scheduled_daily(): void
    {
        $output = $this->scheduleListOutput();

        $this->assertStringContainsString('cleanup:stale-uat', $output);
        $this->assertStringContainsString('0 0 * * *', $output);
    }
}
