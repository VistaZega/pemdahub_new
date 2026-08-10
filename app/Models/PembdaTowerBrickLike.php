<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembdaTowerBrickLike extends Model
{
    use HasFactory;

    protected $fillable = [
        'brick_id',
        'user_id',
    ];

    public function brick()
    {
        return $this->belongsTo(PembdaTowerBrick::class, 'brick_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
