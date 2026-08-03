<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentType extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'school_id',
        'type_code',
        'type_name',
        'description',
        'amount',
        'yayasan_share_amount',
        'is_recurring',
        'allow_installment',
        'is_active',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'yayasan_share_amount' => 'decimal:2',
        'is_recurring' => 'boolean',
        'allow_installment' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Relationship: PaymentType belongs to School
     */
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Relationship: PaymentType has many StudentBills
     */
    public function studentBills()
    {
        return $this->hasMany(StudentBill::class);
    }
}
