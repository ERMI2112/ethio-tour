<?php

namespace App\Models\Concerns;

use Illuminate\Validation\ValidationException;

trait HasCoordinates
{
    protected static function bootHasCoordinates(): void
    {
        static::saving(function ($model): void {
            $latitude = $model->getAttribute('latitude');
            $longitude = $model->getAttribute('longitude');

            if (($latitude === null) !== ($longitude === null)) {
                throw ValidationException::withMessages(['latitude' => 'Latitude and longitude must be provided together.']);
            }

            if ($latitude !== null && (! is_numeric($latitude) || (float) $latitude < -90 || (float) $latitude > 90)) {
                throw ValidationException::withMessages(['latitude' => 'Latitude must be between -90 and 90.']);
            }

            if ($longitude !== null && (! is_numeric($longitude) || (float) $longitude < -180 || (float) $longitude > 180)) {
                throw ValidationException::withMessages(['longitude' => 'Longitude must be between -180 and 180.']);
            }
        });
    }
}
