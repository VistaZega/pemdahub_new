<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolContribution extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'academic_year_id',
        'authorized_expense',
        'expense_details',
        'spp_rates',
        'notes',
    ];

    protected $casts = [
        'authorized_expense' => 'decimal:2',
        'expense_details' => 'array',
        'spp_rates' => 'array',
    ];

    /**
     * Relasi ke School
     */
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Relasi ke AcademicYear
     */
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
