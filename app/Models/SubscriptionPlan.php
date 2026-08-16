<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    protected $primaryKey = 'plan_id';

    protected $fillable = ['plan', 'price', 'commission_rate', 'duration', 'active'];

    protected $attributes = ['active' => true];

    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'commission_rate' => 'decimal:2', 'active' => 'boolean'];
    }

    public function providerSubscriptions()
    {
        return $this->hasMany(ProviderSubscription::class, 'plan_id', 'plan_id');
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function hasHistoricalSubscriptions(): bool
    {
        return $this->providerSubscriptions()->exists();
    }
}
