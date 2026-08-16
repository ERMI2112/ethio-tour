<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\CulturalEvent;
use App\Models\Destination;
use App\Models\EventTicketType;
use App\Models\HotelRoom;
use App\Models\HotelRoomType;
use App\Models\RestaurantTable;
use App\Models\ServiceProvider;
use App\Models\TourGuide;
use App\Models\TourismService;
use App\Models\TransportationVehicle;
use App\Models\User;
use Database\Seeders\UatDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UatDemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_uat_seeder_creates_the_manual_testing_dependency_chains(): void
    {
        $this->seed(UatDemoSeeder::class);

        $this->assertGreaterThanOrEqual(3, Destination::count());
        $this->assertGreaterThanOrEqual(5, Category::count());
        $this->assertGreaterThanOrEqual(7, TourismService::count());
        $this->assertSame('approved', ServiceProvider::where('provider_type', 'hotel')->where('status', 'approved')->firstOrFail()->status);
        $this->assertSame(2, HotelRoomType::count());
        $this->assertSame(4, HotelRoom::count());
        $this->assertSame(3, RestaurantTable::count());
        $this->assertSame(3, TransportationVehicle::count());
        $this->assertSame(2, CulturalEvent::where('status', 'published')->count());
        $this->assertSame(4, EventTicketType::count());
        $this->assertSame('verified', TourGuide::whereHas('user', fn ($query) => $query->where('email', 'guide@test.com'))->value('verification_status'));
        $this->assertTrue(User::where('email', 'bureau@test.com')->where('role', 'tourism_bureau_officer')->exists());
        $this->assertTrue(User::where('email', 'admin@test.com')->where('role', 'administrator')->exists());
    }

    public function test_uat_seeder_is_idempotent(): void
    {
        $this->seed(UatDemoSeeder::class);
        $counts = [
            'users' => User::count(),
            'destinations' => Destination::count(),
            'categories' => Category::count(),
            'services' => TourismService::count(),
            'room_types' => HotelRoomType::count(),
            'rooms' => HotelRoom::count(),
            'tables' => RestaurantTable::count(),
            'vehicles' => TransportationVehicle::count(),
            'events' => CulturalEvent::count(),
            'tickets' => EventTicketType::count(),
        ];

        $this->seed(UatDemoSeeder::class);

        foreach ($counts as $table => $count) {
            $actual = match ($table) {
                'users' => User::count(),
                'destinations' => Destination::count(),
                'categories' => Category::count(),
                'services' => TourismService::count(),
                'room_types' => HotelRoomType::count(),
                'rooms' => HotelRoom::count(),
                'tables' => RestaurantTable::count(),
                'vehicles' => TransportationVehicle::count(),
                'events' => CulturalEvent::count(),
                'tickets' => EventTicketType::count(),
            };
            $this->assertSame($count, $actual, $table.' count changed after a second seed.');
        }
    }
}
