<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class InventoryAsset extends Model
{
    use HasFactory;

    protected $table = 'inventory_assets';

    protected $fillable = [
        'item_number',
        'asset_code',
        'school_id',
        'category_id',
        'location_id',
        'name',
        'brand',
        'serial_number',
        'description',
        'acquisition_year',
        'acquisition_date',
        'funding_source',
        'quantity',
        'unit',
        'unit_price',
        'acquisition_price',
        'useful_life_years',
        'depreciation_method',
        'salvage_value',
        'annual_depreciation',
        'monthly_depreciation',
        'accumulated_depreciation',
        'current_book_value',
        'condition',
        'status',
        'pic_user_id',
        'pic_name',
        'photo_path',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'item_number' => 'integer',
        'acquisition_year' => 'integer',
        'acquisition_date' => 'date',
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'acquisition_price' => 'decimal:2',
        'salvage_value' => 'decimal:2',
        'useful_life_years' => 'integer',
        'annual_depreciation' => 'decimal:2',
        'monthly_depreciation' => 'decimal:2',
        'accumulated_depreciation' => 'decimal:2',
        'current_book_value' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function (InventoryAsset $asset) {
            $asset->calculateDepreciation();
        });
    }

    /**
     * Hitung Nilai Perolehan & Penyusutan secara Otomatis
     */
    public function calculateDepreciation(): void
    {
        $qty = max(1, (int) ($this->quantity ?: 1));
        $uPrice = (float) ($this->unit_price ?: 0);
        
        // Total Nilai Pengadaan / Perolehan
        if ($this->unit_price > 0 && ($this->acquisition_price <= 0 || $this->isDirty(['quantity', 'unit_price']))) {
            $this->acquisition_price = round($qty * $uPrice, 2);
        } elseif ($this->acquisition_price > 0 && $this->unit_price <= 0) {
            $this->unit_price = round($this->acquisition_price / $qty, 2);
        }

        $totalCost = (float) ($this->acquisition_price ?: 0);
        $salvage = (float) ($this->salvage_value ?: 0);
        $usefulLife = (int) ($this->useful_life_years ?: 5);

        // Jika tanpa penyusutan (misal Tanah atau metode 'none')
        if ($this->depreciation_method === 'none' || $usefulLife <= 0) {
            $this->annual_depreciation = 0;
            $this->monthly_depreciation = 0;
            $this->accumulated_depreciation = 0;
            $this->current_book_value = $totalCost;
            return;
        }

        // Metode Garis Lurus (Straight-Line)
        $depreciableBase = max(0, $totalCost - $salvage);
        $annual = $depreciableBase / $usefulLife;
        $monthly = $annual / 12;

        $this->annual_depreciation = round($annual, 2);
        $this->monthly_depreciation = round($monthly, 2);

        // Hitung masa pemakaian sampai tahun berjalan
        $currentYear = (int) date('Y');
        $currentMonth = (int) date('m');
        $acqYear = (int) ($this->acquisition_year ?: $currentYear);

        if ($this->acquisition_date) {
            $acqDate = Carbon::parse($this->acquisition_date);
            $monthsUsed = max(0, $acqDate->diffInMonths(now()));
            $accumulated = min($depreciableBase, $monthsUsed * $monthly);
        } else {
            $yearsUsed = max(0, $currentYear - $acqYear);
            $accumulated = min($depreciableBase, $yearsUsed * $annual);
        }

        $this->accumulated_depreciation = round($accumulated, 2);
        $this->current_book_value = round(max($salvage, $totalCost - $this->accumulated_depreciation), 2);
    }

    /**
     * Jadwal Simulasi Penyusutan Tahunan (Depreciation Schedule)
     */
    public function getDepreciationScheduleAttribute(): array
    {
        $schedule = [];
        $totalCost = (float) $this->acquisition_price;
        $salvage = (float) $this->salvage_value;
        $usefulLife = (int) ($this->useful_life_years ?: 5);
        $startYear = (int) ($this->acquisition_year ?: date('Y'));

        if ($this->depreciation_method === 'none' || $usefulLife <= 0) {
            return [[
                'year' => $startYear,
                'beginning_value' => $totalCost,
                'depreciation' => 0,
                'accumulated' => 0,
                'ending_value' => $totalCost,
            ]];
        }

        $depreciableBase = max(0, $totalCost - $salvage);
        $annualDep = $depreciableBase / $usefulLife;
        $runningAccumulated = 0;
        $runningBookValue = $totalCost;

        for ($i = 0; $i <= $usefulLife; $i++) {
            $year = $startYear + $i;
            if ($i === 0) {
                $schedule[] = [
                    'year' => $year . ' (Awal Perolehan)',
                    'beginning_value' => $totalCost,
                    'depreciation' => 0,
                    'accumulated' => 0,
                    'ending_value' => $totalCost,
                ];
                continue;
            }

            $begVal = $runningBookValue;
            $dep = min($annualDep, max(0, $begVal - $salvage));
            $runningAccumulated += $dep;
            $endVal = max($salvage, $totalCost - $runningAccumulated);
            $runningBookValue = $endVal;

            $schedule[] = [
                'year' => 'Tahun ' . $i . ' (' . $year . ')',
                'beginning_value' => round($begVal, 2),
                'depreciation' => round($dep, 2),
                'accumulated' => round($runningAccumulated, 2),
                'ending_value' => round($endVal, 2),
            ];

            if ($runningBookValue <= $salvage) {
                break;
            }
        }

        return $schedule;
    }

    /**
     * Helper Generate Kode Aset Otomatis
     */
    public static function generateAssetCode(?int $schoolId = null, ?string $categoryCode = 'AST', ?int $year = null): string
    {
        $year = $year ?: date('Y');
        
        $unitCode = 'YYS';
        if ($schoolId) {
            $school = School::find($schoolId);
            if ($school) {
                $name = strtoupper($school->name);
                if ($school->type === 'yayasan' || str_contains($name, 'YAYASAN')) {
                    $unitCode = 'YYS';
                } elseif (str_contains($name, 'SMK')) {
                    $unitCode = 'SMK';
                } elseif (str_contains($name, 'SMA')) {
                    $unitCode = 'SMA';
                } elseif (str_contains($name, 'SMP')) {
                    $unitCode = 'SMP';
                } else {
                    $unitCode = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $school->name), 0, 3));
                }
            }
        }

        $catCode = strtoupper($categoryCode ?: 'AST');
        $prefix = "AST-{$unitCode}-{$year}-";

        $lastAsset = self::where('asset_code', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        $nextNum = 1;
        if ($lastAsset && preg_match('/-(\d+)$/', $lastAsset->asset_code, $matches)) {
            $nextNum = (int) $matches[1] + 1;
        }

        return $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
    }

    // ════════════════ RELATIONSHIPS ════════════════

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function category()
    {
        return $this->belongsTo(InventoryCategory::class, 'category_id');
    }

    public function location()
    {
        return $this->belongsTo(InventoryLocation::class, 'location_id');
    }

    public function picUser()
    {
        return $this->belongsTo(User::class, 'pic_user_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function mutations()
    {
        return $this->hasMany(InventoryMutation::class, 'asset_id')->orderBy('mutation_date', 'desc');
    }

    // ════════════════ ACCESSORS ════════════════

    public function getUnitNameAttribute(): string
    {
        if ($this->school) {
            return $this->school->name;
        }
        return 'Yayasan Perguruan Pembda Nias';
    }

    public function getFormattedAcquisitionPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->acquisition_price, 0, ',', '.');
    }

    public function getFormattedAnnualDepreciationAttribute(): string
    {
        return 'Rp ' . number_format($this->annual_depreciation, 0, ',', '.');
    }

    public function getFormattedAccumulatedDepreciationAttribute(): string
    {
        return 'Rp ' . number_format($this->accumulated_depreciation, 0, ',', '.');
    }

    public function getFormattedCurrentBookValueAttribute(): string
    {
        return 'Rp ' . number_format($this->current_book_value, 0, ',', '.');
    }

    public function getConditionBadgeAttribute(): array
    {
        return match ($this->condition) {
            'baik' => ['label' => 'Baik', 'class' => 'bg-emerald-100 text-emerald-800 border-emerald-300', 'icon' => 'fa-check-circle'],
            'rusak_ringan' => ['label' => 'Rusak Ringan', 'class' => 'bg-amber-100 text-amber-800 border-amber-300', 'icon' => 'fa-exclamation-triangle'],
            'rusak_berat' => ['label' => 'Rusak Berat', 'class' => 'bg-rose-100 text-rose-800 border-rose-300', 'icon' => 'fa-times-circle'],
            'hilang' => ['label' => 'Hilang', 'class' => 'bg-gray-100 text-gray-800 border-gray-400', 'icon' => 'fa-question-circle'],
            'dihapuskan' => ['label' => 'Dihapuskan', 'class' => 'bg-purple-100 text-purple-800 border-purple-300', 'icon' => 'fa-ban'],
            default => ['label' => ucfirst($this->condition), 'class' => 'bg-gray-100 text-gray-800 border-gray-300', 'icon' => 'fa-info-circle'],
        };
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'aktif' => ['label' => 'Digunakan / Aktif', 'class' => 'bg-blue-100 text-blue-800 border-blue-300'],
            'dipinjam' => ['label' => 'Sedang Dipinjam', 'class' => 'bg-purple-100 text-purple-800 border-purple-300'],
            'perbaikan' => ['label' => 'Dalam Perbaikan', 'class' => 'bg-orange-100 text-orange-800 border-orange-300'],
            'disimpan' => ['label' => 'Disimpan di Gudang', 'class' => 'bg-slate-100 text-slate-800 border-slate-300'],
            'dihapuskan' => ['label' => 'Dihapuskan / Dijual', 'class' => 'bg-red-100 text-red-800 border-red-300'],
            default => ['label' => ucfirst($this->status), 'class' => 'bg-gray-100 text-gray-800 border-gray-300'],
        };
    }

    // ════════════════ SCOPES ════════════════

    public function scopeFilterSchool($query, $schoolId)
    {
        if ($schoolId === 'yayasan') {
            return $query->where(function($q) {
                $q->whereNull('school_id')
                  ->orWhereHas('school', fn($sq) => $sq->where('type', 'yayasan'));
            });
        } elseif (!empty($schoolId) && $schoolId !== 'all') {
            return $query->where('school_id', $schoolId);
        }
        return $query;
    }

    public function scopeFilterCategory($query, $categoryId)
    {
        if (!empty($categoryId) && $categoryId !== 'all') {
            return $query->where('category_id', $categoryId);
        }
        return $query;
    }

    public function scopeFilterCondition($query, $condition)
    {
        if (!empty($condition) && $condition !== 'all') {
            return $query->where('condition', $condition);
        }
        return $query;
    }

    public function scopeFilterYear($query, $year)
    {
        if (!empty($year) && $year !== 'all') {
            return $query->where('acquisition_year', $year);
        }
        return $query;
    }

    public function scopeSearch($query, $term)
    {
        if (!empty($term)) {
            return $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('asset_code', 'like', "%{$term}%")
                  ->orWhere('brand', 'like', "%{$term}%")
                  ->orWhere('serial_number', 'like', "%{$term}%")
                  ->orWhere('description', 'like', "%{$term}%")
                  ->orWhere('pic_name', 'like', "%{$term}%");
            });
        }
        return $query;
    }
}
