<?php

namespace App\Models;

use App\Models\Concerns\HasCoordinates;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Destination extends Model
{
    use HasCoordinates;

    protected $primaryKey = 'destination_id';

    public const CATEGORIES = [
        'unesco_heritage' => 'UNESCO World Heritage Sites',
        'national_park' => 'National Parks & Biospheres',
        'eco_resort' => 'Eco-Resorts & Highland Lodges',
        'crater_lake' => 'Crater Lakes & Waterfalls',
        'historical_palace' => 'Imperial Palaces & Memorials',
        'religious_sanctuary' => 'Ancient Monasteries & Churches',
        'cultural_landscape' => 'Cultural Landscapes & Villages',
        'mountain_highlands' => 'Highland Treks & Escarpments',
    ];

    public const REGIONS = [
        'Addis Ababa' => 'Central Capital Hub',
        'Amhara' => 'Northern Historic Circuit',
        'Oromia' => 'Lakes & Highland Forests',
        'SNNP' => 'Southern Wilderness & Canyons',
        'Sidama' => 'Rift Valley & Lake Hawassa',
        'Afar' => 'Danakil Volcanic Triangle',
        'Harari' => 'Eastern Walled Heritage',
        'Tigray' => 'Ancient Aksumite Kingdoms',
        'Gambella' => 'Western Savannas & Migration',
        'Southwest Ethiopia' => 'Wild Coffee Biospheres',
    ];

    protected $fillable = [
        'officer_id',
        'name',
        'slug',
        'location',
        'region',
        'category',
        'is_featured',
        'amenities',
        'description',
        'tagline',
        'hero_image',
        'gallery_images',
        'latitude',
        'longitude',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'is_featured' => 'boolean',
            'amenities' => 'array',
            'gallery_images' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Destination $dest) {
            $baseSlug = ! empty($dest->slug) ? $dest->slug : Str::slug($dest->name);
            $slug = $baseSlug;
            $i = 2;
            while (static::where('slug', $slug)->exists()) {
                $slug = $baseSlug.'-'.$i++;
            }
            $dest->slug = $slug;
        });
    }

    public function heroImageUrl(): string
    {
        if (! empty($this->hero_image)) {
            if (str_starts_with($this->hero_image, 'http://') || str_starts_with($this->hero_image, 'https://')) {
                return $this->hero_image;
            }
            if (str_starts_with($this->hero_image, '/images/') || str_starts_with($this->hero_image, 'images/')) {
                return asset(ltrim($this->hero_image, '/'));
            }
            if (Storage::disk('public')->exists($this->hero_image)) {
                return asset('storage/'.ltrim($this->hero_image, '/'));
            }
        }

        // Slug based file lookup fallback
        $mediaKey = $this->slug ?: Str::slug($this->name);
        $localPath = 'images/destinations/'.$mediaKey.'-hero.jpg';
        if (file_exists(public_path($localPath))) {
            return asset($localPath);
        }

        return asset('images/destinations/gondar-hero.jpg');
    }

    /**
     * Return only gallery records whose image asset can safely be displayed.
     * Gallery metadata remains attributable instead of storing bare paths.
     *
     * @return array<int, array<string, mixed>>
     */
    public function galleryImages(): array
    {
        $images = collect($this->gallery_images ?? [])
            ->filter(fn ($image) => is_array($image) && ! empty($image['path']))
            ->map(function (array $image): ?array {
                $url = $this->galleryImageUrl($image['path']);

                if ($url === null) {
                    return null;
                }

                return array_merge($image, ['url' => $url]);
            })
            ->filter()
            ->sortByDesc(fn (array $image) => (bool) ($image['is_primary'] ?? false))
            ->values();

        if ($images->isNotEmpty()) {
            return $images->all();
        }

        $heroUrl = ! empty($this->hero_image) ? $this->galleryImageUrl($this->hero_image) : null;

        return $heroUrl ? [[
            'path' => $this->hero_image,
            'url' => $heroUrl,
            'alt' => $this->name,
            'is_primary' => true,
        ]] : [];
    }

    private function galleryImageUrl(string $path): ?string
    {
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, '/images/') || str_starts_with($path, 'images/')) {
            return file_exists(public_path(ltrim($path, '/'))) ? asset(ltrim($path, '/')) : null;
        }

        if (Storage::disk('public')->exists($path)) {
            return asset('storage/'.ltrim($path, '/'));
        }

        return null;
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? ($this->category ? ucwords(str_replace('_', ' ', $this->category)) : 'Iconic Destination');
    }

    public function regionCircuitLabel(): string
    {
        return self::REGIONS[$this->region] ?? ($this->region ?: 'Ethiopia');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function officer(): BelongsTo
    {
        return $this->belongsTo(TourismBureauOfficer::class, 'officer_id', 'officer_id');
    }

    public function attractions(): HasMany
    {
        return $this->hasMany(Attraction::class, 'destination_id', 'destination_id');
    }

    public function heritageSites(): HasMany
    {
        return $this->hasMany(HeritageSite::class, 'destination_id', 'destination_id');
    }

    public function culturalEvents(): HasMany
    {
        return $this->hasMany(CulturalEvent::class, 'destination_id', 'destination_id');
    }

    public function tourismServices(): HasMany
    {
        return $this->hasMany(TourismService::class, 'destination_id', 'destination_id');
    }

    public function trips(): BelongsToMany
    {
        return $this->belongsToMany(Trip::class, 'trip_destinations', 'destination_id', 'trip_id');
    }
}
