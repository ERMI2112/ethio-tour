<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Destination;
use App\Models\ServiceProvider;
use App\Models\TourismBureauOfficer;
use App\Models\TourismService;
use App\Models\Tourist;
use App\Models\Trip;
use App\Models\User;
use App\Services\SmartTripAIService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SmartTripAITest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_the_private_ai_planner(): void
    {
        $trip = $this->trip($this->tourist());

        $this->get(route('smart-trip.ai.create', $trip))->assertRedirect(route('login'));
    }

    public function test_missing_openai_configuration_falls_back_to_deterministic_planning(): void
    {
        Config::set('services.openai.key', null);
        [$user, $trip] = $this->tripWithPublicService();

        $response = $this->actingAs($user)->post(route('smart-trip.ai.generate', $trip), [
            'intent' => 'I want a relaxed history-focused trip.',
        ]);

        $response->assertOk()->assertSee('Deterministic fallback')->assertSee('not configured', false);
        Http::assertNothingSent();
    }

    public function test_mocked_responses_output_is_structured_and_only_public_entities_survive(): void
    {
        Config::set('services.openai.key', 'test-key');
        Config::set('services.openai.base_url', 'https://openai.test/v1');
        [$user, $trip, $service] = $this->tripWithPublicService();
        Http::fake(['https://openai.test/*' => Http::response([
            'id' => 'resp_test',
            'output_text' => json_encode([
                'trip_summary' => 'A history-focused plan.',
                'days' => [[
                    'date' => $trip->start_date->toDateString(),
                    'items' => [
                        ['entity_type' => 'service', 'entity_id' => $service->service_id, 'reason' => 'A verified public service.', 'estimated_duration_minutes' => 90],
                        ['entity_type' => 'service', 'entity_id' => 999999, 'reason' => 'Invented place.', 'estimated_duration_minutes' => 60],
                    ],
                ]],
                'notes' => ['Check the existing booking flow before making a reservation.'],
                'warnings' => [],
            ], JSON_THROW_ON_ERROR),
        ])]);

        $response = $this->actingAs($user)->post(route('smart-trip.ai.generate', $trip), [
            'intent' => 'Focus on history and a calm pace.',
        ]);

        $response->assertOk()->assertSee('AI-assisted plan')->assertSee($service->service_name)->assertSee('removed because they were not valid', false);
        $response->assertDontSee('Invented place');
        $this->assertDatabaseCount('bookings', 0);
        Http::assertSent(fn ($request): bool => $request->url() === 'https://openai.test/v1/responses'
            && $request->hasHeader('Authorization', 'Bearer test-key')
            && str_contains($request->body(), 'smart_trip_itinerary')
            && ! str_contains($request->body(), 'password'));
    }

    public function test_function_call_tools_use_public_search_and_continue_to_structured_output(): void
    {
        Config::set('services.openai.key', 'test-key');
        Config::set('services.openai.base_url', 'https://openai.test/v1');
        [$user, $trip, $service] = $this->tripWithPublicService();
        Http::fakeSequence()
            ->push(['id' => 'resp_tools', 'output' => [[
                'type' => 'function_call',
                'name' => 'search_tourism',
                'call_id' => 'call_1',
                'arguments' => json_encode(['q' => $service->service_name, 'type' => 'hotels', 'destination' => null, 'date' => null], JSON_THROW_ON_ERROR),
            ]]], 200)
            ->push(['id' => 'resp_final', 'output_text' => json_encode([
                'trip_summary' => 'A verified service plan.',
                'days' => [['date' => $trip->start_date->toDateString(), 'items' => [['entity_type' => 'service', 'entity_id' => $service->service_id, 'reason' => 'Returned by public search.', 'estimated_duration_minutes' => null]]]],
                'notes' => [],
                'warnings' => [],
            ], JSON_THROW_ON_ERROR)], 200);

        $result = app(SmartTripAIService::class)->plan($trip, 'Find a hotel.');

        $this->assertFalse($result['fallback']);
        $this->assertSame('openai', $result['source']);
        $this->assertSame($service->service_name, $result['days'][0]['items'][0]['title']);
        Http::assertSentCount(2);
    }

    public function test_other_tourists_cannot_use_the_ai_planner_for_a_private_trip(): void
    {
        $owner = $this->tourist();
        $other = $this->tourist();
        $trip = $this->trip($owner);

        $this->actingAs($other)->post(route('smart-trip.ai.generate', $trip), ['intent' => 'Show my plan.'])->assertForbidden();
    }

    public function test_ai_tool_outputs_never_expose_private_trip_or_booking_data(): void
    {
        $user = $this->tourist();
        $trip = $this->trip($user);
        $this->actingAs($user);
        $result = app(SmartTripAIService::class)->executeTool($trip, 'get_trip_details', ['trip_id' => $trip->trip_id]);

        $this->assertArrayHasKey('trip', $result);
        $this->assertArrayNotHasKey('password', $result);
        $this->assertArrayNotHasKey('booking_status', $result);
    }

    public function test_ai_cannot_change_booking_state_or_create_a_booking(): void
    {
        Config::set('services.openai.key', null);
        [$user, $trip] = $this->tripWithPublicService();

        $this->actingAs($user)->post(route('smart-trip.ai.generate', $trip), ['intent' => 'Plan my stay.'])->assertOk();

        $this->assertDatabaseCount('bookings', 0);
        $this->assertDatabaseCount('payments', 0);
    }

    /** @return array{0:User,1:Trip,2:TourismService} */
    private function tripWithPublicService(): array
    {
        $user = $this->tourist();
        [$destination, $category] = $this->catalog();
        $providerUser = User::factory()->create(['role' => 'service_provider']);
        $provider = ServiceProvider::create(['user_id' => $providerUser->user_id, 'business_name' => 'Verified Hotel', 'provider_type' => 'hotel', 'status' => 'approved']);
        $service = TourismService::create(['provider_id' => $provider->provider_id, 'category_id' => $category->category_id, 'destination_id' => $destination->destination_id, 'service_name' => 'Verified Hotel Room', 'price' => 100, 'description' => 'A real public hotel service']);
        $trip = $this->trip($user, $destination);

        return [$user, $trip, $service];
    }

    private function tourist(): User
    {
        $user = User::factory()->create(['role' => 'tourist']);
        Tourist::create(['user_id' => $user->user_id, 'full_name' => 'AI Tourist', 'nationality' => 'Ethiopian']);

        return $user;
    }

    private function trip(User $user, ?Destination $destination = null): Trip
    {
        $destination ??= $this->catalog()[0];
        $trip = Trip::create(['user_id' => $user->user_id, 'title' => 'AI Test Trip', 'start_date' => today()->addDays(5), 'end_date' => today()->addDays(6), 'preferences' => ['history']]);
        $trip->destinations()->attach($destination->destination_id);

        return $trip;
    }

    /** @return array{0:Destination,1:Category} */
    private function catalog(): array
    {
        $officerUser = User::factory()->create(['role' => 'tourism_bureau_officer']);
        $officer = TourismBureauOfficer::create(['user_id' => $officerUser->user_id]);
        $destination = Destination::create(['officer_id' => $officer->officer_id, 'name' => 'Gondar', 'location' => 'Amhara', 'description' => 'Historic city', 'latitude' => 12.6, 'longitude' => 37.46]);
        $category = Category::create(['category_name' => 'Accommodation']);

        return [$destination, $category];
    }
}
