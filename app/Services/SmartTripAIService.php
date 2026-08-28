<?php

namespace App\Services;

use App\Http\Controllers\PublicMapDataController;
use App\Models\CulturalEvent;
use App\Models\Destination;
use App\Models\Review;
use App\Models\TourGuide;
use App\Models\TourismService;
use App\Models\Trip;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SmartTripAIService
{
    public const MAX_ITEMS = 20;

    public function __construct(
        private readonly GlobalSearchService $search,
        private readonly SmartTripRecommendationService $deterministic,
        private readonly TripItemTargetResolver $targets,
        private readonly TourGuideAvailabilityService $guideAvailability,
        private readonly HotelAvailabilityService $hotelAvailability,
        private readonly RestaurantAvailabilityService $restaurantAvailability,
        private readonly TransportationAvailabilityService $transportationAvailability,
    ) {}

    /**
     * Return a validated AI itinerary, or the deterministic planner when AI is unavailable.
     * The returned entities have already been resolved through the public application rules.
     *
     * @return array{trip_summary:string,days:array<int,array{date:string,items:array<int,array<string,mixed>}>,notes:array<int,string>,warnings:array<int,string>,source:string,fallback:bool}
     */
    public function plan(Trip $trip, string $intent): array
    {
        $intent = trim($intent);
        $maxIntent = max(200, (int) config('services.openai.max_intent_chars', 2000));
        if ($intent === '' || mb_strlen($intent) > $maxIntent) {
            throw ValidationException::withMessages(['intent' => "Trip intent must be between 1 and {$maxIntent} characters."]);
        }

        if (! config('services.openai.key')) {
            return $this->fallback($trip, ['AI planning is not configured, so deterministic Smart Trip suggestions are shown.']);
        }

        try {
            $body = $this->request($this->initialPayload($trip, $intent));
            $body = $this->resolveToolCalls($trip, $body);
            $structured = $this->extractStructuredOutput($body);
            if (! is_array($structured)) {
                throw new \RuntimeException('The AI response did not contain structured itinerary data.');
            }

            $validated = $this->validateStructuredPlan($trip, $structured);
            if ($validated['days'] === []) {
                return $this->fallback($trip, ['The AI did not select any verified public resources, so deterministic suggestions are shown.']);
            }

            return $validated + ['source' => 'openai', 'fallback' => false];
        } catch (\Throwable $exception) {
            Log::warning('Smart Trip AI request failed; using deterministic fallback.', [
                'exception' => $exception::class,
                'message' => Str::limit($exception->getMessage(), 200),
            ]);

            return $this->fallback($trip, ['AI planning is temporarily unavailable. Deterministic Smart Trip suggestions are shown instead.']);
        }
    }

    /** @return array<string,mixed> */
    public function executeTool(Trip $trip, string $name, array $arguments): array
    {
        return match ($name) {
            'search_tourism' => $this->toolSearch($arguments),
            'get_destination' => $this->toolDestination($arguments),
            'get_nearby_tourism' => $this->toolNearby($arguments),
            'get_events' => $this->toolEvents($arguments),
            'get_reviews' => $this->toolReviews($arguments),
            'get_trip_details' => $this->toolTripDetails($trip, $arguments),
            'check_availability' => $this->toolAvailability($arguments),
            'get_guide_availability' => $this->toolGuideAvailability($arguments),
            default => ['error' => 'Unknown application tool.'],
        };
    }

    /** @return array<string,mixed> */
    private function initialPayload(Trip $trip, string $intent): array
    {
        $trip->loadMissing('destinations');
        $catalog = $this->deterministic->recommendations($trip)->take(40)->map(fn (array $candidate): array => [
            'entity_type' => $candidate['item_type'],
            'entity_id' => (int) $candidate['item_id'],
            'title' => $candidate['title'],
            'destination_id' => $candidate['destination_id'],
            'rating' => $candidate['rating'],
            'review_count' => $candidate['review_count'],
            'planned_date' => $candidate['planned_date'],
        ])->values()->all();

        $tripData = [
            'dates' => [$trip->start_date->toDateString(), $trip->end_date->toDateString()],
            'destinations' => $trip->destinations->map(fn (Destination $destination): array => [
                'id' => (int) $destination->destination_id,
                'name' => $destination->name,
            ])->all(),
            'preferences' => array_values($trip->preferences ?? []),
        ];

        $system = 'You are the Ethio Tour Smart Trip planning assistant. Use only entities returned by application tools or the supplied verified catalog. Never invent names, IDs, prices, availability, coordinates, opening hours, guide credentials, or reviews. Return only the requested JSON schema. You are advisory: do not create bookings, change booking status, or modify payment data. Explain uncertainty in warnings.';
        $user = json_encode([
            'intent' => $intent,
            'trip' => $tripData,
            'verified_catalog' => $catalog,
            'instructions' => 'Select at most 20 valid resources and assign them only to dates inside the trip. Use tools when more information is needed. Keep reasons concise and factual.',
        ], JSON_THROW_ON_ERROR);

        return [
            'model' => config('services.openai.model', 'gpt-5-mini'),
            'store' => false,
            'input' => [
                ['role' => 'system', 'content' => [['type' => 'input_text', 'text' => $system]]],
                ['role' => 'user', 'content' => [['type' => 'input_text', 'text' => $user]]],
            ],
            'tools' => $this->toolDefinitions(),
            'text' => ['format' => [
                'type' => 'json_schema',
                'name' => 'smart_trip_itinerary',
                'strict' => true,
                'schema' => $this->outputSchema(),
            ]],
        ];
    }

    /** @param array<string,mixed> $payload */
    private function request(array $payload): array
    {
        $response = Http::withToken((string) config('services.openai.key'))
            ->acceptJson()
            ->timeout(max(5, (int) config('services.openai.timeout', 20)))
            ->post(rtrim((string) config('services.openai.base_url', 'https://api.openai.com/v1'), '/').'/responses', $payload);

        return $response->throw()->json();
    }

    /** @param array<string,mixed> $body */
    private function resolveToolCalls(Trip $trip, array $body): array
    {
        $calls = 0;
        while (($functionCalls = $this->functionCalls($body)) !== []) {
            $calls += count($functionCalls);
            if ($calls > max(1, (int) config('services.openai.max_tool_calls', 6)) || empty($body['id'])) {
                throw new \RuntimeException('The AI tool-call limit was reached.');
            }

            $outputs = [];
            foreach ($functionCalls as $call) {
                $arguments = json_decode((string) ($call['arguments'] ?? '{}'), true);
                $arguments = is_array($arguments) ? $arguments : [];
                $result = $this->executeTool($trip, (string) ($call['name'] ?? ''), $arguments);
                $outputs[] = [
                    'type' => 'function_call_output',
                    'call_id' => $call['call_id'] ?? $call['id'] ?? '',
                    'output' => json_encode($result, JSON_THROW_ON_ERROR),
                ];
            }

            $body = $this->request([
                'model' => config('services.openai.model', 'gpt-5-mini'),
                'store' => false,
                'previous_response_id' => $body['id'],
                'input' => $outputs,
                'tools' => $this->toolDefinitions(),
                'text' => ['format' => [
                    'type' => 'json_schema',
                    'name' => 'smart_trip_itinerary',
                    'strict' => true,
                    'schema' => $this->outputSchema(),
                ]],
            ]);
        }

        return $body;
    }

    /** @param array<string,mixed> $body @return array<int,array<string,mixed>> */
    private function functionCalls(array $body): array
    {
        return array_values(array_filter($body['output'] ?? [], fn ($item): bool => is_array($item) && ($item['type'] ?? null) === 'function_call'));
    }

    /** @param array<string,mixed> $structured @return array<string,mixed> */
    private function validateStructuredPlan(Trip $trip, array $structured): array
    {
        $start = CarbonImmutable::parse($trip->start_date->toDateString());
        $end = CarbonImmutable::parse($trip->end_date->toDateString());
        $days = [];
        $seen = [];
        $warnings = $this->stringList($structured['warnings'] ?? [], 10, 300);
        $notes = $this->stringList($structured['notes'] ?? [], 10, 300);
        $invalid = 0;

        foreach (array_slice(is_array($structured['days'] ?? null) ? $structured['days'] : [], 0, self::MAX_ITEMS) as $day) {
            if (! is_array($day) || ! is_string($day['date'] ?? null)) {
                $invalid++;

                continue;
            }

            try {
                $date = CarbonImmutable::createFromFormat('Y-m-d', $day['date']);
            } catch (\Throwable) {
                $invalid++;

                continue;
            }
            if ($date->format('Y-m-d') !== $day['date'] || $date->lt($start) || $date->gt($end)) {
                $invalid++;

                continue;
            }

            $items = [];
            foreach (array_slice(is_array($day['items'] ?? null) ? $day['items'] : [], 0, self::MAX_ITEMS) as $item) {
                if (! is_array($item) || ! isset($item['entity_type'], $item['entity_id'])) {
                    $invalid++;

                    continue;
                }
                $type = (string) $item['entity_type'];
                $id = filter_var($item['entity_id'], FILTER_VALIDATE_INT);
                $key = $type.':'.$id;
                $target = $id && in_array($type, TripItemTargetResolver::TYPES, true) ? $this->targets->resolve($type, $id) : null;
                $eventDateMismatch = $type === 'event'
                    && $target instanceof CulturalEvent
                    && $target->event_date?->toDateString() !== $date->toDateString();
                if ($target === null || $eventDateMismatch || isset($seen[$key]) || count($items) >= self::MAX_ITEMS) {
                    $invalid++;

                    continue;
                }
                $seen[$key] = true;
                $description = $this->targets->describe($type, $target);
                $items[] = [
                    'entity_type' => $type,
                    'entity_id' => (int) $id,
                    'title' => $description['title'],
                    'reason' => Str::limit(strip_tags((string) ($item['reason'] ?? 'Selected from verified public data.')), 300),
                    'estimated_duration_minutes' => isset($item['estimated_duration_minutes']) && is_numeric($item['estimated_duration_minutes'])
                        ? max(0, min(1440, (int) $item['estimated_duration_minutes']))
                        : null,
                    'detail_url' => $description['detail_url'],
                    'booking_url' => $description['booking_url'],
                    'rating' => $description['rating'],
                    'planned_date' => $date->toDateString(),
                ];
            }
            if ($items !== []) {
                $days[] = ['date' => $date->toDateString(), 'items' => $items];
            }
        }

        if ($invalid > 0) {
            $warnings[] = "{$invalid} AI-selected item(s) were removed because they were not valid public tourism resources.";
        }

        return [
            'trip_summary' => Str::limit(strip_tags((string) ($structured['trip_summary'] ?? 'A verified itinerary for your saved trip.')), 600),
            'days' => $days,
            'notes' => $notes,
            'warnings' => array_values(array_unique($warnings)),
        ];
    }

    /** @param array<int,string> $warnings */
    private function fallback(Trip $trip, array $warnings): array
    {
        $days = [];
        foreach ($this->deterministic->recommendations($trip)->take(self::MAX_ITEMS) as $candidate) {
            $target = $this->targets->resolve($candidate['item_type'], (int) $candidate['item_id']);
            if ($target === null) {
                continue;
            }
            $description = $this->targets->describe($candidate['item_type'], $target);
            $date = $candidate['planned_date'] ?? $trip->start_date->toDateString();
            $days[$date] ??= ['date' => $date, 'items' => []];
            $days[$date]['items'][] = [
                'entity_type' => $candidate['item_type'],
                'entity_id' => (int) $candidate['item_id'],
                'title' => $description['title'],
                'reason' => 'Selected by the deterministic Smart Trip planner using verified public data.',
                'estimated_duration_minutes' => null,
                'detail_url' => $description['detail_url'],
                'booking_url' => $description['booking_url'],
                'rating' => $description['rating'],
                'planned_date' => $date,
            ];
        }

        return [
            'trip_summary' => 'A verified itinerary based on your destinations, preferences, dates, and currently public tourism data.',
            'days' => array_values($days),
            'notes' => [],
            'warnings' => array_values(array_unique($warnings)),
            'source' => 'deterministic',
            'fallback' => true,
        ];
    }

    /** @param array<int,mixed> $values @return array<int,string> */
    private function stringList(array $values, int $limit, int $length): array
    {
        return collect($values)
            ->filter(fn ($value): bool => is_string($value) && trim($value) !== '')
            ->map(fn (string $value): string => Str::limit(strip_tags($value), $length))
            ->take($limit)
            ->values()
            ->all();
    }

    /** @return array<int,array<string,mixed>> */
    private function toolDefinitions(): array
    {
        $nullableInteger = ['type' => ['integer', 'null']];
        $nullableString = ['type' => ['string', 'null']];

        return [
            $this->tool('search_tourism', 'Search currently public destinations, heritage, museums, hotels, restaurants, guides, transportation, events, or services.', [
                'q' => ['type' => 'string'],
                'type' => ['type' => 'string'],
                'destination' => $nullableInteger,
                'date' => $nullableString,
            ], ['q', 'type', 'destination', 'date']),
            $this->tool('get_destination', 'Get one public destination and its public coordinates.', ['destination_id' => ['type' => 'integer']], ['destination_id']),
            $this->tool('get_nearby_tourism', 'Find public mapped tourism resources near supplied real coordinates.', [
                'latitude' => ['type' => 'number'],
                'longitude' => ['type' => 'number'],
                'radius_km' => ['type' => 'number'],
                'category' => ['type' => 'string'],
                'search' => ['type' => 'string'],
            ], ['latitude', 'longitude', 'radius_km', 'category', 'search']),
            $this->tool('get_events', 'Find published future events from the public event discovery module.', [
                'destination_id' => $nullableInteger,
                'start_date' => $nullableString,
                'end_date' => $nullableString,
                'search' => ['type' => 'string'],
            ], ['destination_id', 'start_date', 'end_date', 'search']),
            $this->tool('get_reviews', 'Get public rating aggregates and short public review excerpts for one service, event, or guide.', [
                'target_type' => ['type' => 'string'],
                'target_id' => ['type' => 'integer'],
            ], ['target_type', 'target_id']),
            $this->tool('get_trip_details', 'Read the authenticated tourist current trip only.', ['trip_id' => ['type' => 'integer']], ['trip_id']),
            $this->tool('check_availability', 'Check authoritative availability for a public hotel, restaurant, or transportation service. Never infer availability.', [
                'service_id' => ['type' => 'integer'],
                'check_in' => $nullableString,
                'check_out' => $nullableString,
                'guest_count' => ['type' => ['integer', 'null']],
                'start_time' => $nullableString,
                'end_time' => $nullableString,
                'pickup_at' => $nullableString,
                'dropoff_at' => $nullableString,
                'passenger_count' => ['type' => ['integer', 'null']],
            ], ['service_id', 'check_in', 'check_out', 'guest_count', 'start_time', 'end_time', 'pickup_at', 'dropoff_at', 'passenger_count']),
            $this->tool('get_guide_availability', 'Check authoritative date availability for a verified public guide.', [
                'guide_id' => ['type' => 'integer'],
                'start_date' => ['type' => 'string'],
                'end_date' => ['type' => 'string'],
            ], ['guide_id', 'start_date', 'end_date']),
        ];
    }

    /** @param array<string,array<string,mixed>> $properties @param array<int,string> $required @return array<string,mixed> */
    private function tool(string $name, string $description, array $properties, array $required): array
    {
        return [
            'type' => 'function',
            'name' => $name,
            'description' => $description,
            'strict' => true,
            'parameters' => [
                'type' => 'object',
                'properties' => $properties,
                'required' => $required,
                'additionalProperties' => false,
            ],
        ];
    }

    /** @return array<string,mixed> */
    private function outputSchema(): array
    {
        return [
            'type' => 'object',
            'additionalProperties' => false,
            'properties' => [
                'trip_summary' => ['type' => 'string'],
                'days' => ['type' => 'array', 'items' => [
                    'type' => 'object',
                    'additionalProperties' => false,
                    'properties' => [
                        'date' => ['type' => 'string'],
                        'items' => ['type' => 'array', 'items' => [
                            'type' => 'object',
                            'additionalProperties' => false,
                            'properties' => [
                                'entity_type' => ['type' => 'string'],
                                'entity_id' => ['type' => 'integer'],
                                'reason' => ['type' => 'string'],
                                'estimated_duration_minutes' => ['type' => ['integer', 'null']],
                            ],
                            'required' => ['entity_type', 'entity_id', 'reason', 'estimated_duration_minutes'],
                        ]],
                    ],
                    'required' => ['date', 'items'],
                ]],
                'notes' => ['type' => 'array', 'items' => ['type' => 'string']],
                'warnings' => ['type' => 'array', 'items' => ['type' => 'string']],
            ],
            'required' => ['trip_summary', 'days', 'notes', 'warnings'],
        ];
    }

    /** @param array<string,mixed> $arguments */
    private function toolSearch(array $arguments): array
    {
        $request = Request::create('/search', 'GET', [
            'q' => Str::limit((string) ($arguments['q'] ?? ''), 120, ''),
            'type' => (string) ($arguments['type'] ?? ''),
            'destination' => $arguments['destination'] ?? null,
            'date' => $arguments['date'] ?? null,
        ]);
        $results = $this->search->search($request)->getCollection()->take(20)->map(fn (array $result): array => [
            'entity_type' => $result['trip_item_type'],
            'entity_id' => $result['trip_item_id'],
            'title' => $result['title'],
            'summary' => Str::limit((string) ($result['summary'] ?? ''), 240),
            'destination' => $result['destination'],
            'rating' => $result['rating'],
            'detail_url' => $result['url'],
            'map_url' => $result['map_url'],
        ])->filter(fn (array $result): bool => $result['entity_type'] !== null && $result['entity_id'] !== null)->values()->all();

        return ['results' => $results];
    }

    /** @param array<string,mixed> $arguments */
    private function toolDestination(array $arguments): array
    {
        $destination = Destination::query()->whereKey((int) ($arguments['destination_id'] ?? 0))->first();
        if (! $destination) {
            return ['error' => 'Destination not found.'];
        }

        return ['destination' => [
            'id' => (int) $destination->destination_id,
            'name' => $destination->name,
            'location' => $destination->location,
            'description' => Str::limit((string) $destination->description, 500),
            'latitude' => $destination->latitude,
            'longitude' => $destination->longitude,
        ]];
    }

    /** @param array<string,mixed> $arguments */
    private function toolNearby(array $arguments): array
    {
        $latitude = (float) ($arguments['latitude'] ?? 0);
        $longitude = (float) ($arguments['longitude'] ?? 0);
        $radius = max(1, min(100, (float) ($arguments['radius_km'] ?? 10)));
        if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
            return ['error' => 'Coordinates are outside valid geographic ranges.'];
        }
        $category = (string) ($arguments['category'] ?? '');
        if ($category !== '' && ! in_array($category, ['destinations', 'heritage_sites', 'museums', 'services', 'hotels', 'restaurants', 'transportation', 'events'], true)) {
            return ['error' => 'Map category is invalid.'];
        }

        $request = Request::create('/map/data', 'GET', [
            'category' => $category,
            'q' => Str::limit((string) ($arguments['search'] ?? ''), 120, ''),
        ]);
        $response = app(PublicMapDataController::class)($request);
        $markers = $response->getData(true)['data'] ?? [];
        $nearby = collect($markers)->map(function (array $marker) use ($latitude, $longitude): array {
            $marker['distance_km'] = round($this->distanceKm($latitude, $longitude, (float) $marker['latitude'], (float) $marker['longitude']), 2);

            return $marker;
        })->filter(fn (array $marker): bool => $marker['distance_km'] <= $radius)->sortBy('distance_km')->take(30)->values()->all();

        return ['results' => $nearby];
    }

    /** @param array<string,mixed> $arguments */
    private function toolEvents(array $arguments): array
    {
        $request = Request::create('/search', 'GET', [
            'type' => 'events',
            'q' => Str::limit((string) ($arguments['search'] ?? ''), 120, ''),
            'destination' => $arguments['destination_id'] ?? null,
            'date' => $arguments['start_date'] ?? null,
        ]);

        return ['results' => $this->search->search($request)->getCollection()->take(20)->map(fn (array $result): array => [
            'entity_type' => 'event',
            'entity_id' => $result['trip_item_id'],
            'title' => $result['title'],
            'summary' => Str::limit((string) ($result['summary'] ?? ''), 240),
            'detail_url' => $result['url'],
            'rating' => $result['rating'],
        ])->filter(fn (array $result): bool => $result['entity_id'] !== null)->values()->all()];
    }

    /** @param array<string,mixed> $arguments */
    private function toolReviews(array $arguments): array
    {
        $type = (string) ($arguments['target_type'] ?? '');
        $id = (int) ($arguments['target_id'] ?? 0);
        $column = match ($type) {
            'service', 'event' => 'service_id',
            'guide' => 'guide_id',
            default => null,
        };
        if ($column === null || $id < 1) {
            return ['error' => 'Review target is invalid.'];
        }

        if ($type === 'service' && ! $this->publicService($id)) {
            return ['error' => 'Service is not public.'];
        }
        if ($type === 'event' && ! CulturalEvent::query()->whereKey($id)->where('status', 'published')->whereDate('event_date', '>=', today())->whereHas('serviceProvider', fn ($query) => $query->publiclyOperational())->exists()) {
            return ['error' => 'Event is not public.'];
        }
        if ($type === 'guide' && ! TourGuide::query()->whereKey($id)->where('verification_status', 'verified')->where('admin_approval_status', 'approved')->whereHas('user', fn ($query) => $query->where('is_active', true))->exists()) {
            return ['error' => 'Guide is not public.'];
        }

        $targetId = $type === 'event' ? CulturalEvent::query()->whereKey($id)->value('service_id') : $id;
        if ($targetId === null) {
            return ['average_rating' => null, 'review_count' => 0, 'reviews' => []];
        }
        $reviewQuery = Review::query()->whereHas('booking', fn ($query) => $query->where($column, $targetId));
        $reviews = (clone $reviewQuery)->with('tourist')->latest('review_date')->limit(5)->get();

        return [
            'average_rating' => (clone $reviewQuery)->avg('rating'),
            'review_count' => (clone $reviewQuery)->count(),
            'reviews' => $reviews->map(fn (Review $review): array => [
                'first_name' => Str::of((string) $review->tourist?->full_name)->before(' ')->value(),
                'rating' => (int) $review->rating,
                'comment' => Str::limit((string) $review->comment, 240),
                'date' => $review->review_date?->toDateString(),
            ])->all(),
        ];
    }

    /** @param array<string,mixed> $arguments */
    private function toolTripDetails(Trip $trip, array $arguments): array
    {
        if (! auth()->check() || ! $trip->isOwnedBy(auth()->user()) || (int) ($arguments['trip_id'] ?? 0) !== (int) $trip->trip_id) {
            return ['error' => 'That trip is not available to this request.'];
        }

        $trip->loadMissing('destinations', 'items');

        return [
            'trip' => [
                'title' => $trip->title,
                'start_date' => $trip->start_date->toDateString(),
                'end_date' => $trip->end_date->toDateString(),
                'destinations' => $trip->destinations->pluck('name')->values()->all(),
                'item_count' => $trip->items->count(),
            ],
        ];
    }

    /** @param array<string,mixed> $arguments */
    private function toolAvailability(array $arguments): array
    {
        $service = TourismService::query()->with(['serviceProvider', 'hotelRoomType'])->whereKey((int) ($arguments['service_id'] ?? 0))->first();
        if (! $service || ! $service->serviceProvider?->isOperational()) {
            return ['available' => false, 'reason' => 'Service is not public.'];
        }

        try {
            return match ($service->serviceProvider->provider_type) {
                'hotel' => $this->availabilityResult($this->hotelAvailability->findAvailableRooms($service, (string) ($arguments['check_in'] ?? ''), (string) ($arguments['check_out'] ?? ''), max(1, (int) ($arguments['guest_count'] ?? 0)))->count(), 'rooms'),
                'restaurant' => $this->availabilityResult($this->restaurantAvailability->findAvailableTables($service, (string) ($arguments['check_in'] ?? ''), (string) ($arguments['start_time'] ?? ''), (string) ($arguments['end_time'] ?? ''), max(1, (int) ($arguments['guest_count'] ?? 0)))->count(), 'tables'),
                'transportation_car_rental' => $this->availabilityResult($this->transportationAvailability->findAvailableVehicles($service, (string) ($arguments['pickup_at'] ?? ''), (string) ($arguments['dropoff_at'] ?? ''), max(1, (int) ($arguments['passenger_count'] ?? 0)))->count(), 'vehicles'),
                default => ['available' => false, 'reason' => 'This service has no supported availability engine.'],
            };
        } catch (\Throwable $exception) {
            return ['available' => false, 'reason' => Str::limit($exception->getMessage(), 200)];
        }
    }

    /** @param array<string,mixed> $arguments */
    private function toolGuideAvailability(array $arguments): array
    {
        $guide = TourGuide::query()->whereKey((int) ($arguments['guide_id'] ?? 0))->where('verification_status', 'verified')->where('admin_approval_status', 'approved')->whereHas('user', fn ($query) => $query->where('is_active', true))->first();
        if (! $guide) {
            return ['available' => false, 'reason' => 'Guide is not public.'];
        }

        try {
            return ['available' => $this->guideAvailability->isGuideAvailable($guide, (string) $arguments['start_date'], (string) $arguments['end_date'])];
        } catch (\Throwable $exception) {
            return ['available' => false, 'reason' => Str::limit($exception->getMessage(), 200)];
        }
    }

    private function publicService(int $id): bool
    {
        return TourismService::query()->whereKey($id)->whereHas('serviceProvider', fn ($query) => $query->publiclyOperational())->exists();
    }

    /** @return array{available:bool,count:int,inventory:string} */
    private function availabilityResult(int $count, string $inventory): array
    {
        return ['available' => $count > 0, 'count' => $count, 'inventory' => $inventory];
    }

    private function extractStructuredOutput(array $body): ?array
    {
        $text = $body['output_text'] ?? null;
        if (! is_string($text)) {
            foreach ($body['output'] ?? [] as $output) {
                foreach ($output['content'] ?? [] as $content) {
                    if (($content['type'] ?? null) === 'output_text' && is_string($content['text'] ?? null)) {
                        $text = $content['text'];
                        break 2;
                    }
                }
            }
        }

        if (! is_string($text)) {
            return null;
        }

        $decoded = json_decode($text, true);

        return is_array($decoded) ? $decoded : null;
    }

    private function distanceKm(float $latitudeOne, float $longitudeOne, float $latitudeTwo, float $longitudeTwo): float
    {
        $latitudeDifference = deg2rad($latitudeTwo - $latitudeOne);
        $longitudeDifference = deg2rad($longitudeTwo - $longitudeOne);
        $a = sin($latitudeDifference / 2) ** 2
            + cos(deg2rad($latitudeOne)) * cos(deg2rad($latitudeTwo)) * sin($longitudeDifference / 2) ** 2;

        return 6371 * 2 * asin(min(1, sqrt($a)));
    }
}
