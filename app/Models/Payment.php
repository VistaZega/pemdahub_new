<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class Payment extends Model
{
    use HasFactory, LogsActivity;

    public $timestamps = true;

    protected $table = 'payments';

    protected $fillable = [
        'bill_id',
        'student_id',
        'amount_paid',
        'payment_method',
        'qris_transaction_id',
        'qris_status',
        'reference_number',
        'proof_file',
        'payment_date',
        'notes',
        'receipt_number',
        'processed_by',
        'is_verified',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'amount_paid' => 'decimal:2',
        'payment_date' => 'datetime',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
    ];

    protected const PAYMENT_METHODS = [
        'cash' => 'Tunai',
        'transfer' => 'Transfer Bank',
        'qris' => 'QRIS',
        'card' => 'Kartu Kredit',
        'check' => 'Cek',
    ];

    protected const QRIS_STATUSES = [
        'pending' => 'Menunggu',
        'success' => 'Berhasil',
        'failed' => 'Gagal',
        'expired' => 'Kadaluarsa',
    ];

    /**
     * Boot model events to auto-sync parent bill status
     */
    protected static function booted()
    {
        static::saved(function ($payment) {
            if ($payment->bill) {
                $payment->bill->recalculateStatus();
            }
        });

        static::deleted(function ($payment) {
            if ($payment->bill) {
                $payment->bill->recalculateStatus();
            }
        });
    }

    /**
     * Relationship: Payment belongs to Bill
     */
    public function bill()
    {
        return $this->belongsTo(StudentBill::class, 'bill_id');
    }

    /**
     * Relationship: Payment belongs to Student
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Relationship: Payment verified by User
     */
    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Relationship: Payment processed by User
     */
    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Get payment method label
     */
    public function getPaymentMethodLabel()
    {
        return self::PAYMENT_METHODS[$this->payment_method] ?? $this->payment_method;
    }

    /**
     * Get QRIS status label
     */
    public function getQrisStatusLabel()
    {
        return self::QRIS_STATUSES[$this->qris_status] ?? $this->qris_status;
    }

    /**
     * Check if payment is verified
     */
    public function isVerified()
    {
        return $this->is_verified === true;
    }

    /**
     * Scope: Get verified payments only
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope: Get unverified payments
     */
    public function scopeUnverified($query)
    {
        return $query->where('is_verified', false);
    }

    /**
     * Scope: Get payments in date range
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('payment_date', [$startDate, $endDate]);
    }

    /**
     * Scope: Get payments by method
     */
    public function scopeByMethod($query, $method)
    {
        return $query->where('payment_method', $method);
    }
}
