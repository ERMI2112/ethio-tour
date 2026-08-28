<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Category;
use App\Models\Destination;
use App\Models\ServiceProvider;
use App\Models\TourGuide;
use App\Models\TourismBureauOfficer;
use App\Models\TourismService;
use App\Models\Tourist;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingPaymentRateLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_availability_probes_are_throttled(): void
    {
        $service = $this->service();

        // 30 requests/minute are allowed; the 31st must be rejected.
        for ($i = 0; $i < 30; $i++) {
            $this->post(route('tourism-services.check-availability', $service), []);
        }

        $this->post(route('tourism-services.check-availability', $service), [])
            ->assertStatus(429);
    }

    public function test_payment_initialization_is_throttled(): void
    {
        $owner = $this->touristUser();
        $booking = $this->booking($owner);

        // 6 payment initializations/minute are allowed; the 7th must be
        // rejected before the payment gateway is ever contacted.
        for ($i = 0; $i < 6; $i++) {
            $this->actingAs($owner)->post(route('payments.initialize', $booking));
        }

        $this->actingAs($owner)->post(route('payments.initialize', $booking))
            ->assertStatus(429);
    }

    public function test_booking_creation_is_throttled(): void
    {
        $tourist = $this->touristUser();
        $service = $this->service();

        for ($i = 0; $i < 10; $i++) {
            $this->actingAs($tourist)->post(route('tourist.reservations.store', $service), []);
        }

        $this->actingAs($tourist)->post(route('tourist.reservations.store', $service), [])
            ->assertStatus(429);
    }

    private function touristUser(): User
    {
        $user = User::factory()->create(['role' => 'tourist']);
        Tourist::create(['user_id' => $user->user_id, 'full_name' => 'Rate Limit Tourist', 'nationality' => 'Ethiopian']);

        return $user;
    }

    private function service(): TourismService
    {
        $providerUser = User::factory()->create(['role' => 'service_provider']);
        $provider = ServiceProvider::create([
            'user_id' => $providerUser->user_id,
            'business_name' => 'Rate Limit Hotel',
            'provider_type' => 'hotel',
            'status' => 'approved',
        ]);
        $officerUser = User::factory()->create(['role' => 'tourism_bureau_officer']);
        $officer = TourismBureauOfficer::create(['user_id' => $officerUser->user_id]);
        $destination = Destination::create(['officer_id' => $officer->officer_id, 'name' => 'Gondar '.uniqid(), 'location' => 'Gondar', 'description' => 'Rate limit test destination']);
        $category = Category::create(['category_name' => 'Room']);

        return TourismService::create([
            'provider_id' => $provider->provider_id,
            'category_id' => $category->category_id,
            'destination_id' => $destination->destination_id,
            'service_name' => 'Rate Limit Room',
            'price' => 1000.00,
            'description' => 'Rate limit test offering',
        ]);
    }

    private function booking(User $user): Booking
    {
        $guideUser = User::factory()->create(['role' => 'tour_guide']);
        $guide = TourGuide::create(['user_id' => $guideUser->user_id, 'license_number' => 'RL-'.fake()->unique()->numerify('#####'), 'expertise' => 'History', 'availability_status' => 'available', 'verification_status' => 'verified', 'daily_rate' => 100]);

        return Booking::create(['tourist_id' => $user->tourist->tourist_id, 'guide_id' => $guide->guide_id, 'status' => 'accepted', 'booking_date' => now(), 'total_amount' => '100.00', 'currency' => 'ETB']);
    }
}
