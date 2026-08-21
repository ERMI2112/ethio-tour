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
use App\Models\TourPackage;
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
            ['full_name' => 'Abebe Bekele', 'nationality' => 'Ethiopian'],
        );

        $guideUser = $this->user('guide@test.com', 'tour_guide');
        $guide = $this->guide($guideUser, 'TG-GDR-001', 'Historical & cultural heritage tours across Northern Ethiopia', 'verified', 'Yared Tadesse');

        $pendingGuideUser = $this->user('uat-guide-pending@test.com', 'tour_guide');
        $this->guide($pendingGuideUser, 'TG-PENDING-002', 'Regional cultural guide (pending verification)', 'pending');

        $hotelUser = $this->user('hotel@test.com', 'service_provider');
        $hotel = $this->provider($hotelUser, 'Goha Hotel Gondar', 'hotel', 'verified', 'approved');

        $restaurantUser = $this->user('restaurant@test.com', 'service_provider');
        $restaurant = $this->provider($restaurantUser, 'Four Sisters Restaurant', 'restaurant', 'verified', 'approved');

        $transportUser = $this->user('transport@test.com', 'service_provider');
        $transport = $this->provider($transportUser, 'Simien Highlands 4x4 Transport', 'transportation_car_rental', 'verified', 'approved');

        $eventUser = $this->user('event@test.com', 'service_provider');
        $eventOrganizer = $this->provider($eventUser, 'Gondar Cultural Events Association', 'event_organizer', 'verified', 'approved');

        // These accounts keep the Bureau and Administrator queues useful in a development environment.
        $this->provider(
            $this->user('uat-provider-pending@test.com', 'service_provider'),
            'Ras Dashen Lodge (Pending)',
            'hotel',
            'pending',
            'pending',
        );
        $this->provider(
            $this->user('uat-provider-verified@test.com', 'service_provider'),
            'Habesha Cultural Dining',
            'restaurant',
            'verified',
            'pending',
        );
        $this->provider(
            $this->user('uat-provider-suspended@test.com', 'service_provider'),
            'Abyssinia Car Rentals (Suspended)',
            'transportation_car_rental',
            'verified',
            'suspended',
        );

        $gondar = $this->destination($bureau, 'Gondar', 'Gondar, Amhara', 'Gondar served as the capital of the Ethiopian Empire from 1636 to 1855, earning it the nickname "The Camelot of Africa" for its medieval castles and royal compounds.');
        $guide->primary_destination_id = $gondar->destination_id;
        $guide->save();
        $this->destination($bureau, 'Bahir Dar', 'Bahir Dar, Amhara', 'Gateway to Lake Tana\'s ancient island monasteries and the spectacular Blue Nile Falls.');
        $lalibela = $this->destination($bureau, 'Lalibela', 'Lalibela, Amhara', 'Home to eleven medieval monolithic rock-hewn churches, a UNESCO World Heritage Site.');

        $accommodation = $this->category('Accommodation');
        $dining = $this->category('Dining & Reservations');
        $transportation = $this->category('Transportation');
        $culturalEvents = $this->category('Cultural Events');
        $heritage = $this->category('Heritage & Culture');

        $standard = $this->service($hotel, $accommodation, $gondar, 'Standard Heritage View Room', 1500, 'A comfortable standard room with panoramic mountain views and modern amenities.');
        $deluxe = $this->service($hotel, $accommodation, $gondar, 'Deluxe Imperial Suite', 2500, 'A spacious deluxe suite featuring traditional Gondarine decor and private balcony overlooking the city.');
        $standardType = HotelRoomType::updateOrCreate(
            ['service_id' => $standard->service_id],
            ['capacity' => 2, 'amenities' => ['Wi-Fi', 'TV', 'Private Bathroom']],
        );
        $deluxeType = HotelRoomType::updateOrCreate(
            ['service_id' => $deluxe->service_id],
            ['capacity' => 4, 'amenities' => ['Wi-Fi', 'TV', 'Private Bathroom', 'Breakfast']],
        );
        foreach (['RM-101', 'RM-102'] as $roomNumber) {
            HotelRoom::updateOrCreate(['room_type_id' => $standardType->room_type_id, 'room_number' => $roomNumber], ['status' => 'active']);
        }
        foreach (['RM-201', 'RM-202'] as $roomNumber) {
            HotelRoom::updateOrCreate(['room_type_id' => $deluxeType->room_type_id, 'room_number' => $roomNumber], ['status' => 'active']);
        }

        $this->service($restaurant, $dining, $gondar, 'Traditional Feast & Coffee Ceremony', 350, 'Authentic Ethiopian culinary feast featuring injera, doro wat, and traditional coffee roasting ceremony.');
        $this->service($restaurant, $dining, $gondar, 'Specialty Coffee & Breakfast Tasting', 150, 'Single-origin Ethiopian highland coffee tasting paired with fresh local breakfast.');
        foreach ([['T-01', 2], ['T-02', 4], ['T-03', 6]] as [$number, $capacity]) {
            RestaurantTable::updateOrCreate(
                ['provider_id' => $restaurant->provider_id, 'table_number' => $number],
                ['capacity' => $capacity, 'status' => 'active'],
            );
        }

        $transportService = $this->service($transport, $transportation, $gondar, 'Simien 4x4 Safari Car Rental', 1800, 'Heavy-duty 4WD vehicle with professional driver tailored for Simien Mountains and historical circuit.');
        foreach ([['ETH-4WD-01', 'SUV', 'Toyota', 'RAV4', 4, 'active'], ['ETH-MINI-02', 'Minibus', 'Toyota', 'Hiace', 12, 'active'], ['ETH-SEDAN-03', 'Sedan', 'Toyota', 'Corolla', 4, 'inactive']] as [$identifier, $type, $make, $model, $capacity, $status]) {
            TransportationVehicle::updateOrCreate(
                ['provider_id' => $transport->provider_id, 'vehicle_identifier' => $identifier],
                ['service_id' => $transportService->service_id, 'vehicle_type' => $type, 'make' => $make, 'model' => $model, 'year' => 2024, 'capacity' => $capacity, 'status' => $status],
            );
        }

        $festivalService = $this->service($eventOrganizer, $culturalEvents, $gondar, 'Timkat Gondar Epiphany & Cultural Festival', 0, 'Annual baptism and epiphany festival celebration with sacred tabot procession at Fasilides Bath.');
        $heritageService = $this->service($eventOrganizer, $heritage, $lalibela, 'Lalibela Meskel Cultural Celebration', 0, 'Ancient festival of the Finding of the True Cross celebrated with torchlight processions.');
        $festival = $this->event($eventOrganizer, $festivalService, 'Timkat Gondar Epiphany & Cultural Festival', $gondar, 45);
        $heritageEvent = $this->event($eventOrganizer, $heritageService, 'Lalibela Meskel Cultural Celebration', $lalibela, 60);
        $this->ticket($festival, 'General Admission', 250, 100);
        $this->ticket($festival, 'VIP Admission', 600, 25);
        $this->ticket($heritageEvent, 'Standard Admission', 200, 80);
        $this->ticket($heritageEvent, 'Family Admission', 450, 30);

        MuseumInformation::firstOrCreate(
            ['museum_name' => 'Gondar Imperial Heritage Museum'],
            ['officer_id' => $bureau->officer_id, 'description' => 'Museum showcasing royal Gondarine artifacts, ecclesiastical manuscripts, and imperial regalia.', 'location' => 'Gondar, Amhara', 'opening_hours' => '09:00-17:00', 'entrance_fee' => 100, 'contact_information' => 'heritage-museum@gondartourism.gov.et'],
        );

        TourPackage::updateOrCreate(
            ['slug' => '3-day-simien-mountains-trekking-expedition'],
            [
                'guide_id' => $guide->guide_id,
                'destination_id' => $gondar->destination_id,
                'title' => '3-Day Simien Mountains Trekking Expedition',
                'duration_days' => 3,
                'price' => 7500.00,
                'max_group_size' => 8,
                'difficulty_level' => 'challenging',
                'description' => 'A breathtaking expedition along the dramatic highland escarpments of Simien Mountains National Park. Encounter wild Gelada baboons, traverse ancient afro-alpine plateaus, and witness the 500-meter drop of Jinbar Waterfall.',
                'itinerary' => [
                    ['day' => 1, 'title' => 'Debark Gateway & Sankaber Camp Trek', 'description' => 'Depart from Gondar to Debark park headquarters for permits, followed by a scenic 4-hour ridge walk to Sankaber Camp (3,250m) observing endemic wildlife.'],
                    ['day' => 2, 'title' => 'Jinbar Waterfall & Geech Plateau', 'description' => 'Trek along precipitous cliff faces overlooking the Geech Abyss and the dramatic 500m plunge of Jinbar Waterfall with scenic sunset over Imet Gogo.'],
                    ['day' => 3, 'title' => 'Chennek Viewpoint & Return to Gondar', 'description' => 'Early morning excursion towards Chennek to spot Walia ibex on rocky crags before descending back through Debark to Gondar city.'],
                ],
                'included' => [
                    'Licensed English-speaking Tour Guide',
                    'Park Entry Permits & Armed Scout Coordination',
                    '4x4 Expedition Transport from Gondar',
                    'Camping Equipment & 3 Daily Cooked Meals',
                ],
                'excluded' => [
                    'Personal Travel & Medical Insurance',
                    'Alcoholic Beverages',
                    'Gratuities & Tips for Local Scouts and Drivers',
                ],
                'cover_image' => 'images/destinations/gondar-castles.jpg',
                'is_active' => true,
            ]
        );

        TourPackage::updateOrCreate(
            ['slug' => '2-day-gondar-castles-monasteries-circuit'],
            [
                'guide_id' => $guide->guide_id,
                'destination_id' => $gondar->destination_id,
                'title' => '2-Day Gondar Imperial Castles & Monasteries Circuit',
                'duration_days' => 2,
                'price' => 3600.00,
                'max_group_size' => 12,
                'difficulty_level' => 'easy',
                'description' => 'Immerse yourself in the 17th-century Camelot of Africa. Explore the royal palaces of Emperor Fasilides, the winged angel ceiling of Debre Berhan Selassie, and Empress Mentewab\'s hilltop residence.',
                'itinerary' => [
                    ['day' => 1, 'title' => 'Fasil Ghebbi & Royal Archive Exploration', 'description' => 'Comprehensive guided tour of the UNESCO World Heritage royal enclosure, Emperor Iyasu palace, and imperial library.'],
                    ['day' => 2, 'title' => 'Debre Berhan Selassie Murals & Fasilides Bath', 'description' => 'Morning visit to the iconic angel-painted church ceiling followed by an afternoon historical walk around Fasilides Bath and Kuskuam Complex.'],
                ],
                'included' => [
                    'Accredited Cultural Historian Tour Guide',
                    'All Heritage Site Admission Passports',
                    'Traditional Ethiopian Coffee Ceremony with Incense',
                ],
                'excluded' => [
                    'Hotel Accommodation',
                    'Personal Souvenir Purchases',
                ],
                'cover_image' => 'images/destinations/gondar-castles.jpg',
                'is_active' => true,
            ]
        );

        $this->call(GondarPilotSeeder::class);
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

    private function guide(User $user, string $license, string $expertise, string $verification, ?string $fullName = null): TourGuide
    {
        $guide = TourGuide::firstOrNew(['user_id' => $user->user_id]);
        $guide->full_name = $fullName ?: 'Certified Tour Guide';
        $guide->license_number = $license;
        $guide->expertise = $expertise;
        $guide->profile_image = 'images/guides/tour-guide.jpg';
        $guide->bio = 'Certified professional cultural and trekking guide with over 8 years of experience leading historical expeditions across Gondar, the Simien Mountains National Park, and the ancient church circuits of Northern Ethiopia. Passionate about authentic cultural storytelling and traveler safety.';
        $guide->phone_number = '+251 91 184 2901';
        $guide->languages = ['Amharic', 'English', 'French'];
        $guide->years_of_experience = 8;
        $guide->specialties = ['UNESCO Heritage', 'Simien Trekking', 'Ecclesiastical History', 'Coffee Ceremony'];
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
