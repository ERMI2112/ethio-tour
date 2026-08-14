<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Category;
use App\Models\Destination;
use App\Models\HotelRoom;
use App\Models\HotelRoomReservation;
use App\Models\ServiceProvider;
use App\Models\TourismBureauOfficer;
use App\Models\TourismService;
use App\Models\Tourist;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class HotelProviderManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_hotel_provider_can_view_and_edit_own_profile(): void
    {
        $context = $this->hotelContext();

        $this->actingAs($context['user'])->get(route('hotel.profile'))
            ->assertOk()
            ->assertSee($context['provider']->business_name);

        $this->actingAs($context['user'])->put(route('hotel.profile.update'), ['business_name' => 'Updated Hotel'])
            ->assertRedirect(route('hotel.profile'));

        $this->assertSame('Updated Hotel', $context['provider']->fresh()->business_name);
    }

    public function test_hotel_provider_can_create_service_and_room_type_with_own_provider_id(): void
    {
        $context = $this->hotelContext();

        $response = $this->actingAs($context['user'])->post(route('hotel.services.store'), [
            'provider_id' => 999999,
            'service_name' => 'Standard Room',
            'price' => '1250.00',
            'description' => 'Comfortable room',
            'category_id' => $context['category']->category_id,
            'destination_id' => $context['destination']->destination_id,
            'capacity' => 2,
            'amenities' => "Wi-Fi\nTV",
        ]);

        $response->assertRedirect(route('hotel.services.index'));
        $service = TourismService::where('service_name', 'Standard Room')->firstOrFail();
        $this->assertSame($context['provider']->provider_id, $service->provider_id);
        $this->assertSame(2, $service->hotelRoomType->capacity);
        $this->assertSame(['Wi-Fi', 'TV'], $service->hotelRoomType->amenities);
    }

    public function test_hotel_provider_can_edit_own_service_and_room_type(): void
    {
        $context = $this->hotelContext();
        $service = $this->createService($context, 'Standard Room');
        $service->hotelRoomType()->create(['capacity' => 2, 'amenities' => ['Wi-Fi']]);

        $this->actingAs($context['user'])->put(route('hotel.services.update', $service), [
            'service_name' => 'Deluxe Room',
            'price' => '1800',
            'description' => 'Updated room',
            'category_id' => $context['category']->category_id,
            'destination_id' => $context['destination']->destination_id,
            'capacity' => 3,
            'amenities' => "Wi-Fi\nBalcony",
        ])->assertRedirect(route('hotel.services.index'));

        $this->assertSame('Deluxe Room', $service->fresh()->service_name);
        $this->assertSame(3, $service->fresh()->hotelRoomType->capacity);
        $this->assertSame(['Wi-Fi', 'Balcony'], $service->fresh()->hotelRoomType->amenities);
    }

    public function test_hotel_provider_can_create_and_edit_own_physical_rooms(): void
    {
        $context = $this->hotelContext();
        $service = $this->createService($context, 'Standard Room');
        $roomType = $service->hotelRoomType()->create(['capacity' => 2, 'amenities' => []]);

        $this->actingAs($context['user'])->post(route('hotel.rooms.store'), [
            'room_type_id' => $roomType->room_type_id,
            'room_number' => '101',
            'status' => 'active',
        ])->assertRedirect(route('hotel.rooms.index'));

        $room = HotelRoom::firstOrFail();
        $this->assertTrue($room->hotelRoomType->is($roomType));

        $this->actingAs($context['user'])->put(route('hotel.rooms.update', $room), [
            'room_type_id' => $roomType->room_type_id,
            'room_number' => '101A',
            'status' => 'inactive',
        ])->assertRedirect(route('hotel.rooms.index'));

        $this->assertSame('101A', $room->fresh()->room_number);
        $this->assertSame('inactive', $room->fresh()->status);
    }

    public function test_inactive_hotel_provider_cannot_use_management_routes(): void
    {
        $context = $this->hotelContext();
        $context['user']->update(['is_active' => false]);

        $this->actingAs($context['user'])->get(route('hotel.profile'))->assertRedirect(route('login'));
    }

    public function test_tourist_cannot_access_hotel_management(): void
    {
        $tourist = User::factory()->create(['role' => 'tourist']);

        $this->actingAs($tourist)->get(route('hotel.profile'))->assertForbidden();
    }

    #[DataProvider('nonHotelProviderTypes')]
    public function test_non_hotel_provider_types_cannot_access_hotel_management(string $providerType): void
    {
        $user = User::factory()->create(['role' => 'service_provider']);
        ServiceProvider::create([
            'user_id' => $user->user_id,
            'business_name' => ucfirst($providerType).' Provider',
            'provider_type' => $providerType,
            'status' => 'approved',
        ]);

        $this->actingAs($user)->get(route('hotel.profile'))->assertForbidden();
    }

    public static function nonHotelProviderTypes(): array
    {
        return [['restaurant'], ['transportation_car_rental'], ['event_organizer']];
    }

    public function test_one_hotel_provider_cannot_access_another_providers_service_or_room(): void
    {
        $first = $this->hotelContext('first@example.com');
        $second = $this->hotelContext('second@example.com');
        $service = $this->createService($second, 'Second Hotel Room');
        $roomType = $service->hotelRoomType()->create(['capacity' => 2, 'amenities' => []]);
        $room = $roomType->hotelRooms()->create(['room_number' => '201', 'status' => 'active']);

        $this->actingAs($first['user'])->get(route('hotel.services.edit', $service))->assertForbidden();
        $this->actingAs($first['user'])->put(route('hotel.services.update', $service), [
            'service_name' => 'Hijacked', 'price' => 1, 'description' => 'No',
            'category_id' => $first['category']->category_id, 'destination_id' => $first['destination']->destination_id,
            'capacity' => 1, 'amenities' => [],
        ])->assertForbidden();
        $this->actingAs($first['user'])->get(route('hotel.rooms.edit', $room))->assertForbidden();
        $this->actingAs($first['user'])->put(route('hotel.rooms.update', $room), [
            'room_type_id' => $roomType->room_type_id, 'room_number' => '999', 'status' => 'inactive',
        ])->assertForbidden();
    }

    public function test_invalid_category_destination_capacity_and_status_are_rejected(): void
    {
        $context = $this->hotelContext();

        $this->actingAs($context['user'])->post(route('hotel.services.store'), [
            'service_name' => 'Invalid', 'price' => 100, 'description' => 'Invalid',
            'category_id' => 999999, 'destination_id' => 999999, 'capacity' => 0, 'amenities' => [],
        ])->assertSessionHasErrors(['category_id', 'destination_id', 'capacity']);

        $service = $this->createService($context, 'Standard Room');
        $roomType = $service->hotelRoomType()->create(['capacity' => 2, 'amenities' => []]);

        $this->actingAs($context['user'])->post(route('hotel.rooms.store'), [
            'room_type_id' => $roomType->room_type_id, 'room_number' => '101', 'status' => 'broken',
        ])->assertSessionHasErrors('status');
    }

    public function test_room_numbers_are_unique_across_the_owning_hotel(): void
    {
        $context = $this->hotelContext();
        $firstType = $this->createService($context, 'Standard Room')->hotelRoomType()->create(['capacity' => 2, 'amenities' => []]);
        $secondType = $this->createService($context, 'Deluxe Room')->hotelRoomType()->create(['capacity' => 3, 'amenities' => []]);
        $firstType->hotelRooms()->create(['room_number' => '101', 'status' => 'active']);

        $this->actingAs($context['user'])->post(route('hotel.rooms.store'), [
            'room_type_id' => $secondType->room_type_id, 'room_number' => '101', 'status' => 'active',
        ])->assertSessionHasErrors('room_number');
    }

    public function test_provider_cannot_delete_room_with_historical_reservation(): void
    {
        $context = $this->hotelContext();
        $roomType = $this->createService($context, 'Standard Room')->hotelRoomType()->create(['capacity' => 2, 'amenities' => []]);
        $room = $roomType->hotelRooms()->create(['room_number' => '101', 'status' => 'active']);
        $tourist = Tourist::create(['user_id' => User::factory()->create(['role' => 'tourist'])->user_id, 'full_name' => 'Guest', 'nationality' => 'Ethiopian']);
        $booking = Booking::create(['tourist_id' => $tourist->tourist_id, 'service_id' => $roomType->service_id]);
        HotelRoomReservation::create(['booking_id' => $booking->booking_id, 'room_id' => $room->room_id, 'check_in_date' => '2026-09-01', 'check_out_date' => '2026-09-03', 'guest_count' => 1]);

        $this->actingAs($context['user'])->delete(route('hotel.rooms.destroy', $room))
            ->assertSessionHas('error');
        $this->assertDatabaseHas('hotel_rooms', ['room_id' => $room->room_id]);
    }

    public function test_provider_cannot_remove_service_with_inventory(): void
    {
        $context = $this->hotelContext();
        $service = $this->createService($context, 'Standard Room');
        $roomType = $service->hotelRoomType()->create(['capacity' => 2, 'amenities' => []]);
        $roomType->hotelRooms()->create(['room_number' => '101', 'status' => 'active']);

        $this->actingAs($context['user'])->delete(route('hotel.services.destroy', $service))->assertSessionHas('error');
        $this->assertDatabaseHas('tourism_services', ['service_id' => $service->service_id]);
    }

    public function test_hotel_navigation_is_only_shown_to_hotel_providers(): void
    {
        $hotel = $this->hotelContext();
        $this->actingAs($hotel['user'])->get('/')
            ->assertSee('Hotel Dashboard')
            ->assertSee(route('hotel.dashboard'))
            ->assertSee(route('hotel.reservations.index'));

        $restaurant = User::factory()->create(['role' => 'service_provider']);
        ServiceProvider::create(['user_id' => $restaurant->user_id, 'business_name' => 'Restaurant', 'provider_type' => 'restaurant', 'status' => 'approved']);
        $this->actingAs($restaurant)->get('/')
            ->assertDontSee('Hotel Dashboard')
            ->assertSee('Service Provider Portal');
    }

    private function hotelContext(string $email = 'hotel@example.com'): array
    {
        $user = User::factory()->create(['email' => $email, 'role' => 'service_provider']);
        $provider = ServiceProvider::create(['user_id' => $user->user_id, 'business_name' => 'Test Hotel', 'provider_type' => 'hotel', 'status' => 'approved']);
        $officer = TourismBureauOfficer::create(['user_id' => User::factory()->create(['role' => 'tourism_bureau_officer'])->user_id]);
        $category = Category::create(['category_name' => 'Room '.str_replace(['@', '.'], '-', $email)]);
        $destination = Destination::create(['officer_id' => $officer->officer_id, 'name' => 'Gondar', 'location' => 'Gondar', 'description' => 'Test destination']);

        return compact('user', 'provider', 'category', 'destination');
    }

    private function createService(array $context, string $name): TourismService
    {
        return TourismService::create([
            'provider_id' => $context['provider']->provider_id,
            'category_id' => $context['category']->category_id,
            'destination_id' => $context['destination']->destination_id,
            'service_name' => $name,
            'price' => 1000,
            'description' => 'Room service',
        ]);
    }
}
