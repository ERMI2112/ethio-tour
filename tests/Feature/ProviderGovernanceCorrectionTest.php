<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Destination;
use App\Models\ServiceProvider;
use App\Models\TourismBureauOfficer;
use App\Models\TourismService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ProviderGovernanceCorrectionTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('providerStates')]
    public function test_public_services_require_both_provider_gates(bool $active, string $verification, string $status, bool $visible): void
    {
        [$service] = $this->service($active, $verification, $status);
        $response = $this->get(route('tourism-services.show', $service));

        $visible ? $response->assertOk()->assertSee($service->service_name) : $response->assertNotFound();
        $listing = $this->get(route('tourism-services.index'));
        $visible ? $listing->assertSee($service->service_name) : $listing->assertDontSee($service->service_name);
    }

    public static function providerStates(): array
    {
        return [
            'pending' => [true, 'pending', 'pending', false],
            'verified but pending activation' => [true, 'verified', 'pending', false],
            'verified and approved' => [true, 'verified', 'approved', true],
            'rejected' => [true, 'rejected', 'pending', false],
            'administratively rejected' => [true, 'verified', 'rejected', false],
            'suspended' => [true, 'verified', 'suspended', false],
            'inactive user' => [false, 'verified', 'approved', false],
        ];
    }

    public function test_provider_can_view_onboarding_status_but_cannot_operate_before_activation(): void
    {
        $user = User::factory()->create(['role' => 'service_provider']);
        $provider = ServiceProvider::create(['user_id' => $user->user_id, 'business_name' => 'Pending Hotel', 'provider_type' => 'hotel', 'status' => 'pending']);

        $this->actingAs($user)->get(route('provider.status'))
            ->assertOk()
            ->assertSee('Pending Hotel')
            ->assertSee('Application Status')
            ->assertDontSee('Hotel Dashboard');
        $this->actingAs($user)->get(route('hotel.dashboard'))->assertForbidden();
        $this->assertSame('pending', $provider->fresh()->verification_status);
    }

    public function test_provider_cannot_submit_governance_fields_through_profile_form(): void
    {
        [$service, $provider, $user] = $this->service(true, 'pending', 'pending');

        $this->actingAs($user)->put(route('provider.profile.update'), [
            'business_name' => 'Updated Name',
            'verification_status' => 'verified',
            'status' => 'approved',
        ])->assertRedirect(route('provider.status'));

        $this->assertDatabaseHas('service_providers', ['provider_id' => $provider->provider_id, 'business_name' => 'Updated Name', 'verification_status' => 'pending', 'status' => 'pending']);
    }

    private function service(bool $active, string $verification, string $status): array
    {
        $user = User::factory()->create(['role' => 'service_provider', 'is_active' => $active]);
        $provider = ServiceProvider::create(['user_id' => $user->user_id, 'business_name' => uniqid('Provider '), 'provider_type' => 'hotel', 'status' => $status]);
        $provider->forceFill(['verification_status' => $verification])->save();
        $officer = TourismBureauOfficer::create(['user_id' => User::factory()->create(['role' => 'tourism_bureau_officer'])->user_id]);
        $destination = Destination::create(['officer_id' => $officer->officer_id, 'name' => uniqid('Destination '), 'location' => 'Gondar', 'description' => 'Public destination.']);
        $category = Category::create(['category_name' => uniqid('Category ')]);
        $service = TourismService::create(['provider_id' => $provider->provider_id, 'category_id' => $category->category_id, 'destination_id' => $destination->destination_id, 'service_name' => uniqid('Service '), 'price' => 100, 'description' => 'Public service.']);

        return [$service, $provider, $user];
    }
}
