<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $primaryKey = 'report_id';

    protected $fillable = ['generated_by_user_id', 'report_type', 'generated_date'];

    protected function casts(): array
    {
        return ['generated_date' => 'datetime'];
    }

    public function generatedBy()
    {
        return $this->belongsTo(User::class, 'generated_by_user_id', 'user_id');
    }
}
