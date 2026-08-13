<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProviderSubscription extends Model
{
    protected $primaryKey = 'provider_subscription_id';

    protected $fillable = ['provider_id', 'plan_id', 'start_date', 'end_date', 'status'];

    protected function casts(): array
    {
        return ['start_date' => 'date', 'end_date' => 'date'];
    }

    public function serviceProvider()
    {
        return $this->belongsTo(ServiceProvider::class, 'provider_id', 'provider_id');
    }

    public function subscriptionPlan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id', 'plan_id');
    }
}
