<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SimProject extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'board_type',
        'circuit_json',
        'code_ino',
        'user_id',
        'user_type',
        'is_template',
        'is_public',
        'share_token',
    ];

    protected $casts = [
        'is_template' => 'boolean',
        'is_public' => 'boolean',
        'circuit_json' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
