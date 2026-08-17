<?php

namespace Database\Seeders;

use App\Models\Administrator;
use App\Models\Category;
use App\Models\CulturalEvent;
use App\Models\Destination;
use App\Models\EventTicketType;
use App\Models\HotelRoom;
use App\Models\HotelRoomType;
use App\Models\MuseumInformation;
use App\Models\RestaurantTable;
use App\Models\ServiceProvider;
use App\Models\TourGuide;
use App\Models\TourismBureauOfficer;
use App\Models\TourismService;
use App\Models\Tourist;
use App\Models\TransportationVehicle;
use App\Models\User;
use Illuminate\Database\Seeder;

class UatDemoSeeder extends Seeder
{
    public const PASSWORD = 'password';

    public function run(): void
    {
        $bureauUser = $this->user('bureau@test.com', 'tourism_bureau_officer');
        $bureau = TourismBureauOfficer::firstOrCreate(['user_id' => $bureauUser->user_id]);

        $adminUser = $this->user('admin@test.com', 'administrator');
        Administrator::firstOrCreate(['user_id' => $adminUser->user_id]);

        $touristUser = $this->user('tourist@test.com', 'tourist');
        Tourist::firstOrCreate(
            ['user_id' => $touristUser->user_id],
            ['full_name' => 'UAT Tourist', 'nationality' => 'Ethiopian'],
        );

        $guideUser = $this->user('guide@test.com', 'tour_guide');
        $this->guide($guideUser, 'UAT-GUIDE-001', 'History and cultural heritage tours', 'verified');

        $pendingGuideUser = $this->user('uat-guide-pending@test.com', 'tour_guide');
        $this->guide($pendingGuideUser, 'UAT-GUIDE-PENDING', 'Pending verification test guide', 'pending');

        $hotelUser = $this->user('hotel@test.com', 'service_provider');
        $hotel = $this->provider($hotelUser, 'UAT Gondar Hotel', 'hotel', 'verified', 'approved');

        $restaurantUser = $this->user('restaurant@test.com', 'service_provider');
        $restaurant = $this->provider($restaurantUser, 'UAT Gondar Restaurant', 'restaurant', 'verified', 'approved');

        $transportUser = $this->user('transport@test.com', 'service_provider');
        $transport = $this->provider($transportUser, 'UAT Gondar Transport', 'transportation_car_rental', 'verified', 'approved');

        $eventUser = $this->user('event@test.com', 'service_provider');
        $eventOrganizer = $this->provider($eventUser, 'UAT Gondar Events', 'event_organizer', 'verified', 'approved');

        // These accounts keep the Bureau and Administrator queues useful during manual UAT.
        $this->provider(
            $this->user('uat-provider-pending@test.com', 'service_provider'),
            'UAT Pending Provider',
            'hotel',
            'pending',
            'pending',
        );
        $this->provider(
            $this->user('uat-provider-verified@test.com', 'service_provider'),
            'UAT Bureau-Verified Provider',
            'restaurant',
            'verified',
            'pending',
        );
        $this->provider(
            $this->user('uat-provider-suspended@test.com', 'service_provider'),
            'UAT Suspended Provider',
            'transportation_car_rental',
            'verified',
            'suspended',
        );

        $gondar = $this->destination($bureau, 'Gondar', 'Gondar, Amhara', 'Primary UAT destination for manual tourism testing.');
        $this->destination($bureau, 'Bahir Dar', 'Bahir Dar, Amhara', 'UAT destination for discovery and filtering tests.');
        $lalibela = $this->destination($bureau, 'Lalibela', 'Lalibela, Amhara', 'UAT destination for discovery and filtering tests.');

        $accommodation = $this->category('Accommodation');
        $dining = $this->category('Dining & Reservations');
        $transportation = $this->category('Transportation');
        $culturalEvents = $this->category('Cultural Events');
        $heritage = $this->category('Heritage & Culture');

        $standard = $this->service($hotel, $accommodation, $gondar, 'UAT Standard Room', 1500, 'A comfortable standard room for manual hotel booking and availability tests.');
        $deluxe = $this->service($hotel, $accommodation, $gondar, 'UAT Deluxe Room', 2500, 'A larger deluxe room for manual hotel booking and availability tests.');
        $standardType = HotelRoomType::updateOrCreate(
            ['service_id' => $standard->service_id],
            ['capacity' => 2, 'amenities' => ['Wi-Fi', 'TV', 'Private Bathroom']],
        );
        $deluxeType = HotelRoomType::updateOrCreate(
            ['service_id' => $deluxe->service_id],
            ['capacity' => 4, 'amenities' => ['Wi-Fi', 'TV', 'Private Bathroom', 'Breakfast']],
        );
        foreach (['UAT-101', 'UAT-102'] as $roomNumber) {
            HotelRoom::updateOrCreate(['room_type_id' => $standardType->room_type_id, 'room_number' => $roomNumber], ['status' => 'active']);
        }
        foreach (['UAT-201', 'UAT-202'] as $roomNumber) {
            HotelRoom::updateOrCreate(['room_type_id' => $deluxeType->room_type_id, 'room_number' => $roomNumber], ['status' => 'active']);
        }

        $this->service($restaurant, $dining, $gondar, 'UAT Gondar Dining Reservation', 350, 'A dining reservation offering for restaurant availability and acceptance tests.');
        $this->service($restaurant, $dining, $gondar, 'UAT Gondar Coffee & Breakfast', 150, 'A coffee and breakfast offering for restaurant discovery tests.');
        foreach ([['UAT-T1', 2], ['UAT-T2', 4], ['UAT-T3', 6]] as [$number, $capacity]) {
            RestaurantTable::updateOrCreate(
                ['provider_id' => $restaurant->provider_id, 'table_number' => $number],
                ['capacity' => $capacity, 'status' => 'active'],
            );
        }

        $transportService = $this->service($transport, $transportation, $gondar, 'UAT Gondar Car Rental', 1800, 'A transportation service for vehicle availability and reservation tests.');
        foreach ([['UAT-CAR-01', 'SUV', 'Toyota', 'RAV4', 4, 'active'], ['UAT-CAR-02', 'Minibus', 'Toyota', 'Hiace', 12, 'active'], ['UAT-CAR-03', 'Sedan', 'Toyota', 'Corolla', 4, 'inactive']] as [$identifier, $type, $make, $model, $capacity, $status]) {
            TransportationVehicle::updateOrCreate(
                ['provider_id' => $transport->provider_id, 'vehicle_identifier' => $identifier],
                ['service_id' => $transportService->service_id, 'vehicle_type' => $type, 'make' => $make, 'model' => $model, 'year' => 2024, 'capacity' => $capacity, 'status' => $status],
            );
        }

        $festivalService = $this->service($eventOrganizer, $culturalEvents, $gondar, 'UAT Gondar Cultural Festival', 0, 'A public cultural event offering for ticket booking tests.');
        $heritageService = $this->service($eventOrganizer, $heritage, $lalibela, 'UAT Heritage Celebration', 0, 'A second cultural event offering for discovery and ticket tests.');
        $festival = $this->event($eventOrganizer, $festivalService, 'UAT Gondar Cultural Festival', $gondar, 45);
        $heritageEvent = $this->event($eventOrganizer, $heritageService, 'UAT Heritage Celebration', $lalibela, 60);
        $this->ticket($festival, 'General Admission', 250, 100);
        $this->ticket($festival, 'VIP Admission', 600, 25);
        $this->ticket($heritageEvent, 'Standard Admission', 200, 80);
        $this->ticket($heritageEvent, 'Family Admission', 450, 30);

        MuseumInformation::firstOrCreate(
            ['museum_name' => 'UAT Gondar Heritage Museum'],
            ['officer_id' => $bureau->officer_id, 'description' => 'Museum information for manual Bureau and public discovery testing.', 'location' => 'Gondar, Amhara', 'opening_hours' => '09:00-17:00', 'entrance_fee' => 100, 'contact_information' => 'uat-museum@example.com'],
        );
    }

    private function user(string $email, string $role): User
    {
        $user = User::firstOrNew(['email' => $email]);
        $user->role = $role;
        $user->is_active = true;
        $user->password = self::PASSWORD;
        $user->save();

        return $user;
    }

    private function guide(User $user, string $license, string $expertise, string $verification): TourGuide
    {
        $guide = TourGuide::firstOrNew(['user_id' => $user->user_id]);
        $guide->license_number = $license;
        $guide->expertise = $expertise;
        $guide->availability_status = 'available';
        $guide->daily_rate = 2000;
        $guide->verification_status = $verification;
        $guide->save();

        return $guide;
    }

    private function provider(User $user, string $businessName, string $type, string $verification, string $status): ServiceProvider
    {
        $provider = ServiceProvider::firstOrNew(['user_id' => $user->user_id]);
        $provider->business_name = $businessName;
        $provider->provider_type = $type;
        $provider->verification_status = $verification;
        $provider->status = $status;
        $provider->save();

        return $provider;
    }

    private function destination(TourismBureauOfficer $bureau, string $name, string $location, string $description): Destination
    {
        return Destination::firstOrCreate(
            ['name' => $name],
            ['officer_id' => $bureau->officer_id, 'location' => $location, 'description' => $description],
        );
    }

    private function category(string $name): Category
    {
        return Category::firstOrCreate(['category_name' => $name]);
    }

    private function service(ServiceProvider $provider, Category $category, Destination $destination, string $name, float $price, string $description): TourismService
    {
        return TourismService::updateOrCreate(
            ['provider_id' => $provider->provider_id, 'service_name' => $name],
            ['category_id' => $category->category_id, 'destination_id' => $destination->destination_id, 'price' => $price, 'description' => $description],
        );
    }

    private function event(ServiceProvider $provider, TourismService $service, string $name, Destination $destination, int $days): CulturalEvent
    {
        return CulturalEvent::updateOrCreate(
            ['provider_id' => $provider->provider_id, 'event_name' => $name],
            ['destination_id' => $destination->destination_id, 'service_id' => $service->service_id, 'description' => $service->description, 'event_date' => now()->addDays($days)->toDateString(), 'start_time' => '18:00', 'end_time' => '20:00', 'venue' => $destination->name.' Cultural Hall', 'status' => 'published'],
        );
    }

    private function ticket(CulturalEvent $event, string $name, float $price, int $quantity): EventTicketType
    {
        return EventTicketType::updateOrCreate(
            ['event_id' => $event->event_id, 'name' => $name],
            ['price' => $price, 'quantity' => $quantity, 'status' => 'active'],
        );
    }
}
