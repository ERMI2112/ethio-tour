<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $primaryKey = 'user_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function tourist()
    {
        return $this->hasOne(Tourist::class, 'user_id', 'user_id');
    }

    public function tourGuide()
    {
        return $this->hasOne(TourGuide::class, 'user_id', 'user_id');
    }

    public function serviceProvider()
    {
        return $this->hasOne(ServiceProvider::class, 'user_id', 'user_id');
    }

    public function tourismBureauOfficer()
    {
        return $this->hasOne(TourismBureauOfficer::class, 'user_id', 'user_id');
    }

    public function administrator()
    {
        return $this->hasOne(Administrator::class, 'user_id', 'user_id');
    }

    public function reportsGenerated()
    {
        return $this->hasMany(Report::class, 'generated_by_user_id', 'user_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id', 'user_id');
    }
}
