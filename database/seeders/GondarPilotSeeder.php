<?php

namespace Database\Seeders;

use App\Models\Attraction;
use App\Models\Destination;
use Illuminate\Database\Seeder;

class GondarPilotSeeder extends Seeder
{
    public function run(): void
    {
        $this->updateDestinations();
        $this->seedGondarAttractions();
    }

    private function updateDestinations(): void
    {
        // Update Gondar with rich, verifiable historical content
        Destination::where('name', 'Gondar')->update([
            'slug' => 'gondar',
            'description' => 'Gondar served as the imperial capital of Ethiopia from 1636 to 1855, earning the moniker "The Camelot of Africa" for its concentration of stone castles and fortified royal compounds. Nestled in the foothills of the Simien Mountains at 2,200 metres elevation, the city preserves the UNESCO World Heritage–listed Fasil Ghebbi royal enclosure and world-renowned religious monuments. Today Gondar is a premier cultural destination and the primary staging ground for treks into Simien Mountains National Park and the annual Timkat (Epiphany) festivities held at Fasilides\' Bath.',
            'tagline' => 'The Camelot of Africa — Castles & Royal Enclosures',
            'hero_image' => '/images/destinations/gondar-hero.jpg',
            'gallery_images' => [
                [
                    'path' => '/images/destinations/gondar-hero.jpg',
                    'alt' => 'Fasil Ghebbi royal enclosure in Gondar',
                    'attribution' => 'Photo: A. Savin, Wikimedia Commons (FAL).',
                    'source_url' => 'https://commons.wikimedia.org/wiki/File:ET_Gondar_asv2018-02_img03_Fasil_Ghebbi.jpg',
                    'is_primary' => true,
                ],
                [
                    'path' => '/images/attractions/fasil-ghebbi-courtyard.jpg',
                    'alt' => 'Stone walls and courtyard at Fasil Ghebbi in Gondar',
                    'attribution' => 'Photo: A. Savin, Wikimedia Commons (FAL).',
                    'source_url' => 'https://commons.wikimedia.org/wiki/File:ET_Gondar_asv2018-02_img18_Fasil_Ghebbi.jpg',
                    'is_primary' => false,
                ],
                [
                    'path' => '/images/attractions/debre-berhan-selassie-exterior.jpg',
                    'alt' => 'Exterior of Debre Berhan Selassie Church in Gondar',
                    'attribution' => 'Photo: Bernard Gagnon, Wikimedia Commons (CC BY-SA 3.0).',
                    'source_url' => 'https://commons.wikimedia.org/wiki/File:Church_of_Debra_Berhan_Selassie_01.jpg',
                    'is_primary' => false,
                ],
                [
                    'path' => '/images/attractions/fasilides-bath-pavilion.jpg',
                    'alt' => 'Historic pavilion at Fasilides Bath in Gondar',
                    'attribution' => 'Photo: Bernard Gagnon, Wikimedia Commons (CC BY-SA 3.0).',
                    'source_url' => 'https://commons.wikimedia.org/wiki/File:Fasilides_Bath_03.jpg',
                    'is_primary' => false,
                ],
            ],
            'latitude' => 12.6030000,
            'longitude' => 37.4520000,
        ]);

        // Update Bahir Dar with verified editorial content.
        Destination::where('name', 'Bahir Dar')->update([
            'slug' => 'bahir-dar',
            'description' => 'Bahir Dar sits on the scenic southern shore of Lake Tana, Ethiopia\'s largest body of water and the source of the Blue Nile. The city is the gateway to ancient island monasteries—many dating back to the 14th century—and the dramatic Blue Nile Falls (Tis Abay).',
            'tagline' => 'Gateway to Lake Tana & the Blue Nile Falls',
            'hero_image' => '/images/destinations/bahir-dar-hero.jpg',
            'gallery_images' => [
                [
                    'path' => '/images/destinations/bahir-dar-hero.jpg',
                    'alt' => 'Lake Tana waterfront at Bahir Dar',
                    'attribution' => 'Photo: A. Savin, Wikimedia Commons (FAL).',
                    'source_url' => 'https://commons.wikimedia.org/wiki/File:ET_Amhara_asv2018-02_img068_Lake_Tana_at_Bahir_Dar.jpg',
                    'is_primary' => true,
                ],
                [
                    'path' => '/images/destinations/bahir-dar-lake-tana-shore.jpg',
                    'alt' => 'Lake Tana shore near Bahir Dar',
                    'attribution' => 'Photo: A. Savin, Wikimedia Commons (FAL).',
                    'source_url' => 'https://commons.wikimedia.org/wiki/File:ET_Amhara_asv2018-02_img088_Lake_Tana_at_Bahir_Dar.jpg',
                    'is_primary' => false,
                ],
                [
                    'path' => '/images/destinations/bahir-dar-blue-nile-falls.jpg',
                    'alt' => 'Blue Nile Falls near Bahir Dar',
                    'attribution' => 'Photo: A. Savin, Wikimedia Commons (FAL).',
                    'source_url' => 'https://commons.wikimedia.org/wiki/File:ET_Bahir_Dar_asv2018-02_img17_Tis_Issat.jpg',
                    'is_primary' => false,
                ],
                [
                    'path' => '/images/destinations/bahir-dar-lakefront.jpg',
                    'alt' => 'Lake Tana shoreline in Bahir Dar',
                    'attribution' => 'Photo: Adam Jones, Wikimedia Commons (CC BY-SA 2.0).',
                    'source_url' => 'https://commons.wikimedia.org/wiki/File:View_from_Shore_of_Lake_Tana_-_Bahir_Dar_-_Ethiopia_-_02_(8677069911).jpg',
                    'is_primary' => false,
                ],
            ],
            'latitude' => 11.5936000,
            'longitude' => 37.3908000,
        ]);

        // Update Lalibela with verified editorial content.
        Destination::where('name', 'Lalibela')->update([
            'slug' => 'lalibela',
            'description' => 'Lalibela is world-renowned for its eleven monolithic medieval churches hand-hewn directly out of red volcanic rock in the 12th and 13th centuries, designated a UNESCO World Heritage Site in 1978. Often called the "New Jerusalem", the city remains an active pilgrimage and spiritual centre.',
            'tagline' => 'The New Jerusalem — Monolithic Rock-Hewn Churches',
            'hero_image' => '/images/destinations/lalibela-hero.jpg',
            'gallery_images' => [
                [
                    'path' => '/images/destinations/lalibela-hero.jpg',
                    'alt' => 'Rock-hewn church in Lalibela',
                    'attribution' => 'Photo: Radosław Botev, Wikimedia Commons (CC BY 3.0 PL).',
                    'source_url' => 'https://commons.wikimedia.org/wiki/File:Rock-Hewn_Churches,_Lalibela_Ethiopia_(2).jpg',
                    'is_primary' => true,
                ],
                [
                    'path' => '/images/destinations/lalibela-bete-giyorgis.jpg',
                    'alt' => 'Bete Giyorgis rock-hewn church in Lalibela',
                    'attribution' => 'Photo: Bernard Gagnon, Wikimedia Commons (CC BY-SA 3.0).',
                    'source_url' => 'https://commons.wikimedia.org/wiki/File:Bete_Giyorgis_03.jpg',
                    'is_primary' => false,
                ],
                [
                    'path' => '/images/destinations/lalibela-saint-george-sunset.jpg',
                    'alt' => 'Church of Saint George at sunset in Lalibela',
                    'attribution' => 'Photo: Thomas Fuhrmann, Wikimedia Commons (CC BY-SA 4.0).',
                    'source_url' => 'https://commons.wikimedia.org/wiki/File:Ethiopia_-_sunset_at_Church_of_Saint_George,_Lalibela_01.jpg',
                    'is_primary' => false,
                ],
                [
                    'path' => '/images/destinations/lalibela-rock-churches-detail.jpg',
                    'alt' => 'Rock-hewn church detail in Lalibela',
                    'attribution' => 'Photo: Radosław Botev, Wikimedia Commons (CC BY 3.0 PL).',
                    'source_url' => 'https://commons.wikimedia.org/wiki/File:Rock-Hewn_Churches,_Lalibela_Ethiopia_(6).jpg',
                    'is_primary' => false,
                ],
            ],
            'latitude' => 12.0320000,
            'longitude' => 39.0430000,
        ]);
    }

    private function seedGondarAttractions(): void
    {
        $gondar = Destination::where('name', 'Gondar')->first();

        if (! $gondar) {
            return;
        }

        // NOTE: Entry fees and opening hours reflect approximate reported standards
        // and may vary between domestic and international visitors.
        $attractions = [
            [
                'name' => 'Fasil Ghebbi (Royal Enclosure)',
                'slug' => 'fasil-ghebbi',
                'description' => 'Fasil Ghebbi is a 70,000-square-metre fortress-city that served as the primary residence and administrative centre of Ethiopian emperors from the 17th to 19th centuries. The compound contains castles, palaces, ceremonial banqueting halls, and a royal library built by Emperor Fasilides and his successors. Designated a UNESCO World Heritage Site in 1979, the architecture blends indigenous Ethiopian traditions with Portuguese, Moorish, and Indian baroque influences. Visitors can explore Fasilides\' Castle, Iyasu\'s Palace, the Library of Yohannes I, and the castle of Empress Mentewab inside the massive curtain walls.',
                'category' => 'heritage_site',
                'location_address' => 'Fasil Ghebbi, Central Gondar, Amhara Region, Ethiopia',
                'latitude' => 12.6087000,
                'longitude' => 37.4683000,
                'opening_hours' => '08:30 – 12:30, 13:30 – 17:30 daily',
                'entry_fee' => 200.00,
                'is_featured' => true,
                'images' => [
                    [
                        'path' => '/images/attractions/fasil-ghebbi.jpg',
                        'alt' => 'Fasil Ghebbi Royal Enclosure castle complex in Gondar',
                        'attribution' => 'Photo: A. Savin, Wikimedia Commons (FAL).',
                        'is_primary' => true,
                    ],
                    [
                        'path' => '/images/attractions/fasil-ghebbi-courtyard.jpg',
                        'alt' => 'Stone courtyard at Fasil Ghebbi Royal Enclosure in Gondar',
                        'attribution' => 'Photo: A. Savin, Wikimedia Commons (FAL).',
                        'source_url' => 'https://commons.wikimedia.org/wiki/File:ET_Gondar_asv2018-02_img18_Fasil_Ghebbi.jpg',
                        'is_primary' => false,
                    ],
                    [
                        'path' => '/images/attractions/fasil-ghebbi-royal-enclosure.jpg',
                        'alt' => 'Fasil Ghebbi royal enclosure architecture in Gondar',
                        'attribution' => 'Photo: Martijn.Munneke, Wikimedia Commons (CC BY 2.0).',
                        'source_url' => 'https://commons.wikimedia.org/wiki/File:Fasil_Ghebbi_(6821473537).jpg',
                        'is_primary' => false,
                    ],
                ],
            ],
            [
                'name' => 'Debre Berhan Selassie Church',
                'slug' => 'debre-berhan-selassie',
                'description' => 'Debre Berhan Selassie ("Trinity at the Mount of Light") is celebrated worldwide for its iconic ceiling adorned with 135 painted angelic faces looking in every direction. Built by Emperor Iyasu I in the late 17th century, the church miraculously survived the 1888 Mahdist invasion that destroyed dozens of other Gondarine churches. The interior walls feature vivid tempera frescoes depicting Old and New Testament narratives, the life of the Virgin Mary, and Saint George triumphing over the dragon.',
                'category' => 'church',
                'location_address' => 'Debre Berhan Selassie Road, Gondar, Amhara Region, Ethiopia',
                'latitude' => 12.6130000,
                'longitude' => 37.4700000,
                'opening_hours' => '08:00 – 12:00, 14:00 – 17:00 daily',
                'entry_fee' => 200.00,
                'is_featured' => true,
                'images' => [
                    [
                        'path' => '/images/attractions/debre-berhan-selassie.jpg',
                        'alt' => 'Angel-covered ceiling of Debre Berhan Selassie Church',
                        'attribution' => 'Photo: A. Savin, Wikimedia Commons (FAL).',
                        'is_primary' => true,
                    ],
                    [
                        'path' => '/images/attractions/debre-berhan-selassie-exterior.jpg',
                        'alt' => 'Exterior of Debre Berhan Selassie Church in Gondar',
                        'attribution' => 'Photo: Bernard Gagnon, Wikimedia Commons (CC BY-SA 3.0).',
                        'source_url' => 'https://commons.wikimedia.org/wiki/File:Church_of_Debra_Berhan_Selassie_01.jpg',
                        'is_primary' => false,
                    ],
                    [
                        'path' => '/images/attractions/debre-berhan-selassie-wall.jpg',
                        'alt' => 'Painted wall detail inside Debre Berhan Selassie Church',
                        'attribution' => 'Photo: Chuck Moravec, Wikimedia Commons (CC BY 2.0).',
                        'source_url' => 'https://commons.wikimedia.org/wiki/File:Gondar_Debre_Berhan_Selassie_Church_Wall_1_(28424756451).jpg',
                        'is_primary' => false,
                    ],
                ],
            ],
            [
                'name' => 'Fasilides\' Bath',
                'slug' => 'fasilides-bath',
                'description' => 'Fasilides\' Bath is a striking two-story stone pavilion set within a large sunken rectangular pool enclosed by ancient stone walls and towering sycamore fig trees. Built by Emperor Fasilides as a summer retreat and ceremonial bath, the compound is the historic epicenter of Gondar\'s world-famous Timkat (Epiphany) celebrations every January. During the festival, the pool is filled with consecrated water as thousands of faithful pilgrims gather for liturgical blessings and ceremonial immersion.',
                'category' => 'monument',
                'location_address' => 'Fasilides Bath Road, Gondar, Amhara Region, Ethiopia',
                'latitude' => 12.6100000,
                'longitude' => 37.4610000,
                'opening_hours' => '08:30 – 17:30 daily',
                'entry_fee' => 200.00,
                'is_featured' => true,
                'images' => [
                    [
                        'path' => '/images/attractions/fasilides-bath.jpg',
                        'alt' => 'Fasilides\' Bath pavilion and historic pool in Gondar',
                        'attribution' => 'Photo: A. Savin, Wikimedia Commons (FAL).',
                        'is_primary' => true,
                    ],
                    [
                        'path' => '/images/attractions/fasilides-bath-pavilion.jpg',
                        'alt' => 'Pavilion at Fasilides Bath in Gondar',
                        'attribution' => 'Photo: Bernard Gagnon, Wikimedia Commons (CC BY-SA 3.0).',
                        'source_url' => 'https://commons.wikimedia.org/wiki/File:Fasilides_Bath_03.jpg',
                        'is_primary' => false,
                    ],
                    [
                        'path' => '/images/attractions/fasilides-bath-tower.jpg',
                        'alt' => 'Historic tower at Fasilides Bath in Gondar',
                        'attribution' => 'Photo: Bernard Gagnon, Wikimedia Commons (CC BY-SA 3.0).',
                        'source_url' => 'https://commons.wikimedia.org/wiki/File:Fasilides_Bath_-_Tower.jpg',
                        'is_primary' => false,
                    ],
                ],
            ],
            [
                'name' => 'Kuskuam Royal Complex',
                'slug' => 'kuskuam-complex',
                'description' => 'The Kuskuam Complex is a hilltop royal sanctuary built in 1740 by the formidable Empress Mentewab following the death of Emperor Bakaffa. Located on the scenic western slopes of Gondar, the site preserves the ruins of Mentewab\'s castle-palace, defensive guard towers, banquet chambers, and the historic Church of Debre Tsehay (Mount of the Sun). Though quieter than Fasil Ghebbi, Kuskuam provides commanding panoramic views of the city valley and a deeper perspective on 18th-century Gondarine court life.',
                'category' => 'palace',
                'location_address' => 'Kuskuam Hill, Gondar, Amhara Region, Ethiopia',
                'latitude' => 12.6000000,
                'longitude' => 37.4420000,
                'opening_hours' => '08:30 – 17:00 daily',
                'entry_fee' => 100.00,
                'is_featured' => false,
                'images' => [
                    [
                        'path' => '/images/attractions/kuskuam.jpg',
                        'alt' => 'Ruins of Empress Mentewab\'s palace and towers at Kuskuam',
                        'attribution' => 'Photo: A. Savin, Wikimedia Commons (FAL).',
                        'is_primary' => true,
                    ],
                    [
                        'path' => '/images/attractions/kuskuam-palace-2.jpg',
                        'alt' => 'Ruins at the Kuskuam Royal Complex in Gondar',
                        'attribution' => 'Photo: DonCamillo, Wikimedia Commons (CC BY-SA 4.0).',
                        'source_url' => 'https://commons.wikimedia.org/wiki/File:Kuskuam_Palace_092018_-_2.jpg',
                        'is_primary' => false,
                    ],
                    [
                        'path' => '/images/attractions/kuskuam-palace-3.jpg',
                        'alt' => 'Kuskuam Royal Complex palace ruins in Gondar',
                        'attribution' => 'Photo: DonCamillo, Wikimedia Commons (CC BY-SA 4.0).',
                        'source_url' => 'https://commons.wikimedia.org/wiki/File:Kuskuam_Palace_092018_-_3.jpg',
                        'is_primary' => false,
                    ],
                    [
                        'path' => '/images/attractions/kuskuam-palace-4.jpg',
                        'alt' => 'Historic stonework at Kuskuam Royal Complex in Gondar',
                        'attribution' => 'Photo: DonCamillo, Wikimedia Commons (CC BY-SA 4.0).',
                        'source_url' => 'https://commons.wikimedia.org/wiki/File:Kuskuam_Palace_092018_-_4.jpg',
                        'is_primary' => false,
                    ],
                ],
            ],
        ];

        foreach ($attractions as $data) {
            Attraction::updateOrCreate(
                ['slug' => $data['slug']],
                array_merge($data, ['destination_id' => $gondar->destination_id]),
            );
        }
    }
}
