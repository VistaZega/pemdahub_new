<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LmsMaterialNote extends Model
{
    use HasFactory;

    protected $table = 'lms_material_notes';

    protected $fillable = [
        'student_id',
        'material_id',
        'notes',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function material()
    {
        return $this->belongsTo(LmsMaterial::class, 'material_id');
    }
}
