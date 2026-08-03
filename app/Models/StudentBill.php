<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class StudentBill extends Model
{
    use HasFactory, LogsActivity;

    public $timestamps = true;

    protected $table = 'student_bills';

    protected $fillable = [
        'student_id',
        'payment_type_id',
        'academic_year_id',
        'semester_id',
        'month',
        'year',
        'amount',
        'yayasan_share_amount',
        'paid_amount',
        'status',
        'notes',
        'late_fee_waived',
        'waiver_reason',
        'waived_by',
        'waived_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'yayasan_share_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'month' => 'integer',
        'year' => 'integer',
        'late_fee_waived' => 'boolean',
        'waived_at' => 'datetime',
    ];

    protected $appends = ['due_date', 'late_fee', 'total_with_late_fee', 'outstanding_with_late_fee'];

    protected const STATUSES = [
        'belum_bayar' => 'Belum Dibayar',
        'cicilan' => 'Dibayar Sebagian',
        'lunas' => 'Lunas',
    ];

    /**
     * Accessor: Calculate due date automatically from month/year
     * Due date is always the 10th of the month
     */
    public function getDueDateAttribute()
    {
        if ($this->month && $this->year) {
            return \Carbon\Carbon::create($this->year, $this->month, 10);
        }
        return null;
    }

    /**
     * Relationship: Bill belongs to Student
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Relationship: Bill belongs to PaymentType
     */
    public function paymentType()
    {
        return $this->belongsTo(PaymentType::class);
    }

    /**
     * Relationship: Bill belongs to AcademicYear
     */
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Relationship: Bill belongs to Semester
     */
    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    /**
     * Relationship: Bill has many payments
     */
    public function payments()
    {
        return $this->hasMany(Payment::class, 'bill_id');
    }

    /**
     * Relationship: User who waived the late fee
     */
    public function waivedByUser()
    {
        return $this->belongsTo(User::class, 'waived_by');
    }

    /**
     * Get bill type label
     */
    public function getBillTypeLabel()
    {
        return $this->paymentType ? $this->paymentType->type_name : '-';
    }

    /**
     * Get status label
     */
    public function getStatusLabel()
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /**
     * Get total amount paid
     */
    public function getTotalPaid()
    {
        return $this->payments()->where('is_verified', true)->sum('amount_paid');
    }

    /**
     * Get remaining amount
     */
    public function getRemainingAmount()
    {
        $paid = $this->getTotalPaid();
        return $this->amount - $paid;
    }

    /**
     * Accessor: Get status color for UI
     */
    public function getStatusColorAttribute()
    {
        if ($this->status === 'lunas') {
            return 'emerald';
        }
        
        if ($this->isOverdue()) {
            return 'red';
        }

        if ($this->status === 'cicilan') {
            return 'amber';
        }

        return 'yellow';
    }

    /**
     * Check if bill is overdue
     */
    public function isOverdue()
    {
        if ($this->status === 'lunas') {
            return false;
        }

        return $this->due_date && now()->isAfter($this->due_date);
    }

    /**
     * Calculate late payment fee
     */
    public function getLateFeeAttribute()
    {
        // Skip if late fee is waived
        if ($this->late_fee_waived) {
            return 0;
        }

        // Skip if already paid or late fee disabled
        if ($this->status === 'lunas' || !Setting::getValue('late_fee_enabled', true)) {
            return 0;
        }

        // Skip if no due date
        if (!$this->due_date) {
            return 0;
        }

        $gracePeriod = Setting::getValue('late_fee_grace_period', 3);
        $feeAmount = Setting::getValue('late_fee_amount', 10000);
        $feeType = Setting::getValue('late_fee_type', 'fixed');

        // Calculate days overdue (negative means not yet due)
        $daysOverdue = $this->due_date->diffInDays(now(), false);

        // No fee if within grace period or not yet due
        if ($daysOverdue <= $gracePeriod) {
            return 0;
        }

        // Calculate fee based on type
        if ($feeType === 'percentage') {
            $outstanding = $this->amount - $this->paid_amount;
            return ($outstanding * $feeAmount) / 100;
        }

        return $feeAmount;
    }

    /**
     * Get total amount including late fee
     */
    public function getTotalWithLateFeeAttribute()
    {
        return $this->amount + $this->late_fee;
    }

    /**
     * Get outstanding amount including late fee
     */
    public function getOutstandingWithLateFeeAttribute()
    {
        $outstanding = $this->amount - $this->paid_amount;
        return $outstanding + $this->late_fee;
    }

    /**
     * Scope: Get unpaid bills
     */
    public function scopeUnpaid($query)
    {
        return $query->whereIn('status', ['belum_bayar', 'cicilan']);
    }

    /**
     * Scope: Get paid bills
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'lunas');
    }

    /**
     * Scope: Get overdue bills
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', '!=', 'lunas')
            ->where(function ($q) {
                $now = now();
                // Simple version using year/month/day since we define due_date as 10th
                $q->where('year', '<', $now->year)
                  ->orWhere(function ($q2) use ($now) {
                      $q2->where('year', '=', $now->year)
                         ->where('month', '<', $now->month);
                  })
                  ->orWhere(function ($q2) use ($now) {
                      $q2->where('year', '=', $now->year)
                         ->where('month', '=', $now->month)
                         ->whereRaw('10 < ?', [$now->day]);
                  });
            });
    }
}
