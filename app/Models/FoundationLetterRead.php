<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FoundationLetterRead extends Model
{
    use HasFactory;

    protected $fillable = [
        'foundation_letter_id',
        'school_id',
        'user_id',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function letter()
    {
        return $this->belongsTo(FoundationLetter::class, 'foundation_letter_id');
    }

    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
