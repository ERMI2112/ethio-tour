<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class RestaurantTable extends Model
{
    public const STATUSES = ['active', 'inactive'];

    protected $primaryKey = 'table_id';

    protected $attributes = ['status' => 'active'];

    protected $fillable = ['provider_id', 'table_number', 'capacity', 'status'];

    protected static function booted(): void
    {
        static::saving(function (self $table): void {
            $table->table_number = trim((string) $table->table_number);

            if ($table->table_number === '') {
                throw ValidationException::withMessages(['table_number' => 'Table number must not be empty.']);
            }

            if ((int) $table->capacity < 1) {
                throw ValidationException::withMessages(['capacity' => 'Table capacity must be greater than zero.']);
            }

            if (! in_array($table->status, self::STATUSES, true)) {
                throw ValidationException::withMessages(['status' => 'Table status must be active or inactive.']);
            }
        });
    }

    public function serviceProvider()
    {
        return $this->belongsTo(ServiceProvider::class, 'provider_id', 'provider_id');
    }

    public function restaurantReservations()
    {
        return $this->hasMany(RestaurantReservation::class, 'table_id', 'table_id');
    }
}
