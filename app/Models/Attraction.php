<?php

namespace App\Models;

use App\Models\Concerns\HasCoordinates;
use Illuminate\Database\Eloquent\Model;

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

    public function primaryImageUrl(): string
    {
        $img = $this->primaryImage();
        if (! $img || empty($img['path'])) {
            return asset('images/destinations/gondar-castles.jpg');
        }

        $path = $img['path'];
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, '/images/') || str_starts_with($path, 'images/')) {
            return asset(ltrim($path, '/'));
        }

        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
            return asset('storage/' . ltrim($path, '/'));
        }

        return asset('images/destinations/gondar-castles.jpg');
    }

    protected static function booted(): void
    {
        static::creating(function (Attraction $attraction) {
            if (empty($attraction->slug)) {
                $attraction->slug = \Illuminate\Support\Str::slug($attraction->name) . '-' . \Illuminate\Support\Str::random(5);
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
