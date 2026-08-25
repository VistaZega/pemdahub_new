<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class P5ProjectTarget extends Model
{
    use HasFactory;

    protected $table = 'p5_project_targets';

    protected $fillable = [
        'p5_project_id',
        'dimension',
        'sub_element',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(P5Project::class, 'p5_project_id');
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(P5Assessment::class, 'p5_project_target_id');
    }
}
