<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $primaryKey = 'notification_id';

    protected $fillable = ['user_id', 'title', 'message', 'channel', 'sent_date', 'read_status'];

    protected function casts(): array
    {
        return ['sent_date' => 'datetime', 'read_status' => 'boolean'];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
