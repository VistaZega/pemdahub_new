<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExtracurricularActivity extends Model
{
    use HasFactory;

    protected $table = 'extracurricular_activities';

    protected $fillable = [
        'extracurricular_id',
        'title',
        'activity_date',
        'location',
        'description',
        'photos',
        'created_by',
    ];

    protected $casts = [
        'activity_date' => 'date',
        'photos' => 'array',
    ];

    public function extracurricular()
    {
        return $this->belongsTo(Extracurricular::class, 'extracurricular_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
