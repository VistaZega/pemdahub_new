<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Major extends Model
{
    use HasFactory;

    // table does not have timestamps columns
    public $timestamps = false;

    protected $fillable = [
        'school_id',
        'major_code',
        'major_name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }

    public function getCodeAttribute(): ?string
    {
        return $this->major_code;
    }

    public function getNameAttribute(): ?string
    {
        return $this->major_name;
    }
}
