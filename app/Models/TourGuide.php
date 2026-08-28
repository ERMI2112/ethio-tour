<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Storage;

class TourGuide extends Model
{
    protected $primaryKey = 'guide_id';

    protected $attributes = [
        'verification_status' => 'pending',
        'admin_approval_status' => 'pending',
    ];

    protected $fillable = [
        'user_id',
        'full_name',
        'license_number',
        'profile_image',
        'expertise',
        'bio',
        'phone_number',
        'languages',
        'years_of_experience',
        'primary_destination_id',
        'specialties',
        'availability_status',
        'daily_rate',
        'verification_notes',
        'admin_approval_status',
        'admin_approval_notes',
        'admin_approved_at',
        'admin_approved_by',
    ];

    protected function casts(): array
    {
        return [
            'daily_rate' => 'decimal:2',
            'languages' => 'array',
            'specialties' => 'array',
            'years_of_experience' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class, 'primary_destination_id', 'destination_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'guide_id', 'guide_id');
    }

    public function verificationDocuments(): MorphMany
    {
        return $this->morphMany(VerificationDocument::class, 'documentable');
    }

    public function adminApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_approved_by', 'user_id');
    }

    public function isPubliclyApproved(): bool
    {
        return $this->verification_status === 'verified'
            && $this->admin_approval_status === 'approved'
            && $this->user?->is_active === true;
    }

    public function reviews(): HasManyThrough
    {
        return $this->hasManyThrough(
            Review::class,
            Booking::class,
            'guide_id',     // Foreign key on bookings table
            'booking_id',   // Foreign key on reviews table
            'guide_id',     // Local key on tour_guides table
            'booking_id'    // Local key on bookings table
        );
    }

    public function profileImageUrl(): string
    {
        if ($this->profile_image) {
            // Full external URL
            if (str_starts_with($this->profile_image, 'http://') || str_starts_with($this->profile_image, 'https://')) {
                return $this->profile_image;
            }

            // Stored in public/images
            if (str_starts_with($this->profile_image, '/images/') || str_starts_with($this->profile_image, 'images/')) {
                return asset(ltrim($this->profile_image, '/'));
            }

            // Stored in storage/app/public
            if (Storage::disk('public')->exists($this->profile_image)) {
                return asset('storage/'.ltrim($this->profile_image, '/'));
            }
        }

        return asset('images/guides/tour-guide.jpg');
    }

    public function tourPackages()
    {
        return $this->hasMany(TourPackage::class, 'guide_id', 'guide_id');
    }

    public function languagesList(): array
    {
        if (is_array($this->languages)) {
            return $this->languages;
        }

        if (is_string($this->languages) && ! empty($this->languages)) {
            $decoded = json_decode($this->languages, true);
            if (is_array($decoded)) {
                return $decoded;
            }

            return array_map('trim', explode(',', $this->languages));
        }

        return ['Amharic', 'English'];
    }

    public function specialtiesList(): array
    {
        if (is_array($this->specialties)) {
            return $this->specialties;
        }

        if (is_string($this->specialties) && ! empty($this->specialties)) {
            $decoded = json_decode($this->specialties, true);
            if (is_array($decoded)) {
                return $decoded;
            }

            return array_map('trim', explode(',', $this->specialties));
        }

        return ['Historical Heritage', 'Cultural Walks'];
    }
}
