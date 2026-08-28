@extends('layouts.app')

@php
    $type = $provider->provider_type;
    $config = match($type) {
        'restaurant' => [
            'badge' => '🍽️ Official Restaurant & Culinary Provider Application',
            'title' => 'Restaurant & Culinary Profile',
            'classification_label' => 'Dining & Culinary Style',
            'classification_options' => [
                'traditional_messob' => 'Traditional Habesha Messob Cultural Dining',
                'modern_fusion' => 'Modern Ethiopian Fusion Restaurant',
                'coffee_house' => 'Traditional Coffee House & Roastery',
                'rooftop_lounge' => 'Rooftop Lounge with Castle View',
                'bistro_bakery' => 'Bistro, Bakery & Fast Casual',
            ],
            'contact1_name' => 'General Manager / Owner Full Name',
            'contact1_title' => 'Executive Designation',
            'contact1_placeholder' => 'e.g. Ato Muluken Getachew',
            'contact2_name' => 'Executive Head Chef Full Name',
            'contact2_title' => 'Culinary Designation',
            'contact2_placeholder' => 'e.g. Chef Almaz Tadesse',
            'permit_label' => 'Food Safety & Health Inspection Certificate #',
            'permit_placeholder' => 'e.g. MOH-FSC-GDR-2024-4412',
            'capacity_label' => 'Total Seating Capacity (Messobs & Tables)',
            'time_label' => 'Operating Hours (e.g. 07:00 – 23:00 daily)',
            'amenities_heading' => 'Cuisine Specialties & Dining Experience Checklist',
            'amenities' => [
                'kitfo' => ['label' => 'Gondar Traditional Kitfo', 'icon' => 'egg-fried'],
                'doro_wat' => ['label' => 'Doro Wat Feast & Traditional Stews', 'icon' => 'fire'],
                'fasting_buffet' => ['label' => 'Fasting / 100% Vegan Mahberawi Platter', 'icon' => 'flower1'],
                'azmari_bet' => ['label' => 'Live Traditional Music (Azmari Bet)', 'icon' => 'music-note-beamed'],
                'coffee_ceremony' => ['label' => 'Abol-Tona-Bereka Coffee Ceremony', 'icon' => 'cup-hot-fill'],
                'tej_tasting' => ['label' => 'House-Brewed Tej (Honey Wine)', 'icon' => 'droplet-fill'],
                'generator' => ['label' => '24/7 Backup Generator Power', 'icon' => 'lightning-charge-fill'],
                'patio_view' => ['label' => 'Outdoor Patio & Castle Sunset View', 'icon' => 'sun-fill'],
                'wifi' => ['label' => 'Complimentary Guest Wi-Fi', 'icon' => 'wifi'],
                'parking' => ['label' => 'Private Valet & Parking Area', 'icon' => 'car-front-fill'],
            ],
            'description_label' => 'Culinary Philosophy & Heritage Atmosphere',
            'description_placeholder' => 'Describe your traditional recipes, local ingredient sourcing from Amhara farmers, live music performances, and dining atmosphere...',
        ],
        'transportation_car_rental' => [
            'badge' => '🚐 Official Transportation & Fleet Operator Application',
            'title' => 'Transportation & Fleet Profile',
            'classification_label' => 'Fleet Operator Classification',
            'classification_options' => [
                'safari_4x4' => '4x4 Safari & Simien Expedition Fleet',
                'luxury_coaster' => 'Coaster Luxury Tour Bus Operator',
                'airport_shuttle' => 'Airport VIP Shuttle & City Transfer',
                'private_chauffeur' => 'Private Chauffeur & Luxury SUV Fleet',
            ],
            'contact1_name' => 'Fleet Operations Director / Owner Name',
            'contact1_title' => 'Operations Designation',
            'contact1_placeholder' => 'e.g. Ato Dawit Hailu',
            'contact2_name' => 'Chief Dispatcher / Route Manager',
            'contact2_title' => 'Dispatch Designation',
            'contact2_placeholder' => 'e.g. Tewodros Kassahun',
            'permit_label' => 'Road Transport Authority (RTA) Commercial Permit #',
            'permit_placeholder' => 'e.g. RTA-COMM-ETH-89201',
            'capacity_label' => 'Active Fleet Size (Total Vehicles)',
            'time_label' => 'Dispatch Availability (e.g. 24/7 On-Call Dispatch)',
            'amenities_heading' => 'Fleet Safety Standards & Equipment Checklist',
            'amenities' => [
                '4wd_high_clearance' => ['label' => '4WD High Ground Clearance Safari Spec', 'icon' => 'truck'],
                'dual_ac' => ['label' => 'Dual Front & Rear Air Conditioning', 'icon' => 'snow'],
                'certified_driver' => ['label' => 'English-Speaking Certified Professional Driver', 'icon' => 'person-badge'],
                'gps_tracking' => ['label' => 'Real-Time GPS Vehicle Tracking', 'icon' => 'geo-alt-fill'],
                'passenger_insurance' => ['label' => 'Comprehensive Passenger Public Insurance', 'icon' => 'shield-check'],
                'luggage_rack' => ['label' => 'Heavy-Duty Roof Safari Luggage Rack', 'icon' => 'box-seam-fill'],
                'first_aid_kit' => ['label' => 'Emergency Medical First Aid Kit', 'icon' => 'heart-pulse-fill'],
                'dual_spare_tires' => ['label' => 'Dual Spare Off-Road Tires & Recovery Gear', 'icon' => 'wrench-adjustable'],
                'usb_charging' => ['label' => 'Passenger USB Fast Charging Ports', 'icon' => 'phone-vibrate'],
                'bottled_water' => ['label' => 'Complimentary Bottled Spring Water', 'icon' => 'droplet-fill'],
            ],
            'description_label' => 'Fleet Capabilities, Safety Protocols & Expedition Routes',
            'description_placeholder' => 'Describe your vehicle models (Toyota Land Cruiser Prado, HZJ76, Coaster buses), safety maintenance protocols, and popular routes (Simien Mountains, Lake Tana, Lalibela circuit)...',
        ],
        'event_organizer' => [
            'badge' => '🎭 Official Cultural Event Secretariat Application',
            'title' => 'Cultural Event Secretariat Profile',
            'classification_label' => 'Secretariat & Event Organization Type',
            'classification_options' => [
                'timkat_secretariat' => 'Liturgical Epiphany (Timkat) Secretariat',
                'meskel_committee' => 'Meskel Feast & Demera Organizing Committee',
                'heritage_festival' => 'Annual Heritage & Arts Cultural Festival',
                'pilgrimage_org' => 'Orthodox Sacred Pilgrimage Organization',
            ],
            'contact1_name' => 'Secretariat General Coordinator / Director',
            'contact1_title' => 'Secretariat Title',
            'contact1_placeholder' => 'e.g. Memhir Girma Belay',
            'contact2_name' => 'Clergy / Cultural Liaison Officer',
            'contact2_title' => 'Liaison Designation',
            'contact2_placeholder' => 'e.g. Deacon Solomon Worku',
            'permit_label' => 'City Cultural Bureau Assembly Permit #',
            'permit_placeholder' => 'e.g. CUL-PERMIT-GDR-2026-009',
            'capacity_label' => 'Expected Audience & Pilgrim Capacity',
            'time_label' => 'Celebration Timetable & Procession Hours',
            'amenities_heading' => 'Liturgical Protocols & Safety Logistics Checklist',
            'amenities' => [
                'dress_code' => ['label' => 'Traditional White Attire (Netela/Gabi) Enforcement', 'icon' => 'check-circle-fill'],
                'vip_clergy_seating' => ['label' => 'VIP Clergy & International Dignitary Seating', 'icon' => 'star-fill'],
                'perimeter_security' => ['label' => 'Coordinated Security & Perimeter Access Control', 'icon' => 'shield-lock-fill'],
                'medical_station' => ['label' => 'On-Site Red Cross Emergency Medical Tent', 'icon' => 'heart-pulse-fill'],
                'liturgical_soundstage' => ['label' => 'Acoustic Soundstage for Yaredic Choirs (Mahbere Kidusan)', 'icon' => 'soundwave'],
                'broadcast_media' => ['label' => 'Accredited International Media & Camera Riser', 'icon' => 'camera-reels-fill'],
                'lost_found' => ['label' => 'Pilgrim Assistance & Lost & Found Information Desk', 'icon' => 'info-circle-fill'],
                'water_stations' => ['label' => 'Clean Drinking Water Distribution Points', 'icon' => 'droplet-fill'],
            ],
            'description_label' => 'Celebration Significance, Holy Sites & Procession Routes',
            'description_placeholder' => 'Describe the spiritual and cultural importance of the event, procession pathways from churches to Fasilides Bath, and logistical preparations for pilgrims...',
        ],
        default => [
            'badge' => '🏨 Official Hotel & Lodging Application',
            'title' => 'Hotel & Lodging Profile',
            'classification_label' => 'Hospitality Classification',
            'classification_options' => [
                '5_star' => '5-Star Luxury Resort',
                '4_star' => '4-Star Executive Hotel',
                '3_star' => '3-Star Standard Hotel',
                'heritage_lodge' => 'Boutique Heritage Lodge',
                'eco_lodge' => 'Eco-Lodge & Nature Retreat',
                'city_hotel' => 'City Center Commercial Hotel',
            ],
            'contact1_name' => 'General Manager Full Name',
            'contact1_title' => 'Executive Designation',
            'contact1_placeholder' => 'e.g. Ato Abnet Kebede',
            'contact2_name' => 'Front Desk / Guest Relations Manager',
            'contact2_title' => 'Management Title',
            'contact2_placeholder' => 'e.g. W/ro Hiwot Assefa',
            'permit_label' => 'Ministry of Tourism Star Rating Certificate #',
            'permit_placeholder' => 'e.g. MOT-STAR-GDR-2024-8891',
            'capacity_label' => 'Total Physical Rooms Capacity',
            'time_label' => 'Standard Check-in / Check-out Times',
            'amenities_heading' => 'Verified Property Amenities Checklist',
            'amenities' => [
                'wifi' => ['label' => 'High-Speed Wi-Fi', 'icon' => 'wifi'],
                'generator' => ['label' => '24/7 Backup Generator Power', 'icon' => 'lightning-charge-fill'],
                'breakfast' => ['label' => 'Complimentary Breakfast', 'icon' => 'cup-hot-fill'],
                'shuttle' => ['label' => 'Airport Shuttle Service', 'icon' => 'airplane-fill'],
                'coffee' => ['label' => 'Traditional Coffee Ceremony', 'icon' => 'fire'],
                'pool' => ['label' => 'Swimming Pool', 'icon' => 'water'],
                'restaurant' => ['label' => 'On-site Restaurant & Bar', 'icon' => 'egg-fried'],
                'spa' => ['label' => 'Spa & Wellness Center', 'icon' => 'flower1'],
                'security' => ['label' => '24/7 Security & CCTV', 'icon' => 'shield-lock-fill'],
                'parking' => ['label' => 'Free Private Parking', 'icon' => 'car-front-fill'],
            ],
            'description_label' => 'Detailed Property Overview & Heritage Story',
            'description_placeholder' => 'Describe your property, proximity to historic sites (e.g. Fasil Ghebbi castles), room accommodations, dining options, and guest services...',
        ]
    };
@endphp

@section('title', $config['title'] . ' · ' . $provider->business_name)

@section('content')
<div class="container-fluid py-4 py-lg-5 px-3 px-lg-5">
    {{-- Header & Stepper Indicator --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-3 border-bottom">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="{{ route('provider.status') }}" class="text-success text-decoration-none fw-semibold">Application Status</a></li>
                    <li class="breadcrumb-item active text-muted" aria-current="page">{{ $config['title'] }}</li>
                </ol>
            </nav>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-0.5" style="font-size: 0.72rem; font-weight: 700;">
                    {{ $config['badge'] }}
                </span>
                <span class="badge bg-light text-dark border rounded-pill px-2.5 py-0.5 font-monospace" style="font-size: 0.72rem;">
                    ID #APP-{{ str_pad($provider->provider_id, 4, '0', STR_PAD_LEFT) }}
                </span>
            </div>
            <h1 class="h3 fw-bold mb-0 text-dark" style="font-family: var(--font-display); letter-spacing: -0.02em;">
                Provider Profile
            </h1>
            <p class="text-secondary mb-0 small">
                Complete your organizational credentials, leadership contacts, and operational specifications for Tourism Bureau verification.
            </p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a class="btn btn-outline-secondary btn-sm rounded-pill px-3.5 py-2 fw-semibold" href="{{ route('provider.status') }}">
                &larr; Return to Application Status
            </a>
        </div>
    </div>

    @include('layouts.partials.flash-messages')

    <form method="POST" action="{{ route('provider.profile.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row g-4">
            {{-- Left Column (8 cols): The 5 Application Modules --}}
            <div class="col-lg-8">
                {{-- Module 1: Business Identity & Classification --}}
                <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
                    <div class="card-header bg-white p-3.5 border-bottom d-flex align-items-center gap-2.5">
                        <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="bi bi-building fs-6"></i>
                        </div>
                        <div>
                            <h2 class="h6 fw-bold mb-0 text-dark" style="font-family: var(--font-display);">1. Commercial &amp; Brand Identity</h2>
                            <span class="text-muted small" style="font-size: 0.75rem;">Official entity name, jurisdiction, and vertical classification</span>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-7">
                                <label class="form-label small fw-bold text-dark" for="business_name">Business name <span class="text-danger">*</span></label>
                                <input class="form-control rounded-3 @error('business_name') is-invalid @enderror" id="business_name" name="business_name" value="{{ old('business_name', $provider->business_name) }}" required placeholder="e.g. {{ $provider->business_name ?: 'Your Business Name' }}">
                                @error('business_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-5">
                                <label class="form-label small fw-bold text-dark" for="star_rating">{{ $config['classification_label'] }}</label>
                                <select class="form-select rounded-3 @error('star_rating') is-invalid @enderror" id="star_rating" name="star_rating">
                                    <option value="">Select classification tier...</option>
                                    @foreach($config['classification_options'] as $optKey => $optLabel)
                                        <option value="{{ $optKey }}" @selected(old('star_rating', $provider->star_rating) === $optKey)>
                                            {{ $optLabel }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('star_rating')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark" for="destination_id">Destination Jurisdiction</label>
                                <select class="form-select rounded-3 @error('destination_id') is-invalid @enderror" id="destination_id" name="destination_id">
                                    <option value="">Select regional destination...</option>
                                    @foreach($destinations as $dest)
                                        <option value="{{ $dest->destination_id }}" @selected(old('destination_id', $provider->destination_id) == $dest->destination_id)>
                                            {{ $dest->name }} {{ $dest->tagline ? '· '.$dest->tagline : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('destination_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark" for="physical_address">Physical Address / Depot Location</label>
                                <input class="form-control rounded-3 @error('physical_address') is-invalid @enderror" id="physical_address" name="physical_address" value="{{ old('physical_address', $provider->physical_address) }}" placeholder="e.g. Piazza Kebele 02, Near Fasil Ghebbi, Gondar">
                                @error('physical_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Module 2: Key Personnel & Executive Contacts --}}
                <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
                    <div class="card-header bg-white p-3.5 border-bottom d-flex align-items-center gap-2.5">
                        <div class="rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="bi bi-person-badge fs-6"></i>
                        </div>
                        <div>
                            <h2 class="h6 fw-bold mb-0 text-dark" style="font-family: var(--font-display);">2. Key Leadership &amp; Operational Contacts</h2>
                            <span class="text-muted small" style="font-size: 0.75rem;">Verified management credentials and direct communication channels</span>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark" for="manager_name">{{ $config['contact1_name'] }}</label>
                                <input class="form-control rounded-3 @error('manager_name') is-invalid @enderror" id="manager_name" name="manager_name" value="{{ old('manager_name', $provider->manager_name) }}" placeholder="{{ $config['contact1_placeholder'] }}">
                                @error('manager_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark" for="manager_title">{{ $config['contact1_title'] }}</label>
                                <input class="form-control rounded-3 @error('manager_title') is-invalid @enderror" id="manager_title" name="manager_title" value="{{ old('manager_title', $provider->manager_title) }}" placeholder="e.g. General Manager / Director">
                                @error('manager_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark" for="secondary_contact_name">{{ $config['contact2_name'] }}</label>
                                <input class="form-control rounded-3 @error('secondary_contact_name') is-invalid @enderror" id="secondary_contact_name" name="secondary_contact_name" value="{{ old('secondary_contact_name', $provider->secondary_contact_name) }}" placeholder="{{ $config['contact2_placeholder'] }}">
                                @error('secondary_contact_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark" for="secondary_contact_title">{{ $config['contact2_title'] }}</label>
                                <input class="form-control rounded-3 @error('secondary_contact_title') is-invalid @enderror" id="secondary_contact_title" name="secondary_contact_title" value="{{ old('secondary_contact_title', $provider->secondary_contact_title) }}" placeholder="e.g. Head Chef / Chief Dispatcher">
                                @error('secondary_contact_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark" for="manager_phone">Direct Mobile / WhatsApp</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light small font-monospace">+251</span>
                                    <input class="form-control rounded-end @error('manager_phone') is-invalid @enderror" id="manager_phone" name="manager_phone" value="{{ old('manager_phone', $provider->manager_phone) }}" placeholder="91 876 5432">
                                </div>
                                @error('manager_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark" for="contact_email">Official Reservation / Contact Email</label>
                                <input type="email" class="form-control rounded-3 @error('contact_email') is-invalid @enderror" id="contact_email" name="contact_email" value="{{ old('contact_email', $provider->contact_email) }}" placeholder="e.g. info@business.com">
                                @error('contact_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Module 3: Legal Compliance & Escrow Settlement Payout --}}
                <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
                    <div class="card-header bg-white p-3.5 border-bottom d-flex align-items-center gap-2.5">
                        <div class="rounded-circle bg-warning-subtle text-warning-emphasis d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="bi bi-shield-check fs-6"></i>
                        </div>
                        <div>
                            <h2 class="h6 fw-bold mb-0 text-dark" style="font-family: var(--font-display);">3. Legal Verification &amp; Escrow Settlement</h2>
                            <span class="text-muted small" style="font-size: 0.75rem;">Commercial permits, tax ID, and verified Ethiopian banking details</span>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-dark" for="tin_number">Taxpayer ID (TIN #)</label>
                                <input class="form-control rounded-3 font-monospace @error('tin_number') is-invalid @enderror" id="tin_number" name="tin_number" value="{{ old('tin_number', $provider->tin_number) }}" placeholder="e.g. 0084920194">
                                @error('tin_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-dark" for="trade_license_number">Trade License #</label>
                                <input class="form-control rounded-3 font-monospace @error('trade_license_number') is-invalid @enderror" id="trade_license_number" name="trade_license_number" value="{{ old('trade_license_number', $provider->trade_license_number) }}" placeholder="e.g. TRD-GDR-2024-8891">
                                @error('trade_license_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-dark" for="permit_number">{{ $config['permit_label'] }}</label>
                                <input class="form-control rounded-3 font-monospace @error('permit_number') is-invalid @enderror" id="permit_number" name="permit_number" value="{{ old('permit_number', $provider->permit_number) }}" placeholder="{{ $config['permit_placeholder'] }}">
                                @error('permit_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-dark" for="payout_bank_name">Escrow Settlement Bank</label>
                                <select class="form-select rounded-3 @error('payout_bank_name') is-invalid @enderror" id="payout_bank_name" name="payout_bank_name">
                                    <option value="">Select Ethiopian bank...</option>
                                    <option value="Commercial Bank of Ethiopia (CBE)" @selected(old('payout_bank_name', $provider->payout_bank_name) === 'Commercial Bank of Ethiopia (CBE)')>Commercial Bank of Ethiopia (CBE)</option>
                                    <option value="Awash Bank" @selected(old('payout_bank_name', $provider->payout_bank_name) === 'Awash Bank')>Awash Bank</option>
                                    <option value="Dashen Bank" @selected(old('payout_bank_name', $provider->payout_bank_name) === 'Dashen Bank')>Dashen Bank</option>
                                    <option value="Bank of Abyssinia" @selected(old('payout_bank_name', $provider->payout_bank_name) === 'Bank of Abyssinia')>Bank of Abyssinia</option>
                                    <option value="Telebirr Business Escrow" @selected(old('payout_bank_name', $provider->payout_bank_name) === 'Telebirr Business Escrow')>Telebirr Business Escrow</option>
                                    <option value="Cooperative Bank of Oromia" @selected(old('payout_bank_name', $provider->payout_bank_name) === 'Cooperative Bank of Oromia')>Cooperative Bank of Oromia</option>
                                </select>
                                @error('payout_bank_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-dark" for="payout_account_number">Bank Account Number</label>
                                <input class="form-control rounded-3 font-monospace @error('payout_account_number') is-invalid @enderror" id="payout_account_number" name="payout_account_number" value="{{ old('payout_account_number', $provider->payout_account_number) }}" placeholder="e.g. 1000192837482">
                                @error('payout_account_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-dark" for="payout_account_name">Registered Account Name</label>
                                <input class="form-control rounded-3 @error('payout_account_name') is-invalid @enderror" id="payout_account_name" name="payout_account_name" value="{{ old('payout_account_name', $provider->payout_account_name) }}" placeholder="e.g. {{ $provider->business_name ?: 'Business' }} PLC">
                                @error('payout_account_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Module 4: Operating Specifications & Vertical Features --}}
                <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
                    <div class="card-header bg-white p-3.5 border-bottom d-flex align-items-center gap-2.5">
                        <div class="rounded-circle bg-info-subtle text-info-emphasis d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="bi bi-sliders fs-6"></i>
                        </div>
                        <div>
                            <h2 class="h6 fw-bold mb-0 text-dark" style="font-family: var(--font-display);">4. Operating Specifications &amp; Standards</h2>
                            <span class="text-muted small" style="font-size: 0.75rem;">Capacity, service schedules, and quality assurance attributes</span>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark" for="capacity_count">{{ $config['capacity_label'] }}</label>
                                <input type="number" min="1" max="50000" class="form-control rounded-3 font-monospace @error('capacity_count') is-invalid @enderror" id="capacity_count" name="capacity_count" value="{{ old('capacity_count', $provider->capacity_count ?: $provider->total_rooms_count) }}">
                                @error('capacity_count')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark" for="operating_hours">{{ $config['time_label'] }}</label>
                                <input class="form-control rounded-3 font-monospace @error('operating_hours') is-invalid @enderror" id="operating_hours" name="operating_hours" value="{{ old('operating_hours', $provider->operating_hours) }}" placeholder="e.g. 07:00 – 23:00 daily">
                                @error('operating_hours')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <label class="form-label small fw-bold text-dark mb-2">{{ $config['amenities_heading'] }}</label>
                        @php
                            $selectedAmenities = (array) old('amenities', $provider->amenities ?: []);
                        @endphp
                        <div class="row g-2">
                            @foreach($config['amenities'] as $key => $item)
                                <div class="col-sm-6">
                                    <div class="form-check p-2.5 rounded-3 border bg-light-subtle d-flex align-items-center gap-2">
                                        <input class="form-check-input ms-0 me-2" type="checkbox" name="amenities[]" value="{{ $key }}" id="amenity_{{ $key }}" @checked(in_array($key, $selectedAmenities))>
                                        <label class="form-check-label small fw-semibold text-dark cursor-pointer d-flex align-items-center gap-1.5" for="amenity_{{ $key }}">
                                            <i class="bi bi-{{ $item['icon'] }} text-success"></i> {{ $item['label'] }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Module 5: Presentation & Heritage Story --}}
                <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
                    <div class="card-header bg-white p-3.5 border-bottom d-flex align-items-center gap-2.5">
                        <div class="rounded-circle bg-secondary-subtle text-dark d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="bi bi-card-text fs-6"></i>
                        </div>
                        <div>
                            <h2 class="h6 fw-bold mb-0 text-dark" style="font-family: var(--font-display);">5. Experience Presentation &amp; Heritage Narrative</h2>
                            <span class="text-muted small" style="font-size: 0.75rem;">Describe your historical authenticity, customer experience, and unique value</span>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-0">
                            <label class="form-label small fw-bold text-dark" for="description">{{ $config['description_label'] }}</label>
                            <textarea class="form-control rounded-3 @error('description') is-invalid @enderror" id="description" name="description" rows="4" placeholder="{{ $config['description_placeholder'] }}">{{ old('description', $provider->description) }}</textarea>
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column (4 cols): Submission Sticky Card --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4 sticky-top" style="top: 2rem;">
                    <h2 class="h6 fw-bold mb-1 text-dark" style="font-family: var(--font-display);">
                        Submit Application
                    </h2>
                    <p class="small text-muted mb-3">
                        Save and submit your updated organization details directly to the Tourism Bureau verification queue.
                    </p>

                    <div class="d-grid gap-2 mb-3">
                        <button class="btn btn-success rounded-pill py-2.5 fw-bold shadow-sm" type="submit">
                            <i class="bi bi-check2-circle me-1.5"></i> Save profile
                        </button>
                        <a class="btn btn-light border rounded-pill py-2 text-muted fw-semibold small" href="{{ route('provider.status') }}">
                            Cancel &amp; Return
                        </a>
                    </div>

                    <div class="p-3 rounded-3 bg-light-subtle border">
                        <div class="small fw-bold text-dark mb-1">
                            <i class="bi bi-shield-check text-success me-1"></i> Sovereign Audit Guarantee
                        </div>
                        <p class="small text-muted mb-0" style="font-size: 0.75rem; line-height: 1.5;">
                            All submitted credentials are reviewed by certified municipal Tourism Bureau officers in accordance with regional hospitality and safety guidelines.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <div class="card border rounded-3 mt-4">
        <div class="card-header bg-white"><h2 class="h6 mb-0">Verification documents</h2></div>
        <div class="card-body">
            <p class="small text-muted">Upload readable legal, tax, identity, and permit documents. Files are stored privately for Bureau review.</p>
            <form method="POST" action="{{ route('provider.verification-documents.store') }}" enctype="multipart/form-data" class="row g-3 align-items-end mb-3">
                @csrf
                <div class="col-md-4"><label class="form-label" for="provider_document_type">Document type</label><select class="form-select" id="provider_document_type" name="document_type" required><option value="tin">TIN document</option><option value="trade_license">Trade license</option><option value="permit">Operating permit</option><option value="identity">Identity document</option><option value="other">Other</option></select></div>
                <div class="col-md-5"><label class="form-label" for="provider_document">File</label><input class="form-control" id="provider_document" type="file" name="document" accept="application/pdf,image/jpeg,image/png,image/webp" required></div>
                <div class="col-md-3"><button class="btn btn-outline-primary w-100" type="submit">Upload document</button></div>
            </form>
            @forelse($provider->verificationDocuments as $document)<div class="d-flex justify-content-between align-items-center border-top py-2"><span class="small">{{ ucfirst(str_replace('_', ' ', $document->document_type)) }} · {{ $document->original_name }}</span><x-ui.status-badge :status="$document->status" /></div>@empty<div class="small text-muted">No documents uploaded yet.</div>@endforelse
        </div>
    </div>
</div>
@endsection
