<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $primaryKey = 'category_id';

    protected $fillable = ['category_name'];

    public function tourismServices()
    {
        return $this->hasMany(TourismService::class, 'category_id', 'category_id');
    }
}
