<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PlayerAnalysis extends Model
{
    use HasFactory;

    protected $fillable = [
        'player_id',
        'file_name',
        'file_path',
        'file_size',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
        ];
    }

    public function player()
    {
        return $this->belongsTo(Player::class);
    }
}