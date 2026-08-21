<?php

namespace App\Services;

use App\Models\Administrator;
use App\Models\ServiceProvider;
use App\Models\TourGuide;
use App\Models\TourismBureauOfficer;
use App\Models\Tourist;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RoleProfileProvisioner
{
    public function __construct(private readonly NotificationService $notifications) {}

    public function registerPublicUser(array $attributes): User
    {
        return DB::transaction(function () use ($attributes) {
            $role = $attributes['account_type'];
            $user = User::create([
                'email' => $attributes['email'],
                'password' => Hash::make($attributes['password']),
                'role' => $role,
                'is_active' => true,
            ]);

            match ($role) {
                'tourist' => Tourist::create([
                    'user_id' => $user->user_id,
                    'full_name' => $attributes['full_name'],
                    'nationality' => $attributes['nationality'],
                ]),
                'tour_guide' => TourGuide::create([
                    'user_id' => $user->user_id,
                    'license_number' => $attributes['license_number'],
                    'expertise' => $attributes['expertise'],
                    'availability_status' => 'unavailable',
                ]),
                'service_provider' => ServiceProvider::create([
                    'user_id' => $user->user_id,
                    'business_name' => $attributes['business_name'],
                    'provider_type' => $attributes['provider_type'],
                    'status' => 'pending',
                    'verification_status' => 'pending',
                ]),
            };

            if ($role === 'tour_guide') {
                $this->notifications->createForAdministrators('guide_registration', 'New tour guide application', 'A new tour guide application is ready for Tourism Bureau verification.');
            } elseif ($role === 'service_provider') {
                $this->notifications->createForAdministrators('provider_registration', 'New provider application', 'A new tourism provider application is ready for Bureau verification.');
            }

            return $user;
        });
    }

    public function provisionBureauOfficer(array $attributes): User
    {
        return $this->provisionControlledUser($attributes, 'tourism_bureau_officer', TourismBureauOfficer::class);
    }

    public function provisionAdministrator(array $attributes): User
    {
        return $this->provisionControlledUser($attributes, 'administrator', Administrator::class);
    }

    private function provisionControlledUser(array $attributes, string $role, string $profileClass): User
    {
        return DB::transaction(function () use ($attributes, $role, $profileClass) {
            $user = User::create([
                'email' => $attributes['email'],
                'password' => Hash::make($attributes['password']),
                'role' => $role,
                'is_active' => $attributes['is_active'] ?? true,
            ]);

            $profileClass::create(['user_id' => $user->user_id]);

            return $user;
        });
    }
}
