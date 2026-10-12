<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Auth\Authenticatable as AuthenticatableTrait;

class Player extends Model implements \Illuminate\Contracts\Auth\Authenticatable{
    use HasApiTokens, HasFactory , AuthenticatableTrait ;

    protected $fillable = [
        'name',
        'username',
        'password',
        'player_code',
        'age_group',
        'position',
        'phone',
        'photo_path',
        'subscription_start',
        'subscription_end',
        'is_active',
        'present_count',
        'absent_count',
        'coach_rating',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'subscription_start' => 'date',
            'subscription_end' => 'date',
            'is_active' => 'boolean',
            'present_count' => 'integer',
            'absent_count' => 'integer',
            'coach_rating' => 'integer',
        ];
    }

    public function trainings()
    {
        return $this->hasMany(Training::class);
    }
}