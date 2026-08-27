<?php

namespace App\Models;

use App\Models\Concerns\HasCoordinates;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Attraction extends Model
{
    use HasCoordinates;

    protected $primaryKey = 'attraction_id';

    public const CATEGORIES = [
        'heritage_site',
        'church',
        'museum',
        'natural_site',
        'market',
        'palace',
        'monument',
    ];

    protected $fillable = [
        'destination_id',
        'name',
        'slug',
        'description',
        'category',
        'images',
        'location_address',
        'latitude',
        'longitude',
        'opening_hours',
        'entry_fee',
        'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'images' => 'array',
            'entry_fee' => 'decimal:2',
            'is_featured' => 'boolean',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function destination()
    {
        return $this->belongsTo(Destination::class, 'destination_id', 'destination_id');
    }

    public function primaryImage(): ?array
    {
        if (empty($this->images)) {
            return null;
        }

        foreach ($this->images as $image) {
            if (! empty($image['is_primary'])) {
                return $image;
            }
        }

        return $this->images[0] ?? null;
    }

    /**
     * Return gallery records with a safe public URL and primary image first.
     *
     * @return array<int, array<string, mixed>>
     */
    public function galleryImages(): array
    {
        return collect($this->images ?? [])
            ->filter(fn ($image) => is_array($image) && ! empty($image['path']))
            ->map(function (array $image): ?array {
                $url = $this->imageUrl($image['path']);

                if ($url === null) {
                    return null;
                }

                return array_merge($image, ['url' => $url]);
            })
            ->filter()
            ->sortByDesc(fn (array $image) => (bool) ($image['is_primary'] ?? false))
            ->values()
            ->all();
    }

    public function primaryImageUrl(): ?string
    {
        $img = $this->primaryImage();
        if (! $img || empty($img['path'])) {
            return null;
        }

        return $this->imageUrl($img['path']);
    }

    private function imageUrl(string $path): ?string
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

    protected static function booted(): void
    {
        static::creating(function (Attraction $attraction) {
            if (empty($attraction->slug)) {
                $attraction->slug = Str::slug($attraction->name).'-'.Str::random(5);
            }
        });
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function googleMapsUrl(): ?string
    {
        if (! $this->hasCoordinates()) {
            return null;
        }

        return 'https://www.google.com/maps/search/?api=1&query='.$this->latitude.','.$this->longitude;
    }

    public function categoryLabel(): string
    {
        return match ($this->category) {
            'heritage_site' => 'Heritage Site',
            'church' => 'Church & Monastery',
            'museum' => 'Museum',
            'natural_site' => 'Natural Site',
            'market' => 'Market & Culture',
            'palace' => 'Palace & Castle',
            'monument' => 'Historical Monument',
            default => ucwords(str_replace('_', ' ', $this->category)),
        };
    }
}
