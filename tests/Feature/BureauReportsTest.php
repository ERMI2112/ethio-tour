<?php

use App\Models\AuditLog;
use App\Models\TourismBureauOfficer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BureauReportsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_shows_decisions_using_the_audit_actions_written_by_bureau_workflows(): void
    {
        $user = User::factory()->create(['role' => 'tourism_bureau_officer', 'is_active' => true]);
        TourismBureauOfficer::create(['user_id' => $user->user_id]);
        AuditLog::create([
            'actor_user_id' => $user->user_id,
            'action' => 'provider_verification_decided',
            'subject_type' => 'App\\Models\\ServiceProvider',
            'subject_id' => 1,
        ]);

        $this->actingAs($user)
            ->get(route('bureau.reports.index'))
            ->assertOk()
            ->assertSee('Provider verification decided');
    }
}
