<?php

namespace Tests\Feature;

use App\Models\ServiceProvider;
use App\Models\TourGuide;
use App\Models\TourismBureauOfficer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BureauOperationalPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_active_bureau_officer_can_access_operational_portal(): void
    {
        $bureau = $this->bureau();
        $this->actingAs($bureau)->get(route('bureau.dashboard'))->assertOk();

        foreach (['tourist', 'tour_guide', 'service_provider', 'administrator'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]))->get(route('bureau.dashboard'))->assertForbidden();
        }

        $inactive = User::factory()->create(['role' => 'tourism_bureau_officer', 'is_active' => false]);
        TourismBureauOfficer::create(['user_id' => $inactive->user_id]);
        $this->actingAs($inactive)->get(route('bureau.dashboard'))->assertRedirect();
    }

    public function test_bureau_can_approve_and_reject_pending_guides_with_reason(): void
    {
        $bureau = $this->bureau();
        $pending = $this->guide('pending');
        $rejected = $this->guide('pending');

        $this->actingAs($bureau)->get(route('bureau.guides.index'))->assertOk()->assertSee($pending->license_number);
        $this->actingAs($bureau)->patch(route('bureau.guides.decide', $pending), ['decision' => 'approve'])->assertRedirect();
        $this->assertDatabaseHas('tour_guides', ['guide_id' => $pending->guide_id, 'verification_status' => 'verified']);

        $this->actingAs($bureau)->patch(route('bureau.guides.decide', $rejected), ['decision' => 'reject'])->assertSessionHasErrors('verification_notes');
        $this->actingAs($bureau)->patch(route('bureau.guides.decide', $rejected), ['decision' => 'reject', 'verification_notes' => 'License evidence is incomplete.'])->assertRedirect();
        $this->assertDatabaseHas('tour_guides', ['guide_id' => $rejected->guide_id, 'verification_status' => 'rejected', 'verification_notes' => 'License evidence is incomplete.']);
    }

    public function test_non_pending_guide_cannot_be_reviewed_and_guide_cannot_self_verify(): void
    {
        $bureau = $this->bureau();
        $verified = $this->guide('verified');
        $this->actingAs($bureau)->patch(route('bureau.guides.decide', $verified), ['decision' => 'reject', 'verification_notes' => 'No'])->assertRedirect();

        $guideUser = $verified->user;
        $this->actingAs($guideUser)->put(route('tour-guide.profile.update'), ['expertise' => 'History', 'availability_status' => 'available', 'verification_status' => 'pending'])->assertRedirect();
        $this->assertDatabaseHas('tour_guides', ['guide_id' => $verified->guide_id, 'verification_status' => 'verified']);
    }

    public function test_bureau_can_review_provider_and_rejection_is_scoped_to_pending(): void
    {
        $bureau = $this->bureau();
        $provider = $this->provider('pending');
        $approved = $this->provider('approved');

        $this->actingAs($bureau)->get(route('bureau.providers.index'))->assertOk()->assertSee($provider->business_name);
        $this->actingAs($bureau)->patch(route('bureau.providers.decide', $provider), ['decision' => 'reject'])->assertSessionHasErrors('verification_notes');
        $this->actingAs($bureau)->patch(route('bureau.providers.decide', $provider), ['decision' => 'reject', 'verification_notes' => 'Registration information requires correction.'])->assertRedirect();
        $this->assertDatabaseHas('service_providers', ['provider_id' => $provider->provider_id, 'status' => 'rejected']);
        $this->actingAs($bureau)->patch(route('bureau.providers.decide', $approved), ['decision' => 'reject', 'verification_notes' => 'No'])->assertStatus(422);
    }

    public function test_public_guide_discovery_reflects_bureau_verification_decision(): void
    {
        $pending = $this->guide('pending');
        $this->get(route('tour-guides.index'))->assertDontSee($pending->license_number);
        $pending->forceFill(['verification_status' => 'verified'])->save();
        $this->get(route('tour-guides.index'))->assertSee($pending->license_number);
    }

    private function bureau(): User
    {
        $user = User::factory()->create(['role' => 'tourism_bureau_officer']);
        TourismBureauOfficer::create(['user_id' => $user->user_id]);

        return $user;
    }

    private function guide(string $status): TourGuide
    {
        $user = User::factory()->create(['role' => 'tour_guide']);

        $guide = TourGuide::create([
            'user_id' => $user->user_id,
            'license_number' => fake()->unique()->numerify('LIC-####'),
            'expertise' => 'History',
            'availability_status' => 'available',
        ]);
        $guide->forceFill(['verification_status' => $status])->save();

        return $guide->fresh();
    }

    private function provider(string $status): ServiceProvider
    {
        $user = User::factory()->create(['role' => 'service_provider']);

        return ServiceProvider::create([
            'user_id' => $user->user_id,
            'business_name' => fake()->unique()->company(),
            'provider_type' => 'hotel',
            'status' => $status,
        ]);
    }
}
