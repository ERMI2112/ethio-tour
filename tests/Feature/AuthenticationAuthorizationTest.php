<?php

namespace Tests\Feature;

use App\Models\Administrator;
use App\Models\ServiceProvider;
use App\Models\TourGuide;
use App\Models\TourismBureauOfficer;
use App\Models\Tourist;
use App\Models\User;
use App\Services\RoleProfileProvisioner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class AuthenticationAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_tourist_can_register_with_a_linked_profile_and_hashed_password(): void
    {
        $response = $this->post('/register', [
            'account_type' => 'tourist', 'email' => 'tourist@example.com', 'password' => 'secure-password', 'password_confirmation' => 'secure-password', 'full_name' => 'Test Tourist', 'nationality' => 'Ethiopian',
        ]);

        $response->assertRedirect('/account');
        $user = User::where('email', 'tourist@example.com')->firstOrFail();
        $this->assertSame('tourist', $user->role);
        $this->assertTrue(Hash::check('secure-password', $user->password));
        $this->assertTrue($user->tourist->user->is($user));
        $this->assertAuthenticatedAs($user);
    }

    public function test_a_guide_and_provider_can_register_with_their_approved_profiles(): void
    {
        $this->post('/register', ['account_type' => 'tour_guide', 'email' => 'guide@example.com', 'password' => 'secure-password', 'password_confirmation' => 'secure-password', 'license_number' => 'GUIDE-AUTH-01', 'expertise' => 'History'])->assertRedirect('/account');
        $guide = User::where('email', 'guide@example.com')->firstOrFail();
        $this->assertSame('tour_guide', $guide->role);
        $this->assertInstanceOf(TourGuide::class, $guide->tourGuide);

        $this->post('/logout');
        $this->post('/register', ['account_type' => 'service_provider', 'email' => 'provider@example.com', 'password' => 'secure-password', 'password_confirmation' => 'secure-password', 'business_name' => 'Test Hotel', 'provider_type' => 'hotel'])->assertRedirect('/account');
        $provider = User::where('email', 'provider@example.com')->firstOrFail();
        $this->assertSame('service_provider', $provider->role);
        $this->assertSame('hotel', $provider->serviceProvider->provider_type);
    }

    public function test_public_registration_cannot_assign_privileged_or_unapproved_roles(): void
    {
        foreach (['administrator', 'tourism_bureau_officer', 'admin', 'tourism_officer', 'museum_owner'] as $role) {
            $this->post('/register', ['account_type' => $role, 'email' => $role.'@example.com', 'password' => 'secure-password', 'password_confirmation' => 'secure-password'])->assertSessionHasErrors('account_type');
        }

        $this->assertDatabaseCount('users', 0);
    }

    public function test_invalid_and_duplicate_registration_data_is_rejected(): void
    {
        $this->post('/register', ['account_type' => 'tourist', 'email' => 'invalid', 'password' => 'short', 'password_confirmation' => 'mismatch'])->assertSessionHasErrors(['email', 'password', 'full_name', 'nationality']);

        User::factory()->create(['email' => 'duplicate@example.com']);
        $this->post('/register', ['account_type' => 'tourist', 'email' => 'duplicate@example.com', 'password' => 'secure-password', 'password_confirmation' => 'secure-password', 'full_name' => 'Duplicate', 'nationality' => 'Ethiopian'])->assertSessionHasErrors('email');
    }

    public function test_valid_login_invalid_login_deactivated_login_and_logout_behave_securely(): void
    {
        $user = User::factory()->create(['email' => 'login@example.com', 'password' => 'secure-password']);

        $this->post('/login', ['email' => $user->email, 'password' => 'incorrect-password'])->assertSessionHasErrors('email');
        $this->post('/login', ['email' => $user->email, 'password' => 'secure-password'])->assertRedirect('/account');
        $this->assertAuthenticatedAs($user);
        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();

        $user->update(['is_active' => false]);
        $this->post('/login', ['email' => $user->email, 'password' => 'secure-password'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_password_reset_and_password_confirmation_use_laravel_security_mechanisms(): void
    {
        $user = User::factory()->create(['email' => 'reset@example.com', 'password' => 'old-password']);

        $this->post('/forgot-password', ['email' => $user->email])->assertSessionHas('status');
        $token = Password::broker()->createToken($user);
        $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-secure-password',
            'password_confirmation' => 'new-secure-password',
        ])->assertRedirect('/login');
        $this->assertTrue(Hash::check('new-secure-password', $user->fresh()->password));

        $this->actingAs($user->fresh())->post('/confirm-password', ['password' => 'incorrect'])->assertSessionHasErrors('password');
        $this->actingAs($user->fresh())->post('/confirm-password', ['password' => 'new-secure-password'])->assertRedirect('/account');
    }

    public function test_bootstrap_authentication_pages_render(): void
    {
        $this->get('/login')->assertOk()->assertSee('form-control')->assertSee('Log in');
        $this->get('/register')->assertOk()->assertSee('form-select')->assertSee('Create an account');
        $this->get('/forgot-password')->assertOk()->assertSee('Reset your password');
    }

    public function test_controlled_provisioning_creates_bureau_and_administrator_profiles(): void
    {
        $provisioner = app(RoleProfileProvisioner::class);
        $bureauUser = $provisioner->provisionBureauOfficer(['email' => 'bureau@example.com', 'password' => 'secure-password']);
        $adminUser = $provisioner->provisionAdministrator(['email' => 'admin@example.com', 'password' => 'secure-password']);

        $this->assertSame('tourism_bureau_officer', $bureauUser->role);
        $this->assertTrue($bureauUser->tourismBureauOfficer->user->is($bureauUser));
        $this->assertSame('administrator', $adminUser->role);
        $this->assertTrue($adminUser->administrator->user->is($adminUser));
    }

    public function test_role_middleware_blocks_unauthenticated_and_wrong_role_users(): void
    {
        $this->get('/access-check/administrator')->assertRedirect('/login');

        $tourist = $this->profiledUser('tourist');
        $this->actingAs($tourist)->get('/access-check/administrator')->assertForbidden();
        $this->actingAs($tourist)->get('/access-check/tourist')->assertOk();

        $guide = $this->profiledUser('tour_guide');
        $this->actingAs($guide)->get('/access-check/bureau')->assertForbidden();

        $provider = $this->profiledUser('service_provider');
        $this->actingAs($provider)->get('/access-check/administrator')->assertForbidden();

        $bureau = $this->profiledUser('tourism_bureau_officer');
        $this->actingAs($bureau)->get('/access-check/administrator')->assertForbidden();

        $admin = $this->profiledUser('administrator');
        $this->actingAs($admin)->get('/access-check/administrator')->assertOk();
    }

    public function test_public_input_cannot_escalate_role_or_provider_type_and_ownership_helper_distinguishes_users(): void
    {
        $this->post('/register', ['account_type' => 'service_provider', 'role' => 'administrator', 'email' => 'safe-provider@example.com', 'password' => 'secure-password', 'password_confirmation' => 'secure-password', 'business_name' => 'Safe Provider', 'provider_type' => 'hotel'])->assertRedirect('/account');
        $providerUser = User::where('email', 'safe-provider@example.com')->firstOrFail();
        $this->assertSame('service_provider', $providerUser->role);
        $this->assertNotSame('administrator', $providerUser->role);
        $this->assertNotSame('tourism_bureau_officer', $providerUser->role);

        $otherUser = $this->profiledUser('service_provider');
        $this->assertTrue($providerUser->ownsProfile($providerUser->serviceProvider));
        $this->assertFalse($providerUser->ownsProfile($otherUser->serviceProvider));
    }

    private function profiledUser(string $role): User
    {
        $user = User::factory()->create(['role' => $role]);

        match ($role) {
            'tourist' => Tourist::create(['user_id' => $user->user_id, 'full_name' => 'Tourist', 'nationality' => 'Ethiopian']),
            'tour_guide' => TourGuide::create(['user_id' => $user->user_id, 'license_number' => 'GUIDE-'.uniqid(), 'expertise' => 'History', 'availability_status' => 'available']),
            'service_provider' => ServiceProvider::create(['user_id' => $user->user_id, 'business_name' => 'Provider', 'provider_type' => 'hotel', 'status' => 'pending']),
            'tourism_bureau_officer' => TourismBureauOfficer::create(['user_id' => $user->user_id]),
            'administrator' => Administrator::create(['user_id' => $user->user_id]),
        };

        return $user;
    }
}
