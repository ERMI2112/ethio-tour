<?php

namespace Tests\Feature;

use App\Models\CulturalEvent;
use App\Models\TourismService;
use App\Models\User;
use App\Support\ServiceImage;
use Database\Seeders\UatDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression guard for the workspace sidebar active-state: the highlighted
 * navigation item must always match the page being viewed (reported: the
 * Dashboard item stayed highlighted on the My Profile page).
 */
class CompetitionPolishTest extends TestCase
{
    use RefreshDatabase;

    public function test_tourist_sidebar_marks_profile_active_on_profile_page_only(): void
    {
        $this->seed(UatDemoSeeder::class);
        $user = User::where('email', 'tourist@test.com')->firstOrFail();

        $profileHtml = $this->actingAs($user)->get(route('tourist.profile'))->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/nav-link active[^>]*href="[^"]*tourist\/profile"/s', $profileHtml);
        $this->assertDoesNotMatchRegularExpression('/nav-link active[^>]*href="[^"]*\/tourist"/', preg_replace('/tourist\/profile/', 'tourist/skip', $profileHtml));

        $dashboardHtml = $this->actingAs($user)->get(route('tourist.dashboard'))->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/nav-link active[^>]*href="[^"]*\/tourist"/', $dashboardHtml);
    }

    public function test_public_pages_have_brand_favicon_and_social_metadata(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('favicon.svg', $html);
        $this->assertStringContainsString('property="og:title"', $html);
        $this->assertStringContainsString('name="twitter:card"', $html);
        $this->assertStringContainsString('name="description"', $html);
    }

    public function test_landing_trust_strip_shows_trust_signals_not_duplicate_stats(): void
    {
        $this->seed(UatDemoSeeder::class);

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('Bureau-verified operators', $html);
        $this->assertStringContainsString('Chapa-protected payments', $html);
        $this->assertStringContainsString('Tourism Bureau oversight', $html);
        // The duplicated "Current public records" strip is gone.
        $this->assertStringNotContainsString('Current public records', $html);
    }

    public function test_branded_error_pages_render_for_404_and_403(): void
    {
        $notFound = $this->get('/definitely-not-a-real-page')->assertNotFound()->getContent();
        $this->assertStringContainsString('404', $notFound);
        $this->assertStringContainsString('Browse destinations', $notFound);

        $this->seed(UatDemoSeeder::class);
        $tourist = User::where('email', 'tourist@test.com')->firstOrFail();
        $forbidden = $this->actingAs($tourist)->get('/admin')->assertForbidden()->getContent();
        $this->assertStringContainsString('403', $forbidden);
        $this->assertStringContainsString('guarded', $forbidden);
    }

    public function test_seeded_festivals_use_realistic_ethiopian_dates(): void
    {
        $this->seed(UatDemoSeeder::class);

        $timkat = CulturalEvent::where('event_name', 'Timkat Gondar Epiphany & Cultural Festival')->firstOrFail();
        $this->assertSame('01-19', $timkat->event_date->format('m-d'), 'Timkat must fall on 19 January.');
        $this->assertTrue($timkat->event_date->isFuture(), 'Seeded Timkat must be an upcoming event.');

        $meskel = CulturalEvent::where('event_name', 'Lalibela Meskel Cultural Celebration')->firstOrFail();
        $this->assertSame('09-27', $meskel->event_date->format('m-d'), 'Meskel must fall on 27 September.');
        $this->assertTrue($meskel->event_date->isFuture(), 'Seeded Meskel must be an upcoming event.');
    }

    public function test_service_card_images_are_distinct_within_a_vertical(): void
    {
        $this->seed(UatDemoSeeder::class);

        $hotelServices = TourismService::query()
            ->whereHas('serviceProvider', fn ($query) => $query->where('provider_type', 'hotel'))
            ->get();
        $this->assertGreaterThanOrEqual(2, $hotelServices->count());

        $images = $hotelServices->map(fn ($service) => ServiceImage::for($service));
        $this->assertSame($images->count(), $images->unique()->count(), 'Hotel listings must not share identical card images.');

        foreach ($images as $image) {
            $this->assertFileExists(public_path($image));
        }
    }
}
