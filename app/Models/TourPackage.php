<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TourPackage extends Model
{
    use HasFactory;

    protected $primaryKey = 'package_id';

    protected $fillable = [
        'guide_id',
        'destination_id',
        'title',
        'slug',
        'duration_days',
        'price',
        'max_group_size',
        'difficulty_level',
        'description',
        'itinerary',
        'included',
        'excluded',
        'cover_image',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'itinerary' => 'array',
            'included' => 'array',
            'excluded' => 'array',
            'price' => 'decimal:2',
            'duration_days' => 'integer',
            'max_group_size' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (TourPackage $package) {
            if (empty($package->slug)) {
                $package->slug = Str::slug($package->title).'-'.Str::random(5);
            }
        });
    }

    public function guide()
    {
        return $this->belongsTo(TourGuide::class, 'guide_id', 'guide_id');
    }

    public function destination()
    {
        return $this->belongsTo(Destination::class, 'destination_id', 'destination_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function coverImageUrl(): string
    {
        if (! $this->cover_image) {
            return asset('images/destinations/gondar-castles.jpg');
        }

        if (str_starts_with($this->cover_image, 'http://') || str_starts_with($this->cover_image, 'https://')) {
            return $this->cover_image;
        }

        if (str_starts_with($this->cover_image, 'images/')) {
            return asset($this->cover_image);
        }

        if (Storage::disk('public')->exists($this->cover_image)) {
            return asset('storage/'.ltrim($this->cover_image, '/'));
        }

        return asset('images/destinations/gondar-castles.jpg');
    }

    public function formattedPrice(): string
    {
        return number_format((float) $this->price, 2).' ETB';
    }

    public function itineraryList(): array
    {
        return is_array($this->itinerary) ? $this->itinerary : [];
    }

    public function includedList(): array
    {
        return is_array($this->included) ? $this->included : [];
    }

    public function excludedList(): array
    {
        return is_array($this->excluded) ? $this->excluded : [];
    }
}
